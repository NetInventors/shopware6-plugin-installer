<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate\Exception;

use Shopware\Core\Framework\HttpException;
use Symfony\Component\HttpFoundation\Response;

class DefaultLanguageNotFoundException extends HttpException
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
