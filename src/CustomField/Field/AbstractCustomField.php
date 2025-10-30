<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\CustomField\Field;

abstract class AbstractCustomField implements CustomFieldInterface
{
    protected string|null $id = null;

    #[\Override]
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    #[\Override]
    public function getId(): string|null
    {
        return $this->id;
    }

    #[\Override]
    public function isActive(): bool
    {
        return true;
    }

    #[\Override]
    public function isAllowCartExpose(): bool
    {
        return true;
    }

    #[\Override]
    public function isAllowCustomerRead(): bool
    {
        return true;
    }

    #[\Override]
    public function isAllowCustomerWrite(): bool
    {
        return true;
    }
}
