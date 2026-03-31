<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use NetInventors\Shopware6PluginInstaller\UninstallerInterface;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

class DatabaseUninstaller implements UninstallerInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly string $namespace,
        private readonly string $directory,
    ) {
    }

    /**
     * @throws Exception
     */
    public function uninstall(UninstallContext $uninstallContext): void
    {
        if (!$uninstallContext->keepUserData()) {
            /** @var Connection $connection */
            $connection  = $this->container->get(Connection::class);
            $destructors = $this->sortByDependencies($this->getEntityDefinitionDestructors());

            foreach ($destructors as $destructor) {
                $destructor->destruct($connection);
            }
        }
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
    }

    /**
     * @return array<class-string<EntityDefinitionDestructorInterface>, EntityDefinitionDestructorInterface>
     */
    private function getEntityDefinitionDestructors(): array
    {
        $destructors = [];

        foreach ($this->getPluginClasses() as $class) {
            $classRelativePath = \str_replace(
                [ '.php', '/' ],
                [ '', '\\' ],
                $class->getRelativePathname(),
            );

            $fqcn = $this->namespace . '\\' . $classRelativePath;

            if (
                AbstractEntityDefinitionDestructor::class === $fqcn
                || !\is_subclass_of($fqcn, EntityDefinitionDestructorInterface::class)
            ) {
                continue;
            }

            $destructors[$fqcn] = new $fqcn();
        }

        return $destructors;
    }

    /**
     * @param array<class-string<EntityDefinitionDestructorInterface>, EntityDefinitionDestructorInterface> $destructors
     *
     * @return array<class-string<EntityDefinitionDestructorInterface>, EntityDefinitionDestructorInterface>
     */
    private function sortByDependencies(array $destructors): array
    {
        $sorted = [];
        $done   = [];

        while (\count($destructors) > \count($sorted)) {
            $countBefore = \count($sorted);

            foreach ($destructors as $destructorClass => $destructor) {
                if (isset($done[$destructorClass])) {
                    continue;
                }

                $resolved = true;

                /** @var class-string<EntityDefinitionDestructorInterface> $dependency */
                foreach ($destructor->getDependencies() as $dependency) {
                    if (!isset($done[$dependency])) {
                        $resolved = false;

                        break;
                    }
                }

                if ($resolved) {
                    $done[$destructorClass]   = true;
                    $sorted[$destructorClass] = $destructor;
                }
            }

            if (\count($sorted) === $countBefore) {
                $cycle = \implode(', ', \array_keys(\array_diff_key($destructors, $sorted)));

                throw new \RuntimeException("Circular dependency detected among entity destructors: $cycle");
            }
        }

        return $sorted;
    }

    private function getPluginClasses(): Finder
    {
        $finder = new Finder();

        $finder
            ->in($this->directory)
            ->exclude('Resources')
        ;

        return $finder->files()->name('*.php');
    }
}
