Example usage:

```php
<?php

declare(strict_types=1);

namespace NetInventors\ExamplePlugin;

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
use Shopware\Core\Framework\Parameter\AdditionalBundleParameters;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExamplePlugin extends Plugin
{
    private const FALLBACK_ISO_CODE = 'en-GB';

    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);

        if (!$this->container instanceof ContainerInterface) {
            return;
        }

        $pluginInstaller = new PluginInstaller();

        $pluginInstaller->registerInstaller(new MailTemplateInstaller(
            $this->container,
            __DIR__,
            self::FALLBACK_ISO_CODE,
        ));

        $pluginInstaller->registerInstaller(new FlowBuilderInstaller($this->container, __DIR__));

        $pluginInstaller->install($installContext);
    }

    public function update(UpdateContext $updateContext): void
    {
        parent::update($updateContext);

        if (!$this->container instanceof ContainerInterface) {
            return;
        }

        $pluginUpdater = new PluginUpdater();

        $pluginUpdater->registerUpdater(new MailTemplateUpdater($this->container, __DIR__, self::FALLBACK_ISO_CODE));
        $pluginUpdater->registerUpdater(new FlowBuilderUpdater($this->container, __DIR__));


        $pluginUpdater->update($updateContext);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if (!$this->container instanceof ContainerInterface || $uninstallContext->keepUserData()) {
            return;
        }

        $pluginUninstaller = new PluginUninstaller();

        $pluginUninstaller->registerUninstaller(new DatabaseUninstaller($this->container, __NAMESPACE__, __DIR__));
        $pluginUninstaller->registerUninstaller(new FlowBuilderUninstaller($this->container, __DIR__));
        $pluginUninstaller->registerUninstaller(new MailTemplateUninstaller($this->container, __DIR__));

        $pluginUninstaller->uninstall($uninstallContext);
    }
}
```