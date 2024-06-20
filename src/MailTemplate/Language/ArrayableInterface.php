<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate\Language;

interface ArrayableInterface
{
    /**
     * @return array<string, mixed|null>
     */
    public function toArray(): array;
}
