<?php

namespace module\city;

use controller\CronBaseController;
use Exception;
use module\weather\WeatherObject;

/**
 * Városokhoz tartozó időjárási adatok lekérését és mentését végző cron vezérlő.
 *
 * Ez az osztály a CronBaseController absztrakt osztályból származik, így
 * hozzáfér a cron futtatáshoz szükséges alapvető komponensekhez (logger, router, időpont).
 */
class CronController extends CronBaseController {

    /**
     * Városok lekérése, majd az időzített cron kifejezések ellenőrzése után
     * az Open-Meteo API-ból hőmérséklet adatokat gyűjt, és azokat elmenti az adatbázisba.
     *
     * URL: cron/city/fetchAndStoreWeatherData
     *
     * Működés lépései:
     * - Naplózza a cron futás indítását.
     * - Lekéri az összes várost az adatbázisból.
     * - Minden városhoz megvizsgálja, hogy az adott cron időzítés szerint kell-e adatot gyűjteni.
     * - Ha igen, lekéri az aktuális hőmérsékletet a WeatherObject segítségével.
     * - Amennyiben sikerült adatot kapni, elmenti azt.
     * - Naplózza az eseményeket, hibákat és a sikeres mentéseket.
     * - A végén jelzi a cron futás befejezését.
     *
     * @return void
     * @throws Exception
     */
    public function fetchAndStoreWeatherData(): void {
        $this->logger->info("Cron futás elindítva.");

        // WeatherObject példány létrehozása az API hívásokhoz
        $weatherManager = new WeatherObject();

        // Városok lekérése az adatbázisból és feldolgozás
        $cityes = (new CityObject())->getAll();

        foreach ($cityes as $city) {
            $cronExpr = $city['cronExpression'];

            // ha ki van ürítve, nem frissíthető
            if (empty($cronExpr)) {
                continue;
            }

            // Ellenőrzi, hogy a jelenlegi időpont megfelel-e a cron időzítésnek
            if (!$this->schedulerIsDue($cronExpr, $this->now)) {
                $this->logger->debug("Nem időzített adatgyűjtés a városra: {$city['cityName']}, cron: $cronExpr");
                continue;
            }

            $this->logger->info("Adatgyűjtés kezdete: {$city['cityName']}");

            // Hőmérséklet lekérése a koordináták alapján
            $temp = $weatherManager->fetchCurrentTemperature(
                (float)$city['latitude'],
                (float)$city['longitude']
            );

            // Ha nincs adat, naplózza és kihagyja a várost
            if ($temp === NULL) {
                $this->logger->warning("Nem érkezett hőmérséklet adat a városhoz: {$city['cityName']}");
                continue;
            }

            // Hőmérséklet adat mentése az adatbázisba
            $weatherManager->storeToCity($city['id'], $temp);

            $this->logger->info("Hőmérséklet adat elmentve: {$city['cityName']} - {$temp}°C");
        }

        $this->logger->info("Cron futás befejezve.");
    }

}
