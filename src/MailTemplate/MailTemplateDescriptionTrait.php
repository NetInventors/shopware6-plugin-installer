<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

trait MailTemplateDescriptionTrait
{
    /**
     * @var array<string, string>
     */
    protected array $descriptions = [];

    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    public function getDescriptionForLocale(string $locale): string|null
    {
        return $this->descriptions[$locale] ?? null;
    }
}
