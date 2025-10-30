<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\CustomField;

use NetInventors\Shopware6PluginInstaller\CustomField\FieldSet\CustomFieldSetCollection;
use NetInventors\Shopware6PluginInstaller\CustomField\FieldSet\CustomFieldSetCollectionFactory;
use NetInventors\Shopware6PluginInstaller\UninstallerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

final readonly class CustomFieldUninstaller implements UninstallerInterface
{
    private EntityRepository $fieldSetRepository;

    private CustomFieldSetCollection $fieldSetCollection;

    public function __construct(
        private ContainerInterface $container,
        private string $directory,
    ) {
        /** @var EntityRepository $fieldSetRepository */
        $fieldSetRepository = $this->container->get('custom_field_set.repository');

        $this->fieldSetRepository = $fieldSetRepository;
        $this->fieldSetCollection = CustomFieldSetCollectionFactory::create($this->container, $this->directory);
    }

    #[\Override]
    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        $context  = $uninstallContext->getContext();
        $criteria = new Criteria();

        $criteria->addFilter(new EqualsAnyFilter('name', $this->fieldSetCollection->getSetNames()));

        $fieldSetIds = $this->fieldSetRepository->searchIds($criteria, $uninstallContext->getContext())->getIds();

        $data = \array_map(
            static fn ($id) => [ 'id' => $id ],
            $fieldSetIds,
        );

        if ([] === $data) {
            return;
        }

        $this->fieldSetRepository->delete($data, $context);
    }

    #[\Override]
    public function deactivate(DeactivateContext $deactivateContext): void
    {
    }
}
