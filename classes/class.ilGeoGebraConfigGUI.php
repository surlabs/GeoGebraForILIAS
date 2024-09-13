<?php
declare(strict_types=1);
/**
 * Disclaimer: This file is part of the GeoGebra Repository Object plugin for ILIAS.
 */

use platform\GeoGebraConfig;

/**
 * Class ilGeoGebraConfigGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_IsCalledBy  ilGeoGebraConfigGUI: ilObjComponentSettingsGUI
 */
class ilGeoGebraConfigGUI extends ilPluginConfigGUI
{
    private GeoGebraConfig $config;

    public function performCommand(string $cmd): void
    {
        $this->{$cmd}();
    }

    private function configure(): void
    {

    }
}