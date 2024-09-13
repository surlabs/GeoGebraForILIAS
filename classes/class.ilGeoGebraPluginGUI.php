<?php
declare(strict_types=1);
/**
 * Disclaimer: This file is part of the GeoGebra Repository Object plugin for ILIAS.
 */

/**
 * Class ilObjGeoGebraGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_isCalledBy ilObjGeoGebraGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI
 * @ilCtrl_Calls      ilObjGeoGebraGUI: ilObjectCopyGUI, ilPermissionGUI, ilInfoScreenGUI, ilCommonActionDispatcherGUI
 */
class ilGeoGebraPluginGUI extends ilPageComponentPluginGUI
{
    public function executeCommand(): void
    {
        global $DIC;

        $cmd = $DIC->ctrl()->getCmd();

        $this->{$cmd}();
    }

    public function insert(): void
    {

    }

    public function edit(): void
    {

    }

    public function create(): void
    {

    }

    public function getElementHTML(string $a_mode, array $a_properties, string $plugin_version): string
    {
        return '';
    }
}