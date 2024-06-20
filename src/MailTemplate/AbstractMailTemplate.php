<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

abstract class AbstractMailTemplate implements MailTemplateInterface
{
    use MailTemplateIdTrait;
    use MailTemplateTypeIdTrait;
    use MailTemplateUpdatedAtTrait;
    use MailTemplateTranslatedLocalesTrait;

    /**
     * @var array<string, MailTemplateResources>
     */
    private array $resources = [];

    /**
     * @param list<string> $locales
     */
    public function __construct(
        private readonly string $directory,
        private readonly array $locales,
    ) {
        $resourceLocation = $this->getResourceLocation();

        foreach ($this->locales as $locale) {
            $plain = $this->findTemplateResourceForLocaleByFormat(
                $locale,
                TemplateFileFormatEnum::PLAIN,
                $resourceLocation,
            );

            $html = $this->findTemplateResourceForLocaleByFormat(
                $locale,
                TemplateFileFormatEnum::HTML,
                $resourceLocation,
            );

            $this->resources[$locale] = new MailTemplateResources(html: $html, plain: $plain);
        }
    }

    public function getLocales(): array
    {
        return $this->locales;
    }

    public function supportsLocale(string $locale): bool
    {
        return \in_array($locale, $this->locales, true);
    }

    public function getResourceLocation(): string
    {
        return Path::join($this->directory, 'Resources/mails', $this->getTechnicalName());
    }

    public function getResourceFiles(): array
    {
        return $this->resources;
    }

    public function getResourceFilesForLocale(string $locale): MailTemplateResources|null
    {
        return $this->resources[$locale] ?? null;
    }

    public function getSenderName(): string
    {
        return '{{ salesChannel.name }}';
    }

    public function findTemplateResourceForLocaleByFormat(
        string $locale,
        TemplateFileFormatEnum $format,
        string $resourceLocation = null,
    ): SplFileInfo|null {
        $finder = (new Finder())
            ->in($resourceLocation ?? $this->getResourceLocation())
            ->path($locale)
            ->depth(1)
            ->name($format->value . '.html.twig')
        ;

        $iterator = $finder->getIterator();
        $iterator->rewind();

        return $iterator->current();
    }
}
