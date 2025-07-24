<?php

namespace module\city;

class CityModel {

    public string $countryName = '';
    public int $countryOsmId = 0;
    public string $cityName = '';
    public int $cityOsmId = 0;
    public float $lat = 0;
    public float $lon = 0;
    public string $frequency = '0 * * * *';

    public function toArray(): array {
        return [
            'countryName'  => $this->countryName,
            'countryOsmId' => $this->countryOsmId,
            'cityName'     => $this->cityName,
            'cityOsmId'    => $this->cityOsmId,
            'lat'          => $this->lat,
            'lon'          => $this->lon,
            'frequency'    => $this->frequency,
        ];
    }

}

