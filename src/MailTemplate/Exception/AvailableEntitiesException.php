<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate\Exception;

use NetInventors\Shopware6PluginInstaller\MailTemplate\MailTemplateInterface;
use Shopware\Core\Framework\Plugin\PluginException;
use Symfony\Component\HttpFoundation\Response;

final class AvailableEntitiesException extends PluginException
{
    public function __construct(string $message, array $variables = [])
    {
        parent::__construct(
            Response::HTTP_BAD_REQUEST,
            'MAIL_TEMPLATE__INCOMPLETE',
            $message,
            $variables,
        );
    }

    public static function createAvailableEntitiesEmptyException(MailTemplateInterface $mailTemplate): self
    {
        return new self(
            'Mail template {{ class }} is not configured properly, "available entities" is empty.',
            [ 'class' => $mailTemplate::class ],
        );
    }
}
