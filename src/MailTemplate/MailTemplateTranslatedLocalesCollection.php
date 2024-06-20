<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

use Shopware\Core\Framework\Struct\Collection;

/**
 * @extends Collection<true>
 */
class MailTemplateTranslatedLocalesCollection extends Collection
{
    /**
     * @param list<string> $locales
     */
    public function __construct(array $locales = [])
    {
        foreach ($locales as $locale) {
            $this->set($locale, true);
        }
    }
}
