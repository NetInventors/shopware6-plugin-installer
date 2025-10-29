<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\CustomField\Field;

use NetInventors\Shopware6Plugin\PluginInstaller\IdFieldTrait;

abstract class AbstractCustomField implements CustomFieldInterface
{
    use IdFieldTrait;

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
