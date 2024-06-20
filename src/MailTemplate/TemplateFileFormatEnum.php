<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

enum TemplateFileFormatEnum: string
{
    case HTML  = 'html';
    case PLAIN = 'plain';
}
