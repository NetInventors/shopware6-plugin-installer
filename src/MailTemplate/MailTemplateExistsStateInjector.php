<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

use Shopware\Core\Content\MailTemplate\Aggregate\MailTemplateTranslation\MailTemplateTranslationCollection;
use Shopware\Core\Content\MailTemplate\Aggregate\MailTemplateTranslation\MailTemplateTranslationEntity;
use Shopware\Core\Content\MailTemplate\Aggregate\MailTemplateType\MailTemplateTypeEntity;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;

class MailTemplateExistsStateInjector
{
    public function __construct(
        private readonly EntityRepository $mailTemplateTypeRepository,
    ) {
    }

    public function injectMailTemplateExistsState(
        MailTemplateCollection $installableMailTemplates,
        Context $context,
    ): MailTemplateCollection {
        if (0 === $installableMailTemplates->count()) {
            return $installableMailTemplates;
        }

        $criteria = new Criteria();

        $criteria
            ->addAssociations([ 'mailTemplates' ])
            ->addAssociations([ 'mailTemplates.translations' ])
            ->addAssociations([ 'mailTemplates.translations.language' ])
            ->addAssociations([ 'mailTemplates.translations.language.locale' ])
            ->addFilter(new EqualsAnyFilter('technicalName', $installableMailTemplates->getTechnicalNames()))
        ;

        $mailTemplateTypeEntities = $this->mailTemplateTypeRepository->search($criteria, $context);

        /** @var MailTemplateTypeEntity $mailTemplateTypeEntity */
        foreach ($mailTemplateTypeEntities as $mailTemplateTypeEntity) {
            $technicalName           = $mailTemplateTypeEntity->getTechnicalName();
            $installableMailTemplate = $installableMailTemplates->get($technicalName);

            /** @var MailTemplateEntity|null $mailTemplate */
            $mailTemplate = $mailTemplateTypeEntity->getMailTemplates()?->first();

            /**
             * $installableMailTemplate cannot be null.
             * We use all the technical names to fetch the records from the database,
             * traverse them and read the matching installable mail template.
             *
             * @psalm-suppress PossiblyNullReference
             */
            $installableMailTemplate->setTypeId(
                $mailTemplateTypeEntity->getId(),
            );

            if ($mailTemplate instanceof MailTemplateEntity) {
                $installableMailTemplate->setId($mailTemplate->getId());
                $installableMailTemplate->setUpdatedAt($mailTemplate->getUpdatedAt());

                $installableMailTemplate->setTranslatedLocales(
                    $this->assembleMailTemplateTranslations($mailTemplate->getTranslations()),
                );
            }
        }

        return $installableMailTemplates;
    }

    private function assembleMailTemplateTranslations(
        MailTemplateTranslationCollection|null $translations,
    ): MailTemplateTranslatedLocalesCollection {
        if (null === $translations) {
            return new MailTemplateTranslatedLocalesCollection();
        }

        /** @var list<string> $isoCodes */
        $isoCodes = \array_filter($translations->fmap(
            static fn (MailTemplateTranslationEntity $entity): string|null
                => $entity->getLanguage()?->getLocale()?->getCode(),
        ));

        return new MailTemplateTranslatedLocalesCollection($isoCodes);
    }
}
