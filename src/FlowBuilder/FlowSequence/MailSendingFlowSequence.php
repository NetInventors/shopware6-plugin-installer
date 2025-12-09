<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowSequence;

use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowEvent\FlowEventInterface;
use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowSequence\Exception\MissingMailTemplateArgumentException;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;

final readonly class MailSendingFlowSequence implements FlowSequenceInterface
{
    private EntityRepository $mailTemplateRepository;

    private EntityRepository $documentTypeRepository;

    /**
     * @param list<string> $documentTypes
     */
    public function __construct(
        private ContainerInterface $container,
        private FlowEventInterface $flowEvent,
        private string|null $mailTemplateTechnicalName = null,
        private array $documentTypes = [],
    ) {
        if (null === $this->mailTemplateTechnicalName) {
            throw new MissingMailTemplateArgumentException($this);
        }

        /** @var EntityRepository $mailTemplateRepository */
        $mailTemplateRepository = $this->container->get('mail_template.repository');

        $this->mailTemplateRepository = $mailTemplateRepository;

        /** @var EntityRepository $documentTypeRepository */
        $documentTypeRepository = $this->container->get('document_type.repository');

        $this->documentTypeRepository = $documentTypeRepository;
    }

    #[\Override]
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

    #[\Override]
    public function getConfig(MailTemplateEntity $mailTemplateEntity, Context $context): array
    {
        $config = [
            'recipient'          => [
                'data' => [],
                'type' => 'default',
            ],
            'mailTemplateId'     => $mailTemplateEntity->getId(),
            'mailTemplateTypeId' => $mailTemplateEntity->getMailTemplateTypeId(),
        ];

        $documentTypes = $this->getDocumentTypeIds($context);

        if ([] !== $documentTypes) {
            $config['documentTypeIds'] = $documentTypes;
        }

        return $config;
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

    /**
     * @return list<string|array<string, string>>
     */
    private function getDocumentTypeIds(Context $context): array
    {
        if ([] === $this->documentTypes) {
            return [];
        }

        return $this->documentTypeRepository->searchIds(
            (new Criteria())->addFilter(new EqualsAnyFilter('technicalName', $this->documentTypes)),
            $context,
        )->getIds();
    }
}
