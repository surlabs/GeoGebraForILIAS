<?php

declare(strict_types=1);
/**
 * Disclaimer: This file is part of the GeoGebra Repository Object plugin for ILIAS.
 */

use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

/**
 * Class UploadService
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class StorageStakeHolder extends AbstractResourceStakeholder
{
    public function getId(): string
    {
        return 'ggb_file';
    }

    public function getOwnerOfNewResources(): int
    {
        return 6;
    }
}
