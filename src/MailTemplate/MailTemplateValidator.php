<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

use NetInventors\Shopware6PluginInstaller\MailTemplate\Exception\AvailableEntitiesException;
use NetInventors\Shopware6PluginInstaller\MailTemplate\Exception\DescriptionException;
use NetInventors\Shopware6PluginInstaller\MailTemplate\Exception\MissingTemplateFileException;
use NetInventors\Shopware6PluginInstaller\MailTemplate\Exception\SubjectException;

final class MailTemplateValidator
{
    public function validate(MailTemplateInterface $mailTemplate): void
    {
        $availableEntities = $mailTemplate->getAvailableEntities();
        $subjects          = $mailTemplate->getSubjects();
        $descriptions      = $mailTemplate->getDescriptions();

        if ([] === $availableEntities) {
            throw AvailableEntitiesException::createAvailableEntitiesEmptyException($mailTemplate);
        }

        if ([] === $subjects) {
            throw SubjectException::createSubjectsEmptyException($mailTemplate);
        }

        if ([] === $descriptions) {
            throw DescriptionException::createDescriptionsEmptyException($mailTemplate);
        }

        foreach ($mailTemplate->getLocales() as $locale) {
            if (!isset($subjects[$locale])) {
                throw SubjectException::createMissingSubjectTranslationException($mailTemplate, $locale);
            }

            if (!isset($descriptions[$locale])) {
                throw DescriptionException::createMissingDescriptionTranslationException($mailTemplate, $locale);
            }

            $mailTemplateResources = $mailTemplate->getResourceFilesForLocale($locale);

            if (
                null === $mailTemplateResources
                || null === $mailTemplateResources->html
                || null === $mailTemplateResources->plain
            ) {
                throw new MissingTemplateFileException($mailTemplate, $locale);
            }
        }
    }
}
