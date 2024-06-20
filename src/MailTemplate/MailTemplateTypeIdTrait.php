<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

trait MailTemplateTypeIdTrait
{
    protected string|null $typeId = null;

    public function setTypeId(string $typeId): void
    {
        $this->typeId = $typeId;
    }

    public function getTypeId(): string|null
    {
        return $this->typeId;
    }
}
