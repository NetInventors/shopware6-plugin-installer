<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\CustomField;

use NetInventors\Shopware6PluginInstaller\CustomField\Field\CustomFieldInterface;
use NetInventors\Shopware6PluginInstaller\CustomField\FieldSet\CustomFieldSetCollection;
use NetInventors\Shopware6PluginInstaller\CustomField\FieldSet\CustomFieldSetInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetEntity;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSetRelation\CustomFieldSetRelationCollection;
use Shopware\Core\System\CustomField\CustomFieldCollection;

final readonly class FieldSetExistsStateInjector
{
    public function __construct(
        private EntityRepository $fieldSetRepository,
    ) {
    }

    /**
     * @return \WeakMap<CustomFieldSetInterface, list<CustomFieldInterface>>
     */
    public function injectFieldSetExistsState(
        CustomFieldSetCollection $installableFieldSets,
        Context $context,
    ): \WeakMap {
        /** @var \WeakMap<CustomFieldSetInterface, list<CustomFieldInterface>> $fieldsBySet */
        $fieldsBySet = new \WeakMap();

        if (0 === $installableFieldSets->count()) {
            return $fieldsBySet;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('name', $installableFieldSets->getSetNames()));
        $criteria->addAssociation('customFields');
        $criteria->addAssociation('relations');

        $fieldSetEntities = $this->fieldSetRepository->search($criteria, $context);

        /** @var CustomFieldSetEntity $customFieldSet */
        foreach ($fieldSetEntities as $customFieldSet) {
            $fieldSetName = $customFieldSet->getName();

            /** @var CustomFieldSetInterface|null $installableFieldSet */
            $installableFieldSet = $installableFieldSets->get($fieldSetName);

            if (null === $installableFieldSet) {
                continue;
            }

            $installableFieldSet->setId($customFieldSet->getId());

            $installableFields = $installableFieldSet->getFields();

            $fieldsBySet[$installableFieldSet] = $installableFields;

            $customFields = $customFieldSet->getCustomFields();

            if (!$customFields instanceof CustomFieldCollection) {
                continue;
            }

            foreach ($customFields as $customFieldEntity) {
                $dbFieldName = $customFieldEntity->getName();

                foreach ($installableFields as $installableField) {
                    if ($installableField->getName() === $dbFieldName) {
                        $installableField->setId($customFieldEntity->getId());
                    }
                }
            }

            $relationsToInstall = [];

            foreach ($installableFieldSet->getRelatedEntities() as $entityName) {
                $relationsToInstall[$entityName] = $entityName;
            }

            $relationCollection = $customFieldSet->getRelations();

            if ($relationCollection instanceof CustomFieldSetRelationCollection) {
                foreach ($relationCollection->getElements() as $relation) {
                    $entityName = $relation->getEntityName();

                    if (isset($relationsToInstall[$entityName])) {
                        unset($relationsToInstall[$entityName]);
                    }
                }
            }

            $installableFieldSet->setRelatedEntities(\array_keys($relationsToInstall));
        }

        return $fieldsBySet;
    }
}
