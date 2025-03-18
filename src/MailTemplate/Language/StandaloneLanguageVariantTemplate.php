<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate\Language;

use Shopware\Core\System\Language\LanguageEntity;

final class StandaloneLanguageVariantTemplate extends AbstractLanguageTemplate
{
    public function __construct(
        private readonly string|null $mailTemplateId,
        string|null $subject,
        string $contentPlain,
        string $contentHtml,
        string $senderName,
        string|null $description,
        private readonly LanguageEntity $language,
    ) {
        parent::__construct($this->language->getId(), $subject, $contentPlain, $contentHtml, $senderName, $description);
    }

    /**
     * @return array<string, mixed|null>
     */
    #[\Override]
    public function toArray(): array
    {
        return \array_merge(
            parent::toArray(),
            [
                'mailTemplateId' => $this->mailTemplateId,
            ],
        );
    }
}
