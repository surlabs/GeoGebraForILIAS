<?php
declare(strict_types=1);
/**
 * Disclaimer: This file is part of the GeoGebra Repository Object plugin for ILIAS.
 */

use ILIAS\Filesystem\Stream\Streams;

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
        global $DIC;

        $new_id = self::getPCMapping($a_id, $a_mapping);

        $properties = self::getPCProperties($new_id);

        $import_gbb_dir = $this->getImportDirectory() . "/Plugins/GeoGebra";

        if (!is_dir($import_gbb_dir)) {
            $import_gbb_dir = $this->getImportDirectory() . "/Plugins/SrGeogebra";
        }

        $gbb_import_files = $this->findImportFiles(
            $import_gbb_dir,
            [basename((string) ($properties["fileName"] ?? "")), (string) ($properties["legacyFileName"] ?? "")]
        );

        if (count($gbb_import_files) > 0) {
            $gbb_file = $gbb_import_files[0];

            $irss = $DIC->resourceStorage();
            $stakeholder = new StorageStakeHolder();

            $rs = fopen($gbb_file, 'r');

            $stream = Streams::ofResource($rs);

            $resource = $irss->manage()->stream($stream, $stakeholder);

            $properties["fileName"] = $resource->serialize();
        }

        self::setPCProperties($new_id, $properties);
    }

    /**
     * Exports up to ILIAS 9 numbered the sets from 1, newer ones from 0. Exports made with
     * plugin versions 10.0.4 / 11.0.0 left the file in the root of the export.
     */
    private function findImportFiles(string $import_gbb_dir, array $file_names): array
    {
        $search_dirs = glob($import_gbb_dir . "/set_*/expDir_*", GLOB_ONLYDIR) ?: [];
        $search_dirs[] = $this->getImportDirectory();

        $gbb_import_files = [];

        foreach (array_filter($file_names) as $file_name) {
            foreach ($search_dirs as $search_dir) {
                $gbb_file = $search_dir . "/" . $file_name;

                if (is_file($gbb_file)) {
                    $gbb_import_files[] = $gbb_file;
                }
            }
        }

        return $gbb_import_files;
    }
}
