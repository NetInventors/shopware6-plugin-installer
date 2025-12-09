<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\CustomField;

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
    private EntityRepository $fieldSetRepository;

    private EntityRepository $fieldRepository;

    private FieldSetExistsStateInjector $fieldSetExistsStateInjector;

    private CustomFieldSetCollection $fieldSetCollection;

    public function __construct(
        private ContainerInterface $container,
        private string $directory,
    ) {
        /** @var EntityRepository $fieldSetRepository */
        $fieldSetRepository = $this->container->get('custom_field_set.repository');

        /** @var EntityRepository $fieldRepository */
        $fieldRepository = $this->container->get('custom_field.repository');

        $this->fieldSetRepository          = $fieldSetRepository;
        $this->fieldRepository             = $fieldRepository;
        $this->fieldSetExistsStateInjector = new FieldSetExistsStateInjector($this->fieldSetRepository);
        $this->fieldSetCollection          = CustomFieldSetCollectionFactory::create($this->container, $this->directory);
    }

    #[\Override]
    public function install(InstallContext $installContext): void
    {
        $context = $installContext->getContext();

        $fieldSetPayLoads = [];
        $fieldPayloads    = [];

        $installableFieldSets = $this->fieldSetExistsStateInjector->injectFieldSetExistsState(
            $this->fieldSetCollection,
            $context,
        );

        foreach ($installableFieldSets as $set) {
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

            foreach ($set->getFields() as $field) {
                $fieldPayloads[] = [
                    'id'                 => $field->getId() ?? Uuid::randomHex(),
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
}
