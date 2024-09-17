<?php
declare(strict_types=1);
/**
 * Disclaimer: This file is part of the GeoGebra Repository Object plugin for ILIAS.
 */

/**
 * Class ilGeoGebraPlugin
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class ilGeoGebraPlugin extends ilPageComponentPlugin
{
    const PLUGIN_ID = 'srgg';
    private static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            global $DIC;

            $component_repository = $DIC["component.repository"];

            $info = $component_repository->getPluginByName("GeoGebra");

            $component_factory = $DIC["component.factory"];

            $plugin_obj = $component_factory->getPlugin($info->getId());

            self::$instance = $plugin_obj;
        }

        return self::$instance;
    }

    protected function uninstallCustom(): void
    {
        // TODO: Implement uninstallCustom() method.
    }

    public function isValidParentType(string $a_type): bool
    {
        return true;
    }
}
