<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowSequence\Exception;

use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowSequence\FlowSequenceInterface;
use Shopware\Core\Framework\Plugin\PluginException;
use Symfony\Component\HttpFoundation\Response;

class MissingMailTemplateArgumentException extends PluginException
{
    public function __construct(FlowSequenceInterface $flowSequence)
    {
        parent::__construct(
            Response::HTTP_BAD_REQUEST,
            'FLOW_BUILDER__INCOMPLETE',
            <<<TEXT
Flow sequence element {{ class }} is not configured properly, constructor argument "mailTemplate" cannot be null.
TEXT,
            [ 'class' => $flowSequence::class ],
        );
    }
}
