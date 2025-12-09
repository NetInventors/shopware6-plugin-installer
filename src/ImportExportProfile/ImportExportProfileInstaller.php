<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\ImportExportProfile;

use NetInventors\Shopware6PluginInstaller\InstallerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Dynamically installs all profiles defined in Resources/config/setup/import-export-profile.php
 *
 * @psalm-import-type MappingEntry from ImportExportProfileInterface
 * @psalm-type ProfileClass = class-string<ImportExportProfileInterface>
 */
final readonly class ImportExportProfileInstaller implements InstallerInterface
{
    private EntityRepository $profileRepository;

    private EntityRepository $profileTranslationRepository;

    private EntityRepository $languageRepository;

    private ProfileExistsStateInjector $profileExistsStateInjector;

    private ImportExportProfileCollection $profileCollection;

    public function __construct(
        private ContainerInterface $container,
        private string $directory,
    ) {
        /** @var EntityRepository $profileRepository */
        $profileRepository = $this->container->get('import_export_profile.repository');

        /** @var EntityRepository $profileTranslationRepository */
        $profileTranslationRepository = $this->container->get('import_export_profile_translation.repository');

        /** @var EntityRepository $languageRepository */
        $languageRepository = $this->container->get('language.repository');

        $this->profileRepository            = $profileRepository;
        $this->profileTranslationRepository = $profileTranslationRepository;
        $this->languageRepository           = $languageRepository;
        $this->profileExistsStateInjector   = new ProfileExistsStateInjector($this->profileRepository);
        $this->profileCollection            = ImportExportProfileCollectionFactory::create($this->container, $this->directory);

    }

    #[\Override]
    public function install(InstallContext $installContext): void
    {
        $context = $installContext->getContext();

        /** @var list<string>|list<array<string, string>> $languageIds */
        $languageIds = $this->languageRepository->searchIds(new Criteria(), $context)->getIds();

        $profilePayloads     = [];
        $translationPayloads = [];

        $installableProfiles = $this->profileExistsStateInjector->injectProfileExistsState(
            $this->profileCollection,
            $context,
        );

        foreach ($installableProfiles as $profile) {
            $profileId         = $profile->getId() ?? Uuid::randomHex();
            $profilePayloads[] = [
                'id'            => $profileId,
                'technicalName' => $profile::getTechnicalName(),
                'label'         => $profile::getLabel(),
                'systemDefault' => $profile->isSystemDefault(),
                'sourceEntity'  => $profile->getSourceEntity(),
                'fileType'      => $profile->getFileMimeType(),
                'delimiter'     => $profile->getDelimiter(),
                'enclosure'     => $profile->getEnclosure(),
                'config'        => $profile->getConfig(),
                'mapping'       => $profile->getMapping(),
            ];

            foreach ($languageIds as $languageId) {
                $translationPayloads[] = [
                    'importExportProfileId' => $profileId,
                    'languageId'            => $languageId,
                    'label'                 => $profile::getLabel(),
                ];
            }
        }

        if ([] !== $profilePayloads) {
            $this->profileRepository->upsert($profilePayloads, $context);
        }

        if ([] !== $translationPayloads) {
            $this->profileTranslationRepository->upsert($translationPayloads, $context);
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
