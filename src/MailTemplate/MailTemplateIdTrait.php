<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

trait MailTemplateIdTrait
{
    protected string|null $id = null;

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string|null
    {
        return $this->id;
    }
}
