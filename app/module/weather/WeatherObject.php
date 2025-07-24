<?php

namespace module\weather;

use controller\ObjectBaseController;

/**
 * Class WeatherObject
 *
 * Open-Meteo API kliens, amely felelős a hőmérsékleti adatok lekéréséért és tárolásáért.
 *
 * Ez az osztály HTTP kéréseket küld az Open-Meteo API-nak, hogy aktuális időjárási adatokat szerezzen
 * meg adott földrajzi koordináták alapján, majd az adatokat elmenti az adatbázisba.
 *
 * Az öröklődés révén az ObjectBaseController osztály adatbázis kapcsolatot és logger szolgáltatást biztosít.
 */
class WeatherObject extends ObjectBaseController {

    /**
     * Alapértelmezett Open-Meteo API URL az időjárás előrejelzés lekéréséhez.
     *
     * @var string
     */
    private string $baseUrl = 'https://api.open-meteo.com/v1/forecast';

    /**
     * Lekéri az aktuális hőmérsékletet Celsius fokban a megadott földrajzi koordinátákra.
     *
     * Összeállítja a megfelelő API URL-t, lekéri a JSON választ, és feldolgozza az adatokat.
     * Ha az adat lekérése vagy értelmezése sikertelen, null értékkel tér vissza.
     *
     * @param float $latitude A földrajzi szélesség (pl. 47.4979)
     * @param float $longitude A földrajzi hosszúság (pl. 19.0402)
     *
     * @return float|null Visszaadja az aktuális hőmérsékletet Celsius fokban,
     *                    vagy null értéket, ha nincs elérhető adat vagy hiba történt.
     */
    public function fetchCurrentTemperature(float $latitude, float $longitude): ?float {
        $url = sprintf(
            '%s?latitude=%s&longitude=%s&current_weather=true',
            $this->baseUrl,
            urlencode((string)$latitude),
            urlencode((string)$longitude)
        );

        $this->logger->debug("Lekérdezés az Open-Meteo API-ról: $url");

        $response = @file_get_contents($url);
        if ($response === FALSE) {
            $this->logger->error("Nem sikerült lekérni az adatokat az Open-Meteo API-ról");
            return NULL;
        }

        $data = json_decode($response, TRUE);
        if (!isset($data['current_weather']['temperature'])) {
            $this->logger->warning("Nincs elérhető aktuális hőmérséklet adat a válaszban");
            return NULL;
        }

        $temp = (float)$data['current_weather']['temperature'];
        $this->logger->info("Aktuális hőmérséklet: {$temp}°C (lat: $latitude, lon: $longitude)");
        return $temp;
    }

    /**
     * Ment egy adott városhoz tartozó hőmérséklet adatot az adatbázisba.
     *
     * A metódus egy SQL INSERT parancsot hajt végre, amely eltárolja a város azonosítóját,
     * a mért hőmérsékletet, valamint az aktuális időpontot (NOW()).
     *
     * @param int $cityId Az adott város adatbázisbeli azonosítója
     * @param float $temp A mért hőmérséklet Celsius fokban
     *
     * @return void
     */
    public function storeToCity(int $cityId, float $temp) {
        $sql = "INSERT INTO weather_data (city_id, temperature, recorded_at) VALUES (:city_id, :temperature, NOW())";
        $params = [
            ':city_id'     => $cityId,
            ':temperature' => $temp,
        ];
        $this->db->execute($sql, $params);
    }

    /**
     * Lekéri az adott városhoz tartozó hőmérsékleti adatokat időrendi sorrendben.
     *
     * Az adatokat az adatbázisból szedi ki, a rekordok időpont szerint növekvő sorrendben.
     * Az eredmény egy asszociatív tömbökből álló tömb, ahol minden elem tartalmazza
     * az adat rögzítésének időpontját és a hőmérséklet értékét.
     *
     * @param int $cityId Az adott város azonosítója
     *
     * @return array Egy tömb, amely minden eleme az adott város hőmérsékleti adatát tartalmazza:
     *              [
     *                  ['recorded_at' => '2025-07-23 15:00:00', 'temperature' => 25.3],
     *                  ...
     *              ]
     */
    public function getDataForCity(int $cityId): array {
        $stmt = $this->db->prepare("SELECT recorded_at, temperature FROM weather_data WHERE city_id = :city_id ORDER BY recorded_at ASC");
        $stmt->execute([':city_id' => $cityId]);

        return $stmt->fetchAll();
    }

    /**
     * Lekéri az adott városhoz tartozó legfrissebb hőmérséklet adatot.
     *
     * @param int $cityId A város egyedi azonosítója.
     * @return array|null A legfrissebb adat (recorded_at és temperature mezőkkel), vagy null, ha nincs ilyen adat.
     */
    public function getLatestDataForCity(int $cityId): ?array {
        $stmt = $this->db->prepare("SELECT recorded_at, temperature FROM weather_data WHERE city_id = :city_id ORDER BY recorded_at DESC LIMIT 1");
        $stmt->execute([':city_id' => $cityId]);

        return $stmt->fetch() ?: null;
    }

}
