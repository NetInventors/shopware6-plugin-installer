<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\ImportExportProfile;

use Shopware\Core\Content\ImportExport\ImportExportProfileEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;

final readonly class ProfileExistsStateInjector
{
    public function __construct(
        private EntityRepository $profileRepository,
    ) {
    }

    public function injectProfileExistsState(
        ImportExportProfileCollection $installableProfiles,
        Context $context,
    ): ImportExportProfileCollection {
        if (0 === $installableProfiles->count()) {
            return $installableProfiles;
        }

        $criteria = new Criteria();

        $criteria->addFilter(new EqualsAnyFilter('technicalName', $installableProfiles->getTechnicalNames()));

        $profileEntities = $this->profileRepository->search($criteria, $context);

        /** @var ImportExportProfileEntity $importExportProfileEntity */
        foreach ($profileEntities as $importExportProfileEntity) {
            $technicalName      = $importExportProfileEntity->getTechnicalName();
            $installableProfile = $installableProfiles->get($technicalName);

            if (null === $installableProfile) {
                continue;
            }

            $installableProfile->setId($importExportProfileEntity->getId());
        }

        return $installableProfiles;
    }
}
