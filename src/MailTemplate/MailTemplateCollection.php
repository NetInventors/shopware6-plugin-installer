<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

use Shopware\Core\Framework\Struct\Collection;

/**
 * @extends Collection<MailTemplateInterface>
 */
class MailTemplateCollection extends Collection
{
    /**
     * @param array<class-string<MailTemplateInterface>, list<string>> $installableMailTemplates
     */
    public function __construct(
        private readonly string $directory,
        array $installableMailTemplates = [],
    ) {
        foreach ($installableMailTemplates as $className => $locales) {
            $mailTemplate = new $className($this->directory, $locales);

            $this->set($mailTemplate->getTechnicalName(), $mailTemplate);
        }
    }

    /**
     * @return list<string>
     */
    public function getTechnicalNames(): array
    {
        return \array_map(
            static fn (MailTemplateInterface $mailTemplate): string => $mailTemplate->getTechnicalName(),
            \array_values($this->elements),
        );
    }

    /**
     * @return list<string>
     */
    public function getLocales(): array
    {
        /** @var list<string> $locales */
        $locales = \array_unique(\array_merge(...\array_map(
            static fn (MailTemplateInterface $mailTemplate): array => $mailTemplate->getLocales(),
            \array_values($this->elements),
        )));

        return $locales;
    }
}
