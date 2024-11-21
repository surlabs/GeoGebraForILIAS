<?php
declare(strict_types=1);
/**
 * Disclaimer: This file is part of the GeoGebra Repository Object plugin for ILIAS.
 */

/**
 * Class ilGeoGebraImporter
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class ilGeoGebraImporter extends ilPageComponentPluginImporter
{
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        $new_id = self::getPCMapping($a_id, $a_mapping);

        $properties = self::getPCProperties($new_id);

        $import_gbb_dir = $this->getImportDirectory() . "/Plugins/GeoGebra";

        if (!is_dir($import_gbb_dir)) {
            $import_gbb_dir = $this->getImportDirectory() . "/Plugins/SrGeogebra";
        }

        $gbb_import_files = [];
        $set_num = 1;
        do {
            $import_gbb_set_dir = $import_gbb_dir . "/set_" . $set_num;
            $exp_num = 1;
            do {
                $import_gbb_set_exp_dir = $import_gbb_set_dir . "/expDir_" . $exp_num;
                $gbb_file = $import_gbb_set_exp_dir . "/" . $properties["legacyFileName"];
                if (file_exists($gbb_file)) {
                    $gbb_import_files[] = $gbb_file;
                }
                $exp_num++;
            } while (is_dir($import_gbb_set_exp_dir));
            $set_num++;
        } while (is_dir($import_gbb_set_dir));

        $dest_gbb_file = ILIAS_WEB_DIR . '/' . CLIENT_ID . "/geogebra/" . $properties["legacyFileName"];

        if (count($gbb_import_files) > 0) {
            $gbb_file = $gbb_import_files[0];

            ilFileUtils::makeDir(dirname($dest_gbb_file));

            copy($gbb_file, $dest_gbb_file);

            $properties["fileName"] = $properties["legacyFileName"];
        }

        self::setPCProperties($new_id, $properties);
    }
}