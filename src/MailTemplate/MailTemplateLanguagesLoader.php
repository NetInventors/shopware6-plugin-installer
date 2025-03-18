<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

use NetInventors\Shopware6PluginInstaller\MailTemplate\Exception\DefaultLanguageNotFoundException;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\System\Language\LanguageEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

final readonly class MailTemplateLanguagesLoader
{
    private EntityRepository $languageRepository;

    public function __construct(
        private ContainerInterface $container,
    ) {
        /** @var EntityRepository $languageRepository */
        $languageRepository = $this->container->get('language.repository');

        $this->languageRepository = $languageRepository;
    }

    /**
     * @param list<string> $templatesLocales
     */
    public function loadLanguagesForLocales(array $templatesLocales, Context $context): MailTemplateLanguages
    {
        $criteria = new Criteria();
        $criteria->addAssociation('locale');

        /**
         * @psalm-suppress InternalClass
         */
        $defaultLanguageId = Defaults::LANGUAGE_SYSTEM;

        $criteria->addFilter(
            new MultiFilter(
                MultiFilter::CONNECTION_OR,
                [
                    new EqualsFilter('id', $defaultLanguageId),
                    new EqualsAnyFilter('locale.code', $templatesLocales),
                ],
            ),
        );

        $languageEntities = $this->languageRepository->search($criteria, $context);

        $defaultLanguage = null;
        $languages       = [];

        /** @var LanguageEntity $languageEntity */
        foreach ($languageEntities as $languageEntity) {
            if ($defaultLanguageId === $languageEntity->getId()) {
                $defaultLanguage = $languageEntity;

                continue;
            }

            $languages[] = $languageEntity;
        }

        if (null === $defaultLanguage) {
            throw new DefaultLanguageNotFoundException();
        }

        return new MailTemplateLanguages($defaultLanguage, $languages);
    }
}
