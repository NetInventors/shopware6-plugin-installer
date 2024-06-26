<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

use Symfony\Component\Finder\SplFileInfo;

class MailTemplateResources
{
    public function __construct(
        public readonly SplFileInfo|null $html,
        public readonly SplFileInfo|null $plain,
    ) {
    }

    public function hasHtml(): bool
    {
        return null !== $this->html;
    }

    public function hasPlain(): bool
    {
        return null !== $this->plain;
    }
}
