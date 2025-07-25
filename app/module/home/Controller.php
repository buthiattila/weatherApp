<?php

namespace module\home;

use Smarty\Smarty;

/**
 * Class Controller
 *
 * Egyszerű vezérlő osztály, amely a kezdőoldal megjelenítéséért felelős.
 * Smarty sablonmotort használ a nézet rendereléséhez.
 */
class Controller {

    /**
     * Az alapértelmezett művelet (index), amely betölti és megjeleníti a felületet.
     *
     * @return void
     */
    public function index(): void {
        $smarty = new Smarty();

        // Sablonok forráskönyvtára
        $smarty->setTemplateDir(__DIR__ . '/templates');

        // Fordított (compile-olt) sablonok tárolási helye
        $smarty->setCompileDir(ROOT_PATH . '/cache/templates');

        // Változó átadása a sablonnak (aktuális idő UNIX timestamp formátumban)
        $smarty->assign('devVersion', 1753458241);

        // Sablon megjelenítése (output a böngészőnek)
        $smarty->display('index.tpl');
    }
}


