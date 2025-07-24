<?php

namespace core;

/**
 * Környezeti beállításokat kezelő osztály.
 * Konfigurációs adatokat tölt be és biztosít hozzáférést hozzájuk több szintű kulcsok segítségével.
 */
class Environment {

    /**
     * Tárolja a konfigurációs adatokat.
     * @var array
     */
    private static mixed $data = [];

    /**
     * Inicializálja az adott típushoz tartozó környezeti beállításokat.
     * Betölti a konfigurációs fájlt az 'environments' mappából.
     *
     * @param string $type A környezet típusa (pl. 'production', 'development').
     * @return void
     */
    public static function init(string $type): void {
        self::$data = require_once 'environments/' . $type . '.php';
    }

    /**
     * Többrétegű kulcs alapján lekéri a konfigurációs beállítást.
     * A kulcsok egymástól '|' jellel vannak elválasztva (pl. 'database|host').
     * Ha a megadott kulcs nem létezik, a default értéket adja vissza.
     *
     * @param string $key A lekérdezni kívánt beállítás kulcsa (többrétegű, '|' elválasztóval).
     * @param mixed|null $default Az alapértelmezett érték, ha a kulcs nem található.
     * @return mixed A beállítás értéke vagy a default.
     */
    public static function get(string $key, mixed $default = null): mixed {
        $result = $default;

        if (!empty($key)) {
            $keys = explode('|', $key);
            $keysCount = count($keys);
            $var = self::$data;

            foreach ($keys as $i => $keyName) {
                if (isset($var[$keyName])) {
                    $var = $var[$keyName];

                    if ($i === ($keysCount - 1)) {
                        $result = $var;
                    }
                } else {
                    // Ha bármelyik kulcs nem létezik, kilépünk és visszaadjuk a default értéket
                    break;
                }
            }
        }

        return $result;
    }

}
