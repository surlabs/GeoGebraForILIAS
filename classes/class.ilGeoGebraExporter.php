<?php
declare(strict_types=1);
/**
 * Disclaimer: This file is part of the GeoGebra Repository Object plugin for ILIAS.
 */

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class ilGeoGebraExporter
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class ilGeoGebraExporter extends ilPageComponentPluginExporter
{
    private function exportFile(string $fileName, string $legacyFileName): string
    {
        global $DIC;

        $old_path = ILIAS_WEB_DIR . '/' . CLIENT_ID . "/geogebra/" . $fileName;

        if (!file_exists($old_path)) {
            $irss = $DIC->resourceStorage();
            $path = $irss->consume()->src(new ResourceIdentification($fileName))->getSrc();
        } else {
            $path = $old_path;
        }

        // The stored file name is unique per element, unlike the uploaded name
        $export_file_name = basename($fileName);

        $export_gbb_file = $this->getComponentExportDirectory() . "/" . $export_file_name;

        ilFileUtils::makeDirParents(dirname($export_gbb_file));

        copy($path, $export_gbb_file);

        return "<fileName>$export_file_name</fileName>";
    }

    /**
     * The export handler passes the root of the export as absolute directory, so the
     * file has to be placed in the expDir of the component where the importer looks for it.
     */
    private function getComponentExportDirectory(): string
    {
        try {
            $path_in_container = $this->getExport()->getPathToComponentExpDirInContainer();
        } catch (Error $e) {
            // Legacy export run: the absolute directory already is the expDir of the component
            return $this->getAbsoluteExportDirectory();
        }

        $export_folder = $this->getRelativeExportDirectory() . "/";

        if (str_starts_with($path_in_container, $export_folder)) {
            $path_in_container = substr($path_in_container, strlen($export_folder));
        }

        return $this->getAbsoluteExportDirectory() . "/" . $path_in_container;
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        $prop = self::getPCProperties($a_id);

        $xml = "<GeoGebra>";

        foreach ($prop as $key => $value) {

            if ($key == 'fileName') {
                $xml .= $this->exportFile($value, $prop['legacyFileName']);
            } else {
                $xml .= "<$key>$value</$key>";
            }
        }

        $xml .= "</GeoGebra>";

        return $xml;
    }

    public function init(): void
    {
    }

    public function getValidSchemaVersions(string $a_entity): array
    {
        return array(
            '5.3.0' => array(
                'namespace' => 'http://www.ilias.de/',
                'uses_dataset' => false,
                'min' => '5.3.0',
                'max' => ''
            )
        );
    }
}