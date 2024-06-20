<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

trait MailTemplateSubjectTrait
{
    /**
     * @var array<string, string>
     */
    protected array $subjects = [];

    public function getSubjects(): array
    {
        return $this->subjects;
    }

    public function getSubjectForLocale(string $locale): string|null
    {
        return $this->subjects[$locale] ?? null;
    }
}
