<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowSequence;

use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowEvent\FlowEventInterface;
use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowSequence\Exception\MissingMailTemplateArgumentException;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MailSendingFlowSequence implements FlowSequenceInterface
{
    private EntityRepository $mailTemplateRepository;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly FlowEventInterface $flowEvent,
        private readonly string|null $mailTemplateTechnicalName = null,
    ) {
        if (null === $this->mailTemplateTechnicalName) {
            throw new MissingMailTemplateArgumentException($this);
        }

        /** @var EntityRepository $mailTemplateRepository */
        $mailTemplateRepository = $this->container->get('mail_template.repository');

        $this->mailTemplateRepository = $mailTemplateRepository;
    }

    public function build(Context $context): array
    {
        /**
         * It's impossible that $this->mailTemplateTechnicalName is null
         * because of the checking condition in the constructor.
         *
         * @psalm-suppress PossiblyNullArgument
         */
        $mailTemplateEntity = $this->getMailTemplateByTechnicalName(
            $this->mailTemplateTechnicalName,
            $context,
        );

        if (!$mailTemplateEntity instanceof MailTemplateEntity) {
            return [];
        }

        return [
            'id'           => Uuid::randomHex(),
            'actionName'   => $this->flowEvent::getActionName(),
            'config'       => $this->getConfig($mailTemplateEntity, $context),
            'displayGroup' => 1,
        ];
    }

    public function getConfig(MailTemplateEntity $mailTemplateEntity, Context $context): array
    {
        return [
            'recipient'          => [
                'data' => [],
                'type' => 'default',
            ],
            'mailTemplateId'     => $mailTemplateEntity->getId(),
            'mailTemplateTypeId' => $mailTemplateEntity->getMailTemplateTypeId(),
        ];
    }

    private function getMailTemplateByTechnicalName(
        string $technicalName,
        Context $context,
    ): MailTemplateEntity|null {
        $criteria = new Criteria();

        $criteria
            ->addAssociations([ 'mailTemplateType' ])
            ->addFilter(
                new EqualsFilter('mailTemplateType.technicalName', $technicalName),
            )
        ;

        /** @var MailTemplateEntity|null $mailTemplateEntity */
        $mailTemplateEntity = $this->mailTemplateRepository->search($criteria, $context)->first();

        return $mailTemplateEntity;
    }
}
