<?php

namespace module\city;

use controller\BaseController;
use core\Validator;
use module\weather\WeatherObject;

class Controller extends BaseController {

    /**
     * Város adatainak hozzáadása POST kéréssel.
     *
     * Működés:
     * - POST adatok fogadása és megtisztítása (szűrés, sanitization).
     * - Bejövő adatok érvényesítése a Validator osztállyal.
     * - Létezés ellenőrzése, hogy ne legyen duplikált város.
     * - Város mentése az adatbázisba.
     * - Azonnali hőmérséklet adat lekérése és tárolása, ha elérhető.
     * - JSON válasz visszaküldése a művelet eredményéről.
     *
     * @return void
     */
    public function add(): void {
        // POST-ból érkező adatok kinyerése és tisztítása
        $countryName = filter_input(INPUT_POST, 'countryName', FILTER_SANITIZE_STRING);
        $countryOsmId = filter_input(INPUT_POST, 'countryOsmId', FILTER_SANITIZE_STRING);
        $cityName = filter_input(INPUT_POST, 'cityName', FILTER_SANITIZE_STRING);
        $cityOsmId = filter_input(INPUT_POST, 'cityOsmId', FILTER_SANITIZE_STRING);
        $lat = filter_input(INPUT_POST, 'lat', FILTER_SANITIZE_STRING);
        $lon = filter_input(INPUT_POST, 'lon', FILTER_SANITIZE_STRING);
        $frequency = filter_input(INPUT_POST, 'frequency', FILTER_SANITIZE_STRING);

        // Érvényesítő objektum létrehozása
        $validator = new Validator();

        // Érvényesítési szabályok hozzáadása
        $validator
            ->add('countryName', $countryName, 'required|text')
            ->add('countryOsmId', $countryOsmId, 'required|integer')
            ->add('cityName', $cityName, 'required|text')
            ->add('cityOsmId', $cityOsmId, 'required|integer')
            ->add('lat', $lat, 'required|float')
            ->add('lon', $lon, 'required|float')
            ->add('frequency', $frequency, 'required|cronExperiment');

        // Ha az érvényesítés nem sikerül, JSON hibaüzenettel válaszol
        if (!$validator->validate()) {
            $this->jsonResponse
                ->setMessage($validator->getErrorMessage())
                ->send();
            return;
        }

        // Város objektum létrehozása és adatainak beállítása
        $cityManager = new CityObject();
        $cityManager->setCountryName($countryName)
            ->setCountryOsmId($countryOsmId)
            ->setCityName($cityName)
            ->setCityOsmId($cityOsmId)
            ->setLat($lat)
            ->setLon($lon)
            ->setFrequency($frequency);

        // Ellenőrzi, hogy a város már létezik-e az adatbázisban
        if ($cityManager->checkExist()) {
            $this->jsonResponse
                ->setMessage('A város már korábban rögzítve lett')
                ->send();
            return;
        }

        // Város adatainak mentése az adatbázisba
        $itemId = $cityManager->store();

        // Ha mentés sikertelen, hibaüzenettel válaszol
        if (!$itemId) {
            $this->jsonResponse
                ->setMessage('A mentés nem sikerült')
                ->send();
            return;
        }

        // Azonnali hőmérséklet adat lekérése és tárolása, ha sikeres
        $weather = new WeatherObject();
        $temp = $weather->fetchCurrentTemperature((float)$lat, (float)$lon);
        if ($temp !== NULL) {
            $weather->storeToCity($itemId, $temp);
        }

        // Sikeres válasz küldése
        $this->jsonResponse
            ->setSuccess(TRUE)
            ->setMessage('A mentés sikerült')
            ->send();
    }

}
