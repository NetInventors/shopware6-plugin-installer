<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate\Exception;

use NetInventors\Shopware6PluginInstaller\MailTemplate\MailTemplateInterface;
use Shopware\Core\Framework\Plugin\PluginException;
use Symfony\Component\HttpFoundation\Response;

class SubjectException extends PluginException
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

    public static function createSubjectsEmptyException(MailTemplateInterface $mailTemplate): self
    {
        return new self(
            'Mail template {{ class }} is not configured properly, "subject" is empty.',
            [ 'class' => $mailTemplate::class ],
        );
    }

    public static function createMissingSubjectTranslationException(
        MailTemplateInterface $mailTemplate,
        string $locale,
    ): self {
        return new self(
            'Mail template {{ class }} is not configured properly, Subject for "{{ locale }}" is empty.',
            [
                'class'  => $mailTemplate::class,
                'locale' => $locale,
            ],
        );
    }
}
