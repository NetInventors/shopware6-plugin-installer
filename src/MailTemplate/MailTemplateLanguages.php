<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

use Shopware\Core\System\Language\LanguageEntity;

readonly class MailTemplateLanguages
{
    /**
     * @param list<LanguageEntity> $languages
     */
    public function __construct(
        public LanguageEntity $default,
        public array $languages,
    ) {
    }
}
