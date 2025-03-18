<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate\Exception;

use Shopware\Core\Framework\Plugin\PluginException;
use Symfony\Component\HttpFoundation\Response;

final class DefaultLanguageNotFoundException extends PluginException
{
    public function __construct()
    {
        parent::__construct(
            Response::HTTP_BAD_REQUEST,
            'MAIL_TEMPLATE__INCOMPLETE',
            'Default language not found.',
        );
    }
}
