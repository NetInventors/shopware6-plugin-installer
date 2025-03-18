<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate\Language;

use Shopware\Core\System\Language\LanguageEntity;

final class LanguageVariantTemplate extends AbstractLanguageTemplate
{
    public function __construct(
        private readonly string $id,
        private readonly string|null $typeId,
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
                'id'                 => $this->id,
                'mailTemplateTypeId' => $this->typeId,
            ],
        );
    }
}
