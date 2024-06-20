<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

use NetInventors\Shopware6PluginInstaller\UninstallerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

final readonly class MailTemplateUninstaller implements UninstallerInterface
{
    private MailTemplateCollection $mailTemplateCollection;

    private EntityRepository $mailTemplateTypeRepository;

    private EntityRepository $mailTemplateRepository;

    public function __construct(
        private ContainerInterface $container,
        private string $directory,
    ) {
        /** @var EntityRepository $mailTemplateTypeRepository */
        $mailTemplateTypeRepository = $this->container->get('mail_template_type.repository');

        /** @var EntityRepository $mailTemplateRepository */
        $mailTemplateRepository = $this->container->get('mail_template.repository');

        $this->mailTemplateTypeRepository = $mailTemplateTypeRepository;
        $this->mailTemplateRepository     = $mailTemplateRepository;
        $this->mailTemplateCollection     = MailTemplateCollectionFactory::create($this->directory);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        $context            = $uninstallContext->getContext();
        $technicalMailNames = $this->mailTemplateCollection->getTechnicalNames();

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('technicalName', $technicalMailNames));

        /** @var array<int<0, max>, string> $mailTemplateTypeIds */
        $mailTemplateTypeIds = $this->mailTemplateTypeRepository->searchIds($criteria, $context)->getIds();

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('mailTemplateTypeId', $mailTemplateTypeIds));

        $mailTemplateIds = $this->mailTemplateRepository->searchIds($criteria, $context)->getIds();

        $this->removeIds($this->mailTemplateRepository, $mailTemplateIds, $context);
        $this->removeIds($this->mailTemplateTypeRepository, $mailTemplateTypeIds, $context);
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
    }

    private function removeIds(EntityRepository $repository, array $ids, Context $context): void
    {
        $ids = \array_map(static fn ($id) => [ 'id' => $id ], $ids);

        if (0 < \count($ids)) {
            $repository->delete($ids, $context);
        }
    }
}
