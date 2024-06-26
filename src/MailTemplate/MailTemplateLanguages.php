<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

use Shopware\Core\System\Language\LanguageEntity;

class MailTemplateLanguages
{
    /**
     * @param list<LanguageEntity> $languages
     */
    public function __construct(
        public readonly LanguageEntity $default,
        public readonly array $languages,
    ) {
    }
}
