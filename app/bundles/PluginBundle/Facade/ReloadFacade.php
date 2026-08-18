<?php

namespace MailVotech\PluginBundle\Facade;

use MailVotech\PluginBundle\Helper\ReloadHelper;
use MailVotech\PluginBundle\Model\PluginModel;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ReloadFacade
{
    public function __construct(
        private PluginModel $pluginModel,
        private ReloadHelper $reloadHelper,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * This method finds all plugins that needs to be enabled, disabled, installed and updated
     * and do all those actions.
     *
     * Returns humanly understandable message about its doings.
     */
    public function reloadPlugins(): string
    {
        $plugins                 = $this->pluginModel->getAllPluginsConfig();
        $pluginMetadata          = $this->pluginModel->getPluginsMetadata();
        $installedPlugins        = $this->pluginModel->getInstalledPlugins();
        $installedPluginTables   = $this->pluginModel->getInstalledPluginTables($pluginMetadata);
        $installedPluginsSchemas = $this->pluginModel->createPluginSchemas($installedPluginTables);
        $disabledPlugins         = $this->reloadHelper->disableMissingPlugins($plugins, $installedPlugins);
        $enabledPlugins          = $this->reloadHelper->enableFoundPlugins($plugins, $installedPlugins);
        $updatedPlugins          = $this->reloadHelper->updatePlugins($plugins, $installedPlugins, $pluginMetadata, $installedPluginsSchemas);
        $installedPlugins        = $this->reloadHelper->installPlugins($plugins, $installedPlugins, $pluginMetadata, $installedPluginsSchemas);
        $persist                 = array_values($disabledPlugins + $enabledPlugins + $updatedPlugins + $installedPlugins);

        $this->pluginModel->saveEntities($persist);

        // Alert the user to the number of additions
        return $this->translator->trans(
            'mailvotech.plugin.notice.reloaded',
            [
                '%added%'    => count($installedPlugins),
                '%disabled%' => count($disabledPlugins),
                '%updated%'  => count($updatedPlugins),
            ],
            'flashes'
        );
    }
}
