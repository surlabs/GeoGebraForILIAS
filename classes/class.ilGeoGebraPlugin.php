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

    protected function uninstallCustom(): void
    {
        // TODO: Implement uninstallCustom() method.
    }

    public function isValidParentType(string $a_type): bool
    {
        return true;
    }
}
