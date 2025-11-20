## Installation

```sh
composer require netinventors/shopware6-plugin-installer
```

## Example usage

```php
<?php

declare(strict_types=1);

namespace NetInventors\ExamplePlugin;

use Composer\Autoload\ClassLoader;
use Composer\Console\Application;
use NetInventors\Shopware6PluginInstaller\Database\DatabaseUninstaller;
use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowBuilderInstaller;
use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowBuilderUninstaller;
use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowBuilderUpdater;
use NetInventors\Shopware6PluginInstaller\MailTemplate\MailTemplateInstaller;
use NetInventors\Shopware6PluginInstaller\MailTemplate\MailTemplateUninstaller;
use NetInventors\Shopware6PluginInstaller\MailTemplate\MailTemplateUpdater;
use NetInventors\Shopware6PluginInstaller\PluginInstaller;
use NetInventors\Shopware6PluginInstaller\PluginUpdater;
use NetInventors\Shopware6PluginInstaller\PluginUninstaller;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\Plugin\KernelPluginLoader\KernelPluginLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Serializer;

class ExamplePlugin extends Plugin
{
    private const FALLBACK_ISO_CODE = 'en-GB';

    private ClassLoader|null $classLoader = null;

    private Serializer|null $serializer = null;

    private PluginInstaller|null $installer = null;

    private PluginUpdater|null $updater = null;

    private PluginUninstaller|null $uninstaller = null;

    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);

        $this->injectAutoloader(
            'netinventors/shopware6-plugin-installer',
            'NetInventors\\Shopware6PluginInstaller\\',
        );

        $this->getPluginInstaller()->install($installContext);
    }

    public function postInstall(InstallContext $installContext): void
    {
        parent::postInstall($installContext);

        $this->injectAutoloader(
            'netinventors/shopware6-plugin-installer',
            'NetInventors\\Shopware6PluginInstaller\\',
        );

        $this->getPluginInstaller()->postInstall($installContext);
    }

    public function update(UpdateContext $updateContext): void
    {
        parent::update($updateContext);

        $this->injectAutoloader(
            'netinventors/shopware6-plugin-installer',
            'NetInventors\\Shopware6PluginInstaller\\',
        );

        $this->getPluginUpdater()->update($updateContext);
    }

    public function postUpdate(UpdateContext $updateContext): void
    {
        parent::postUpdate($updateContext);

        $this->injectAutoloader(
            'netinventors/shopware6-plugin-installer',
            'NetInventors\\Shopware6PluginInstaller\\',
        );

        $this->getPluginUpdater()->postUpdate($updateContext);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        $this->getPluginUninstaller()->uninstall($uninstallContext);
    }

    public function activate(ActivateContext $activateContext): void
    {
        parent::activate($activateContext);

        $this->getPluginInstaller()->activate($activateContext);
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        parent::deactivate($deactivateContext);

        $this->getPluginUninstaller()->deactivate($deactivateContext);
    }

    public function executeComposerCommands(): bool
    {
        return true;
    }

    private function getContainer(): ContainerInterface
    {
        return $this->container ?? throw new \RuntimeException('Container must be initialized.');
    }

    private function getPluginInstaller(): PluginInstaller
    {
        if (null !== $this->installer) {
            return $this->installer;
        }

        $this->installer = new PluginInstaller();

        $container = $this->getContainer();

        $this->installer->registerInstaller(new MailTemplateInstaller($container, __DIR__, self::FALLBACK_ISO_CODE));
        $this->installer->registerInstaller(new FlowBuilderInstaller($container, __DIR__));

        return $this->installer;
    }

    private function getPluginUpdater(): PluginUpdater
    {
        if (null !== $this->updater) {
            return $this->updater;
        }

        $this->updater = new PluginUpdater();

        $container = $this->getContainer();

        $this->updater->registerUpdater(new MailTemplateUpdater($container, __DIR__, self::FALLBACK_ISO_CODE));
        $this->updater->registerUpdater(new FlowBuilderUpdater($container, __DIR__));

        return $this->updater;
    }

    private function getPluginUninstaller(): PluginUninstaller
    {
        if (null !== $this->uninstaller) {
            return $this->uninstaller;
        }

        $this->uninstaller = new PluginUninstaller();

        $container = $this->getContainer();

        $this->uninstaller->registerUninstaller(new DatabaseUninstaller($container, __NAMESPACE__, __DIR__));
        $this->uninstaller->registerUninstaller(new FlowBuilderUninstaller($container, __DIR__));
        $this->uninstaller->registerUninstaller(new MailTemplateUninstaller($container, __DIR__));

        return $this->uninstaller;
    }

    private function injectAutoloader(string $packageName, string $psr4Prefix, string $path = 'src'): void
    {
        $classLoader = $this->getClassLoader();

        if (\in_array($psr4Prefix, $classLoader->getPrefixesPsr4(), true)) {
            return;
        }

        $classLoader->addPsr4($psr4Prefix, Path::join(
            $this->getContainer()->getParameter('kernel.project_dir'),
            'vendor',
            $packageName,
            $path,
        ));
    }

    private function getClassLoader(): ClassLoader
    {
        if (null !== $this->classLoader) {
            return $this->classLoader;
        }

        /** @var KernelPluginLoader $pluginLoader **/
        $pluginLoader = $this->getContainer()->get(KernelPluginLoader::class);

        return $this->classLoader = $pluginLoader->getClassLoader();
    }

    private function getSerializer(): Serializer
    {
        if (null === $this->serializer) {
            $this->serializer = new Serializer([], [ new JsonEncoder() ]);
        }

        return $this->serializer;
    }
}
```