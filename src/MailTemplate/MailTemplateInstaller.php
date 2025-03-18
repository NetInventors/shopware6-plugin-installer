<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

use NetInventors\Shopware6PluginInstaller\InstallerInterface;
use NetInventors\Shopware6PluginInstaller\MailTemplate\Language\DefaultLanguageTemplate;
use NetInventors\Shopware6PluginInstaller\MailTemplate\Language\LanguageVariantTemplate;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;

final readonly class MailTemplateInstaller implements InstallerInterface
{
    private EntityRepository $mailTemplateTypeRepository;

    private EntityRepository $mailTemplateRepository;

    private MailTemplateLanguagesLoader $mailTemplateLanguagesLoader;

    private MailTemplateValidator $mailTemplateValidator;

    private MailTemplateExistsStateInjector $mailTemplateExistsStateInjector;

    private MailTemplateCollection $mailTemplateCollection;

    public function __construct(
        private ContainerInterface $container,
        private string $directory,
        private string $fallbackIsoCode,
    ) {
        /** @var EntityRepository $mailTemplateTypeRepository */
        $mailTemplateTypeRepository = $this->container->get('mail_template_type.repository');

        /** @var EntityRepository $mailTemplateRepository */
        $mailTemplateRepository = $this->container->get('mail_template.repository');

        $this->mailTemplateTypeRepository      = $mailTemplateTypeRepository;
        $this->mailTemplateRepository          = $mailTemplateRepository;
        $this->mailTemplateLanguagesLoader     = new MailTemplateLanguagesLoader($this->container);
        $this->mailTemplateValidator           = new MailTemplateValidator();
        $this->mailTemplateExistsStateInjector = new MailTemplateExistsStateInjector($this->mailTemplateTypeRepository);
        $this->mailTemplateCollection          = MailTemplateCollectionFactory::create($this->directory);
    }

    #[\Override]
    public function install(InstallContext $installContext): void
    {
        $this->insertNonExistentTemplates($installContext->getContext());
    }

    #[\Override]
    public function postInstall(InstallContext $installContext): void
    {
    }

    #[\Override]
    public function activate(ActivateContext $activateContext): void
    {
    }

    private function insertNonExistentTemplates(Context $context): void
    {
        $mailTemplateTypes = [];
        $mailTemplates     = [];
        $templatesLocales  = $this->mailTemplateCollection->getLocales();
        $languages         = $this->mailTemplateLanguagesLoader->loadLanguagesForLocales($templatesLocales, $context);

        $installableMailTemplates = $this->mailTemplateExistsStateInjector->injectMailTemplateExistsState(
            $this->mailTemplateCollection,
            $context,
        );

        foreach ($installableMailTemplates as $mailTemplate) {
            if (null !== $mailTemplate->getId()) {
                continue;
            }

            $this->mailTemplateValidator->validate($mailTemplate);

            if (null === $mailTemplate->getTypeId()) {
                $mailTemplate->setTypeId(Uuid::randomHex());

                $mailTemplateTypes[] = [
                    'id'                => $mailTemplate->getTypeId(),
                    'technicalName'     => $mailTemplate->getTechnicalName(),
                    'availableEntities' => $mailTemplate->getAvailableEntities(),
                    'translations'      => [
                        Defaults::LANGUAGE_SYSTEM => [
                            'name' => $mailTemplate->getName(),
                        ],
                    ],
                ];
            }

            $languageVariants = [];

            foreach ($languages->languages as $language) {
                $isoCode = $language->getLocale()?->getCode();

                if (null === $isoCode) {
                    continue;
                }

                if (!$mailTemplate->supportsLocale($isoCode)) {
                    continue;
                }

                $resourceFiles = $mailTemplate->getResourceFilesForLocale($isoCode);

                $languageVariants[] = new LanguageVariantTemplate(
                    Uuid::randomHex(),
                    $mailTemplate->getTypeId(),
                    $mailTemplate->getSubjectForLocale($isoCode),
                    $resourceFiles?->plain?->getContents() ?? '',
                    $resourceFiles?->html?->getContents() ?? '',
                    $mailTemplate->getSenderName(),
                    $mailTemplate->getDescriptionForLocale($isoCode),
                    $language,
                );
            }

            $isoCode = $languages->default->getLocale()?->getCode();

            if (null === $isoCode) {
                continue;
            }

            if (!$mailTemplate->supportsLocale($isoCode)) {
                $isoCode = $this->fallbackIsoCode;
            }

            $resourceFiles = $mailTemplate->getResourceFilesForLocale($isoCode);

            $mailTemplates[] = (new DefaultLanguageTemplate(
                Uuid::randomHex(),
                $mailTemplate->getTypeId(),
                Defaults::LANGUAGE_SYSTEM,
                $mailTemplate->getSubjectForLocale($isoCode),
                $resourceFiles?->plain?->getContents() ?? '',
                $resourceFiles?->html?->getContents() ?? '',
                $mailTemplate->getSenderName(),
                $mailTemplate->getDescriptionForLocale($isoCode),
                $languageVariants,
            ))->toArray();
        }

        if ([] !== $mailTemplateTypes) {
            $this->mailTemplateTypeRepository->create($mailTemplateTypes, $context);
        }

        if ([] !== $mailTemplates) {
            $this->mailTemplateRepository->create($mailTemplates, $context);
        }
    }
}
