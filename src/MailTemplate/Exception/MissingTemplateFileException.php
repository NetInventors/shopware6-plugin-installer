<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate\Exception;

use NetInventors\Shopware6PluginInstaller\MailTemplate\MailTemplateInterface;
use Shopware\Core\Framework\Plugin\PluginException;
use Symfony\Component\HttpFoundation\Response;

class MissingTemplateFileException extends PluginException
{
    public function __construct(MailTemplateInterface $mailTemplate, string $locale)
    {
        parent::__construct(
            Response::HTTP_BAD_REQUEST,
            'MAIL_TEMPLATE__INCOMPLETE',
            'Mail template {{ class }} is not configured properly, template files missing for "{{ locale }}".',
            [
                'class'  => $mailTemplate::class,
                'locale' => $locale,
            ],
        );
    }
}
