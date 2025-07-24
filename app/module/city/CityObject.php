<?php

namespace module\city;

use controller\ObjectBaseController;

/**
 * Class CityManager
 * Városok kezelése adatbázisból.
 */
class CityObject extends ObjectBaseController {

    public function __construct() {
        parent::__construct();
        $this->item = new CityModel();
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setCountryName(string $name): self {
        $this->item->countryName = $name;
        return $this;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setCountryOsmId(int $id): self {
        $this->item->countryOsmId = $id;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setCityName(string $name): self {
        $this->item->cityName = $name;
        return $this;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setCityOsmId(int $id): self {
        $this->item->cityOsmId = $id;
        return $this;
    }

    /**
     * @param float $lat
     * @return $this
     */
    public function setLat(float $lat): self {
        $this->item->lat = $lat;
        return $this;
    }

    /**
     * @param float $lon
     * @return $this
     */
    public function setLon(float $lon): self {
        $this->item->lon = $lon;
        return $this;
    }

    /**
     * @param string $frequency
     * @return $this
     */
    public function setFrequency(string $frequency): self {
        $this->item->frequency = $frequency;
        return $this;
    }

    // --- Getters ---
    public function getCityData(): CityModel {
        return $this->item;
    }

    /**
     * Ellenőrzi, hogy adott OSMID-val szerepel-e már rekord az adatbázisban
     * @return bool
     */
    public function checkExist(): bool {
        $existing = $this->db->fetchOne("SELECT id FROM cities WHERE cityOsmId = :cityOsmId", [
            ':cityOsmId' => $this->item->cityOsmId,
        ]);

        if ($existing) {
            $this->logger->info("A város már létezik az adatbázisban: {$this->item->cityName} ({$this->item->cityOsmId})");
            return true;
        }

        return false;
    }

    /**
     * Város adatainak lekérdezése ID alapján.
     * @param int $id
     * @return array|null
     */
    public function get(int $id): ?array {
        $result = $this->db->query("SELECT * FROM cities WHERE id = :id", [':id' => $id]);

        return $result[0] ?? NULL;
    }

    /**
     * Összes város lekérdezése.
     * @return array
     */
    public function getAll(): array {
        return $this->db->query("SELECT * FROM cities ORDER BY countryName, cityName");
    }

    /**
     * Város rögzítése és ID visszatérés
     * @return int
     */
    public function store(): int {
        $this->db->execute("INSERT INTO cities (countryName, countryOsmId, cityName, cityOsmId, latitude,longitude,cronExpression) VALUES (:countryName, :countryOsmId, :cityName, :cityOsmId, :latitude, :longitude, :cronExpression)",
            [
                ':countryName'    => $this->item->countryName,
                ':countryOsmId'   => $this->item->countryOsmId,
                ':cityName'       => $this->item->cityName,
                ':cityOsmId'      => $this->item->cityOsmId,
                ':latitude'       => $this->item->lat,
                ':longitude'      => $this->item->lon,
                ':cronExpression' => $this->item->frequency,
            ]
        );

        $id = (int)$this->db->lastInsertId();
        $this->logger->info("Új város hozzáadva: {$this->item->cityName}, {$this->item->countryName} (ID: $id)");

        return $id;
    }

    /**
     * Város módosítása.
     * @param int $id
     * @param array $fields Módosítandó mezők (pl. ['city'=>'Debrecen', 'cron_expression'=>"5 * * * *"])
     * @return bool
     */
    public function update(int $id, array $fields): bool {
        $sets = [];
        $params = [];

        foreach ($fields as $field => $value) {
            $sets[] = "$field = :$field";
            $params[":$field"] = $value;
        }
        $params[':id'] = $id;

        $affected = $this->db->execute("UPDATE cities SET " . implode(', ', $sets) . " WHERE id = :id", $params);
        $this->logger->info("Város (ID: $id) módosítva: " . json_encode($fields));

        return $affected > 0;
    }

    /**
     * Város törlése.
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $affected = $this->db->execute("DELETE FROM cities WHERE id = :id", [':id' => $id]);

        if ($affected) {
            $this->logger->info("Város törölve (ID: $id)");
            return TRUE;
        }

        return FALSE;
    }
}
