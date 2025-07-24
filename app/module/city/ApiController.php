<?php

namespace module\city;

use controller\ApiBaseController;
use core\Validator;
use module\weather\WeatherObject;

class ApiController extends ApiBaseController {

    /**
     * Az API végpont, amely a városokhoz tartozó időjárási adatokat adja vissza JSON formátumban.
     * URL [GET]: api/city/data
     *
     * Működés:
     * - Csak GET metódust engedélyez.
     * - Ha a kérés nem GET, hibás választ küld vissza.
     * - Lekéri az összes várost.
     * - Minden városhoz lekéri az időjárási adatokat.
     * - Az adatokat rendezett tömbbé alakítja, amely tartalmazza a város adatait és az időjárás mért értékeit.
     * - JSON választ küld vissza a kliensnek.
     *
     * @return void
     */
    public function data(): void {
        $this->validateHTTPMethod(['GET']);

        // WeatherObject és CityObject példányok létrehozása az adatok lekéréséhez
        $weatherManager = new WeatherObject();
        $cityManager = new CityObject();

        $cities = $cityManager->getAll();

        $result = [];

        // Városonkénti feldolgozás
        foreach ($cities as $city) {
            // Az adott városhoz tartozó időjárási adatok lekérése
            $data = $weatherManager->getDataForCity($city['id']);

            // Az adatokat az elvárt formátumba alakítja
            $result[] = [
                'id'      => $city['id'],
                'city'    => $city['cityName'],
                'country' => $city['countryName'],
                'data'    => array_map(function ($entry) {
                    return [
                        'recorded_at' => $entry['recorded_at'],
                        'temperature' => (float)$entry['temperature']
                    ];
                }, $data)
            ];
        }

        // Sikeres JSON válasz összeállítása és elküldése
        $this->jsonResponse
            ->setSuccess(TRUE)
            ->setParam('cities', $result)
            ->send();
    }

    /**
     * Prometheus formátumban adja vissza a legfrissebb hőmérsékleti adatokat.
     * URL [GET]: api/city/metrics
     *
     * @return void
     */
    public function metrics(): void {
        $this->validateHTTPMethod(['GET']);

        $weatherManager = new WeatherObject();
        $cityManager = new CityObject();
        $cities = $cityManager->getAll();

        $temperatureData = [];

        // Városonkénti feldolgozás
        foreach ($cities as $city) {
            // Az adott városhoz tartozó legfrissebb időjárási adatok lekérése
            $entry = $weatherManager->getLatestDataForCity($city['id']);

            $temperatureData[] = [
                'id'          => $city['id'],
                'city'        => $city['cityName'],
                'temperature' => (float)$entry['temperature'] ?? '-',
            ];
        }

        $lines = [
            '# HELP current_temperature_celsius Aktuális hőmérséklet Celsius fokban.',
            '# TYPE current_temperature_celsius gauge'
        ];

        foreach ($temperatureData as $data) {
            $city = addcslashes($data['city'], "\"\\"); // idézőjelek escape
            $temperature = $data['temperature'];
            $lines[] = "current_temperature_celsius{city=\"$city\"} $temperature";
        }

        $this->jsonResponse
            ->setSuccess(TRUE)
            ->setParam('metrics', implode("\n", $lines))
            ->send();
    }

    /**
     * frissíti PATCH metódus alapján a város adatait
     * URL [PATCH]: api/city/update/{id}
     *
     * @return void
     */
    protected function update(): void {
        $this->validateHTTPMethod(['PATCH']);

        $id = (int)$this->routerParams[0];

        // A PATCH adatok JSON formátumban érkeznek
        $input = json_decode(file_get_contents("php://input"), TRUE);

        if (!is_array($input) || empty($input)) {
            $this->jsonResponse->setMessage("Érvénytelen vagy hiányzó JSON adat.")->send();
        }

        // Lekérjük az adott várost
        $cityManager = new CityObject();
        $city = $cityManager->get($id);

        if (empty($city)) {
            $this->jsonResponse->setMessage("A megadott azonosítóval nem található város.")->send();
        }

        // Módosítható mezők listája
        $updatableFields = ['frequency'];

        $validator = new Validator();
        $fields = [];

        // Csak azokat dolgozzuk fel, amelyek szerepelnek az engedélyezett mezők listáján
        foreach ($input as $field => $value) {
            if (!in_array($field, $updatableFields, TRUE)) {
                continue;
            }

            $rule = match ($field) {
                'frequency' => 'cronExperiment',
            };

            $validator->add($field, $value, $rule);

            $fields[$field] = $value;
        }

        if (!$validator->validate()) {
            $this->jsonResponse->setMessage($validator->getErrorMessage())->send();
            return;
        }

        if (!$cityManager->update($id, $fields)) {
            $this->jsonResponse->setMessage("A város módosítása nem sikerült.")->send();
            return;
        }

        $this->jsonResponse->setSuccess(TRUE)->setMessage("A város sikeresen módosítva.")->send();
    }

    /**
     * Az API végpont, amely a várost töröl.
     * URL [DELETE]: api/city/delete/{id}
     *
     * @return void
     */
    protected function delete(): void {
        $this->validateHTTPMethod(['DELETE']);

        $id = (int)$this->routerParams[0];

        $cityManager = new CityObject();
        $city = $cityManager->get($id);

        if (empty($city)) {
            $this->jsonResponse->setMessage("A megadott azonosítóval nem található város.")->send();
        }

        // város tényleges törlése az adatbázisból
        $deleteResult = $cityManager->delete($id);

        if (!$deleteResult) {
            $this->jsonResponse->setMessage("A város törlése nem sikerült")->send();
        }

        $this->jsonResponse->setSuccess(TRUE)->setMessage("A város törlése sikerült")->send();
    }

}
