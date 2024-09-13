<?php /** @noinspection PhpUnused */
/** @noinspection SqlNoDataSourceInspection */
declare(strict_types=1);
/**
 * Disclaimer: This file is part of the GeoGebra Repository Object plugin for ILIAS.
 */

namespace platform;

use Exception;
use ilDBInterface;
use ilGeoGebraPlugin;

/**
 * Class GeoGebraConfig
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class GeoGebraDatabase
{
    const TABLE_OBJECT = "geogebras";

    private ilDBInterface $db;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    /**
     * Inserts a new row in the database
     *
     * Usage: GeoGebraDatabase->insert('table_name', ['column1' => 'value1', 'column2' => 'value2']);
     *
     * @param string $table
     * @param array $data
     * @return void
     * @throws GeoGebraException
     */
    public function insert(string $table, array $data): void
    {
        $table = $this->formatTableName($table);

        try {
            $this->db->query("INSERT INTO " . $table . " (" . implode(", ", array_keys($data)) . ") VALUES (" . implode(", ", array_map(function ($value) {
                    return $this->db->quote($value);
                }, array_values($data))) . ")");
        } catch (Exception $e) {
            throw new GeoGebraException($e->getMessage());
        }
    }

    /**
     * Inserts a new row in the database, if the row already exists, updates it
     *
     * Usage: GeoGebraDatabase->insertOnDuplicatedKey('table_name', ['column1' => 'value1', 'column2' => 'value2']);
     *
     * @param string $table
     * @param array $data
     * @return void
     * @throws GeoGebraException
     */
    public function insertOnDuplicatedKey(string $table, array $data): void
    {
        $table = $this->formatTableName($table);

        try {
            $this->db->query("INSERT INTO " . $table . " (" . implode(", ", array_keys($data)) . ") VALUES (" . implode(", ", array_map(function ($value) {
                    return $this->db->quote($value);
                }, array_values($data))) . ") ON DUPLICATE KEY UPDATE " . implode(", ", array_map(function ($key, $value) {
                    return $key . " = " . $value;
                }, array_keys($data), array_map(function ($value) {
                    return $this->db->quote($value);
                }, array_values($data)))));
        } catch (Exception $e) {
            throw new GeoGebraException($e->getMessage());
        }
    }

    /**
     * Updates a row/s in the database
     *
     * Usage: GeoGebraDatabase->update('table_name', ['column1' => 'value1', 'column2' => 'value2'], ['id' => 1]);
     *
     * @param string $table
     * @param array $data
     * @param array $where
     * @return void
     * @throws GeoGebraException
     */
    public function update(string $table, array $data, array $where): void
    {
        $table = $this->formatTableName($table);

        try {
            $this->db->query("UPDATE " . $table . " SET " . implode(", ", array_map(function ($key, $value) {
                    return $key . " = " . $value;
                }, array_keys($data), array_map(function ($value) {
                    return $this->db->quote($value);
                }, array_values($data)))) . " WHERE " . implode(" AND ", array_map(function ($key, $value) {
                    return $key . " = " . $value;
                }, array_keys($where), array_map(function ($value) {
                    return $this->db->quote($value);
                }, array_values($where)))));
        } catch (Exception $e) {
            throw new GeoGebraException($e->getMessage());
        }
    }

    /**
     * Deletes a row/s in the database
     *
     * Usage: GeoGebraDatabase->delete('table_name', ['id' => 1]);
     *
     * @param string $table
     * @param array $where
     * @return void
     * @throws GeoGebraException
     */
    public function delete(string $table, array $where): void
    {
        $table = $this->formatTableName($table);

        try {
            $this->db->query("DELETE FROM " . $table . " WHERE " . implode(" AND ", array_map(function ($key, $value) {
                    return $key . " = " . $value;
                }, array_keys($where), array_map(function ($value) {
                    return $this->db->quote($value);
                }, array_values($where)))));
        } catch (Exception $e) {
            throw new GeoGebraException($e->getMessage());
        }
    }

    /**
     * Selects a row/s in the database
     *
     * Usage: GeoGebraDatabase->select('table_name', ['id' => 1]);
     *
     * @param string $table
     * @param array|null $where
     * @param array|null $columns
     * @param string|null $extra
     * @return array
     * @throws GeoGebraException
     */
    public function select(string $table, ?array $where = null, ?array $columns = null, ?string $extra = ""): array
    {
        $table = $this->formatTableName($table);

        try {
            $query = "SELECT " . (isset($columns) ? implode(", ", $columns) : "*") . " FROM " . $table;

            if (isset($where)) {
                $query .= " WHERE " . implode(" AND ", array_map(function ($key, $value) {
                        return $key . " = " . $value;
                    }, array_keys($where), array_map(function ($value) {
                        return $this->db->quote($value);
                    }, array_values($where))));
            }

            if (is_string($extra)) {
                $extra = strip_tags($extra);
                $query .= " " . $extra;
            }

            $result = $this->db->query($query);

            $rows = [];

            while ($row = $this->db->fetchAssoc($result)) {
                $rows[] = $row;
            }

            return $rows;
        } catch (Exception $e) {
            throw new GeoGebraException($e->getMessage());
        }
    }

    /**
     * Returns the next id for a table
     *
     * Usage: GeoGebraDatabase->nextId('table_name');
     *
     * @param string $table
     * @return int
     * @throws GeoGebraException
     */
    public function nextId(string $table): int
    {
        $table = $this->formatTableName($table);

        try {
            return (int) $this->db->nextId($table);
        } catch (Exception $e) {
            throw new GeoGebraException($e->getMessage());
        }
    }


    /**
     * Returns the formatted table name
     *
     * Usage: GeoGebraDatabase->formatTableName('table_name');
     *
     * @param string $table
     * @return string
     */
    private function formatTableName(string $table): string
    {
        return ilGeoGebraPlugin::PLUGIN_ID . "_" . $table;
    }
}