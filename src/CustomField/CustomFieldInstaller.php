<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\CustomField;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception;
use NetInventors\Shopware6PluginInstaller\CustomField\Field\CustomFieldInterface;
use NetInventors\Shopware6PluginInstaller\CustomField\FieldSet\CustomFieldSetCollection;
use NetInventors\Shopware6PluginInstaller\CustomField\FieldSet\CustomFieldSetCollectionFactory;
use NetInventors\Shopware6PluginInstaller\InstallerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @psalm-import-type FieldConfig from CustomFieldInterface
 */
final readonly class CustomFieldInstaller implements InstallerInterface
{
    private Connection $connection;

    private EntityRepository $fieldSetRepository;

    private EntityRepository $fieldRepository;

    private FieldSetExistsStateInjector $fieldSetExistsStateInjector;

    private CustomFieldSetCollection $fieldSetCollection;

    public function __construct(
        private ContainerInterface $container,
        private string $directory,
    ) {
        /** @var Connection $fieldSetRepository */
        $this->connection = $this->container->get(Connection::class);

        /** @var EntityRepository $fieldSetRepository */
        $fieldSetRepository = $this->container->get('custom_field_set.repository');

        /** @var EntityRepository $fieldRepository */
        $fieldRepository = $this->container->get('custom_field.repository');

        $this->fieldSetRepository          = $fieldSetRepository;
        $this->fieldRepository             = $fieldRepository;
        $this->fieldSetExistsStateInjector = new FieldSetExistsStateInjector($this->fieldSetRepository);
        $this->fieldSetCollection          = CustomFieldSetCollectionFactory::create($this->container, $this->directory);
    }

    /**
     * @throws Exception
     */
    #[\Override]
    public function install(InstallContext $installContext): void
    {
        $context = $installContext->getContext();

        $fieldSetPayLoads = [];
        $fieldPayloads    = [];

        /** @var list<CustomFieldInterface> $existingFields */
        $existingFields   = [];

        $initializedFieldsMap = $this->fieldSetExistsStateInjector->injectFieldSetExistsState(
            $this->fieldSetCollection,
            $context,
        );

        foreach ($this->fieldSetCollection as $set) {
            $setId = $set->getId() ?? Uuid::randomHex();

            $relations = [];

            foreach ($set->getRelatedEntities() as $entityName) {
                $relations[] = ['entityName' => $entityName];
            }

            $setPayLoad = [
                'id'     => $setId,
                'name'   => $set::getName(),
                'active' => $set->isActive(),
                'global' => $set->isGlobal(),
                'config' => [
                    'label'      => $set->getLabels(),
                    'translated' => $set->isTranslated(),
                ],
            ];

            if ([] !== $relations) {
                $setPayLoad['relations'] = $relations;
            }

            /** @var list<CustomFieldInterface> $fields */
            $fields = $initializedFieldsMap[$set] ?? $set->getFields();

            foreach ($fields as $field) {
                $fieldId = $field->getId();

                if (null !== $fieldId) {
                    $existingFields[] = $field;
                }

                $fieldPayloads[] = [
                    'id'                 => $fieldId ?? Uuid::randomHex(),
                    'name'               => $field->getName(),
                    'type'               => $field->getType(),
                    'config'             => $field->getConfig(),
                    'active'             => $field->isActive(),
                    'allowCartExpose'    => $field->isAllowCartExpose(),
                    'allowCustomerRead'  => $field->isAllowCustomerRead(),
                    'allowCustomerWrite' => $field->isAllowCustomerWrite(),
                    'customFieldSetId'   => $setId,
                ];
            }

            $fieldSetPayLoads[] = $setPayLoad;
        }

        $this->syncChangedFieldTypes($existingFields);

        if ([] !== $fieldSetPayLoads) {
            $this->fieldSetRepository->upsert($fieldSetPayLoads, $context);
        }

        if ([] !== $fieldPayloads) {
            $this->fieldRepository->upsert($fieldPayloads, $context);
        }
    }

    #[\Override]
    public function postInstall(InstallContext $installContext): void
    {
    }

    #[\Override]
    public function activate(ActivateContext $activateContext): void
    {
    }

    /**
     * @param list<CustomFieldInterface> $existingFields
     * @throws Exception
     */
    private function syncChangedFieldTypes(array $existingFields): void
    {
        if ([] === $existingFields) {
            return;
        }

        $bytesIds = [];

        foreach ($existingFields as $field) {
            $id = $field->getId();

            if (null !== $id) {
                $bytesIds[] = Uuid::fromHexToBytes($id);
            }
        }

        if ([] === $bytesIds) {
            return;
        }

        $dbTypes = $this->connection->executeQuery(
            'SELECT LOWER(HEX(id)), type FROM custom_field WHERE id IN (:ids)',
            ['ids' => $bytesIds],
            ['ids' => ArrayParameterType::STRING],
        )->fetchAllKeyValue();

        /** @var list<array{id: string, type: string}> $updates */
        $updates = [];

        foreach ($existingFields as $field) {
            $id = $field->getId();

            if (null === $id) {
                continue;
            }

            /** @var string|null $dbType */
            $dbType = $dbTypes[$id] ?? null;

            if (null !== $dbType && $dbType !== $field->getType()) {
                $updates[] = [
                    'id'   => $id,
                    'type' => $field->getType(),
                ];
            }
        }

        if ([] === $updates) {
            return;
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->update('custom_field');

        $caseParts  = [];
        $parameters = [];
        $types      = [];
        $updateIds  = [];

        foreach ($updates as $i => $update) {
            $idParam   = 'id_' . $i;
            $typeParam = 'type_' . $i;

            $caseParts[] = \sprintf('WHEN :%s THEN :%s', $idParam, $typeParam);

            $parameters[$idParam]   = Uuid::fromHexToBytes($update['id']);
            $parameters[$typeParam] = $update['type'];

            $updateIds[] = Uuid::fromHexToBytes($update['id']);
        }

        $caseSql = \sprintf('(CASE id %s END)', \implode(' ', $caseParts));
        $queryBuilder->set('type', $caseSql);

        $queryBuilder->where('id IN (:updateIds)');
        $parameters['updateIds'] = $updateIds;
        $types['updateIds']      = ArrayParameterType::STRING;

        $queryBuilder->setParameters($parameters, $types);
        $queryBuilder->executeStatement();
    }
}
