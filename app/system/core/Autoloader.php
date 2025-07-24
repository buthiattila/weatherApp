<?php

namespace core;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class Autoloader
 *
 * Egy egyszerű automatikus osztálybetöltő (autoloader) implementáció,
 * amely adott könyvtárakban rekurzívan beolvassa a PHP fájlokat,
 * és betölti őket az osztálynév alapján.
 *
 * Az osztály támogatja az osztálynév -> fájlnév konverziót camelCase alapokon,
 * illetve a fájlok egyszeri indexelését az optimális betöltés érdekében.
 */
class Autoloader {

    /**
     * @var bool $firstStart Jelzi, hogy ez az első betöltés, ekkor indexelünk.
     */
    protected static bool $firstStart = TRUE;

    /**
     * @var string $fileExt Betöltendő fájlok kiterjesztése (általában 'php').
     */
    protected static string $fileExt = 'php';

    /**
     * @var array $fileIterator Az összes beolvasott fájl abszolút elérési útja.
     */
    protected static array $fileIterator = [];

    /**
     * @var array $loadedFiles Az osztálynév és fájl párok gyorsítótára a gyorsabb betöltéshez.
     */
    protected static array $loadedFiles = [];

    /**
     * @var array $paths A keresési útvonalak listája, ahonnan betöltünk.
     */
    protected static array $paths = [];

    /**
     * Automatikus betöltő metódus, amit a PHP hív az osztálynév alapján.
     *
     * @param string $className Teljes névtérrel ellátott osztálynév.
     * @return void
     */
    public static function loader(string $className): void {
        // Az osztálynév namespace szeparátorait fájl elválasztóra cseréljük, és a kiterjesztést hozzáadjuk.
        $relativeClass = str_replace('\\', '/', $className) . '.' . self::$fileExt;

        // Ha ez az első betöltés, akkor végigmegyünk a megadott keresési útvonalakon,
        // és beolvassuk az összes PHP fájlt, hogy gyorsabb legyen a későbbi betöltés.
        if (self::$firstStart) {
            foreach (self::$paths as $path) {
                self::scanDirectory($path);
            }
            self::$firstStart = FALSE;
        }

        // Megpróbáljuk betölteni a fájlt a beolvasott fájlok listájából.
        self::requireFileFromIterator($relativeClass);
    }

    /**
     * Rekurzívan beolvassa a megadott könyvtárat, és hozzáadja a PHP fájlokat a listához.
     *
     * @param string $dir A beolvasandó könyvtár abszolút vagy relatív elérési útja.
     * @return void
     */
    private static function scanDirectory(string $dir): void {
        $directoryIterator = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($iterator as $file) {
            // Csak a megadott kiterjesztésű fájlokat vesszük figyelembe.
            if ($file->isFile() && $file->getExtension() === self::$fileExt) {
                self::$fileIterator[] = $file->getPathName();
            }
        }
    }

    /**
     * Megpróbálja betölteni a fájlt az osztálynév alapján az indexelt fájlokból.
     * Ha még nincs betöltve, végigmegy az összes fájlon, és ha megtalálja,
     * require_once-tal behívja, majd cache-eli az eredményt.
     *
     * @param string $relativeClass Az osztálynévből képzett relatív fájlnév.
     * @return void
     */
    private static function requireFileFromIterator(string $relativeClass): void {
        // Ha már betöltöttük ezt az osztályt, akkor gyorsan behívjuk.
        if (array_key_exists($relativeClass, self::$loadedFiles)) {
            require_once self::$loadedFiles[$relativeClass];
        } else {
            // Egyébként megpróbáljuk megtalálni a megfelelő fájlt a beolvasott fájlok között.
            foreach (self::$fileIterator as $filePath) {
                // Kétféle összehasonlítást végzünk: pontos egyezés és camelCase alapú konvertált fájlnév.
                if (str_ends_with($filePath, $relativeClass) || str_ends_with($filePath, self::convertClassNameToFileName($relativeClass))) {
                    self::$loadedFiles[$relativeClass] = $filePath;

                    require_once $filePath;
                    break;
                }
            }
        }
    }

    /**
     * Osztálynév camelCase -> kisbetűs pontozott fájlnév konvertálása.
     * Pl: SomeClassName.php -> some.class.name.php
     *
     * @param string $className Az osztálynév (vagy relatív fájlnév).
     * @return string A konvertált fájlnév.
     */
    public static function convertClassNameToFileName(string $className): string {
        // Először szétszedjük camelCase részekre, majd pontokkal összefűzzük,
        // és az első betűt kisbetűssé alakítjuk.
        return lcfirst(implode('.', self::explodeCamelCase($className)));
    }

    /**
     * CamelCase sztring feldarabolása szavakra.
     * Pl: 'SomeClassName' => ['Some', 'Class', 'Name']
     *
     * @param string $input A camelCase formátumú string.
     * @return array Szavak tömbje.
     */
    private static function explodeCamelCase(string $input): array {
        // Pozitív lookbehind és lookahead reguláris kifejezés a kis- és nagybetűk mentén történő felbontáshoz.
        return preg_split('/(?<=\p{Ll})(?=\p{Lu})/u', $input);
    }

    /**
     * Új keresési útvonal hozzáadása az autoloader számára.
     *
     * @param array|string $path Egy vagy több könyvtár útvonala.
     * @return void
     */
    public static function addPath(array|string $path): void {
        // Egységesítjük, hogy mindig tömb legyen.
        if (!is_array($path)) {
            $path = [$path];
        }

        foreach ($path as $item) {
            self::$paths[] = $item;
        }
    }

}

// Alapértelmezett útvonal hozzáadása a rendszer könyvtárához.
Autoloader::addPath('app');

// Az autoloader regisztrálása a PHP SPL autoload rendszerébe.
spl_autoload_register('core\Autoloader::loader');
