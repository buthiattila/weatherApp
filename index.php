<?php

/**
 * Alkalmazás belépési pontja (index.php).
 *
 * Ez a fájl inicializálja a környezetet, betölti az autoloadert,
 * létrehozza az útválasztót (Router), majd elindítja az endpoint-kezelést.
 */

// Az alkalmazás gyökérkönyvtárának definiálása
define('ROOT_PATH', realpath(__DIR__));

// Composer és a aaját keretrendszer autoloaderének betöltése
require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/app/system/core/Autoloader.php';

use core\EndpointHandler;
use core\Environment;
use core\Router;

// Környezet inicializálása ('dev', 'prod', 'test')
Environment::init('dev');

// Útválasztó példány létrehozása
$router = new Router();

// EndpointHandler osztály példányosítása és kérések kezelése.
(new EndpointHandler($router))->handle();
