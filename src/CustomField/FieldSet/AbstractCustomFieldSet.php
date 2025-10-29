<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\CustomField\FieldSet;

abstract class AbstractCustomFieldSet implements CustomFieldSetInterface
{
    use CustomFieldSetTrait;

    #[\Override]
    public function isActive(): bool
    {
        return true;
    }

    #[\Override]
    public function isGlobal(): bool
    {
        return false;
    }

    #[\Override]
    public function isTranslated(): bool
    {
        return true;
    }
}
