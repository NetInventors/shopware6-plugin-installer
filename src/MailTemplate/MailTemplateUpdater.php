<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use NetInventors\Shopware6PluginInstaller\MailTemplate\Language\DefaultLanguageTemplate;
use NetInventors\Shopware6PluginInstaller\MailTemplate\Language\LanguageVariantTemplate;
use NetInventors\Shopware6PluginInstaller\MailTemplate\Language\StandaloneLanguageVariantTemplate;
use NetInventors\Shopware6PluginInstaller\UpdaterInterface;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class MailTemplateUpdater implements UpdaterInterface
{
    private readonly EntityRepository $mailTemplateRepository;

    private readonly EntityRepository $mailTemplateTranslationRepository;

    private readonly MailTemplateLanguagesLoader $mailTemplateLanguagesLoader;

    private readonly MailTemplateValidator $mailTemplateValidator;

    private readonly MailTemplateExistsStateInjector $mailTemplateExistsStateInjector;

    private readonly MailTemplateCollection $mailTemplateCollection;

    private readonly UpdatedAtFixer $updatedAtFixer;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly string $directory,
        private readonly string $fallbackIsoCode,
    ) {
        /** @var EntityRepository $mailTemplateTypeRepository */
        $mailTemplateTypeRepository = $this->container->get('mail_template_type.repository');

        /** @var EntityRepository $mailTemplateRepository */
        $mailTemplateRepository = $this->container->get('mail_template.repository');

        /** @var EntityRepository $mailTemplateTranslationRepository */
        $mailTemplateTranslationRepository = $this->container->get('mail_template_translation.repository');

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        $this->mailTemplateRepository            = $mailTemplateRepository;
        $this->mailTemplateTranslationRepository = $mailTemplateTranslationRepository;
        $this->mailTemplateLanguagesLoader       = new MailTemplateLanguagesLoader($this->container);
        $this->mailTemplateValidator             = new MailTemplateValidator();
        $this->mailTemplateExistsStateInjector   = new MailTemplateExistsStateInjector($mailTemplateTypeRepository);
        $this->mailTemplateCollection            = MailTemplateCollectionFactory::create($this->directory);
        $this->updatedAtFixer                    = new UpdatedAtFixer($connection);
    }

    /**
     * @throws Exception
     */
    public function update(UpdateContext $updateContext): void
    {
        (new MailTemplateInstaller($this->container, $this->directory, $this->fallbackIsoCode))
            ->install($updateContext)
        ;

        $this->updateUntouchedTemplates($updateContext->getContext());
        $this->addMissingTemplateTranslations($updateContext->getContext());
    }

    public function postUpdate(UpdateContext $updateContext): void
    {
    }

    /**
     * @throws Exception
     */
    private function updateUntouchedTemplates(Context $context): void
    {
        $mailTemplates    = [];
        $templatesLocales = $this->mailTemplateCollection->getLocales();
        $languages        = $this->mailTemplateLanguagesLoader->loadLanguagesForLocales($templatesLocales, $context);

        $installableMailTemplates = $this->mailTemplateExistsStateInjector->injectMailTemplateExistsState(
            $this->mailTemplateCollection,
            $context,
        );

        foreach ($installableMailTemplates as $mailTemplate) {
            $mailTemplateId = $mailTemplate->getId();

            // Mail template is not persisted, skipping
            if (null === $mailTemplateId) {
                continue;
            }

            // Mail template was updated, skipping
            if ($mailTemplate->wasUpdated()) {
                continue;
            }

            $this->mailTemplateValidator->validate($mailTemplate);

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
                    $mailTemplateId,
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
                $mailTemplateId,
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

        if ([] === $mailTemplates) {
            return;
        }

        $context->addArrayExtension('neti_plugin_installer', [ 'context' => 'update' ]);
        $written = $this->mailTemplateRepository->update($mailTemplates, $context);
        $context->removeExtension('neti_plugin_installer');

        $this->updatedAtFixer->fix($this->mailTemplateRepository->getDefinition(), $written);
        $this->updatedAtFixer->fix($this->mailTemplateTranslationRepository->getDefinition(), $written);
    }

    private function addMissingTemplateTranslations(Context $context): void
    {
        $languageVariants = [];
        $templatesLocales = $this->mailTemplateCollection->getLocales();
        $languages        = $this->mailTemplateLanguagesLoader->loadLanguagesForLocales($templatesLocales, $context);

        $installableMailTemplates = $this->mailTemplateExistsStateInjector->injectMailTemplateExistsState(
            $this->mailTemplateCollection,
            $context,
        );

        foreach ($installableMailTemplates as $mailTemplate) {
            $mailTemplateId = $mailTemplate->getId();

            // Mail template is not persisted, skipping
            if (null === $mailTemplateId) {
                continue;
            }

            $translatedLocales = $mailTemplate->getTranslatedLocales();

            if (null === $translatedLocales) {
                continue;
            }

            foreach ($languages->languages as $language) {
                $isoCode = $language->getLocale()?->getCode();

                if (null === $isoCode) {
                    continue;
                }

                if (!$mailTemplate->supportsLocale($isoCode)) {
                    continue;
                }

                if ($translatedLocales->has($isoCode)) {
                    continue;
                }

                $resourceFiles = $mailTemplate->getResourceFilesForLocale($isoCode);

                $languageVariants[] = (new StandaloneLanguageVariantTemplate(
                    $mailTemplate->getId(),
                    $mailTemplate->getSubjectForLocale($isoCode),
                    $resourceFiles?->plain?->getContents() ?? '',
                    $resourceFiles?->html?->getContents() ?? '',
                    $mailTemplate->getSenderName(),
                    $mailTemplate->getDescriptionForLocale($isoCode),
                    $language,
                ))->toArray();
            }
        }

        if ([] !== $languageVariants) {
            $this->mailTemplateTranslationRepository->create($languageVariants, $context);
        }
    }
}
