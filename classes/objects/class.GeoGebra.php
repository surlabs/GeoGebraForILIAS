<?php
declare(strict_types=1);
/**
 * Disclaimer: This file is part of the GeoGebra Repository Object plugin for ILIAS.
 */

namespace objects;

use platform\GeoGebraException;
use platform\GeoGebraDatabase;

/**
 * Class GeoGebra
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class GeoGebra
{
    private int $id = 0;
    private bool $online = false;

    /**
     * @throws GeoGebraException
     */
    public function __construct(?int $id = null)
    {
        if (isset($id) && $id > 0) {
            $this->id = $id;

            $this->loadFromDB();
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): void
    {
        $this->online = $online;
    }

    /**
     * @throws GeoGebraException
     */
    public function loadFromDB(): void
    {
        $database = new GeoGebraDatabase();

        $result = $database->select(GeoGebraDatabase::TABLE_OBJECT, ["id" => $this->getId()]);

        if (isset($result[0])) {
            $this->setOnline((bool) $result[0]["online"]);
        }
    }

    /**
     * @throws GeoGebraException
     */
    public function save(): void
    {
        if (!isset($this->id) || $this->id == 0) {
            throw new GeoGebraException("GeoGebra::save() - GeoGebra ID is 0");
        }

        $database = new GeoGebraDatabase();

        $database->insertOnDuplicatedKey(GeoGebraDatabase::TABLE_OBJECT, array(
            "id" => $this->id,
            "online" => (int) $this->online
        ));
    }

    /**
     * @throws GeoGebraException
     */
    public function delete(): void
    {
        $database = new GeoGebraDatabase();

        $database->delete(GeoGebraDatabase::TABLE_OBJECT, ["id" => $this->id]);

    }
}