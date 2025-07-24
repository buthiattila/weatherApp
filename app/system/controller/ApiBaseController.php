<?php

namespace controller;

use core\JsonResponse;
use core\Router;

/**
 * Alaposztály (szülőosztály) az API végpontokat kezelő osztályok számára.
 *
 * Ez az osztály biztosítja az alapvető közös funkciókat, mint a router kezelését
 * és a JSON válaszok előállítását.
 */
abstract class ApiBaseController {

    /**
     * @var Router Az útválasztó (router) példány, amely az API hívások feldolgozásáért felel.
     */
    protected Router $router;
    protected array $routerParams;

    /**
     * @var JsonResponse A JSON formátumú válaszokat előállító objektum.
     */
    protected JsonResponse $jsonResponse;


    /**
     * @param Router $router Az útválasztó példány, amely az aktuális kérés paramétereit tartalmazza.
     */
    public function __construct(Router $router) {
        $this->router = $router;
        $this->routerParams = $this->router->getParams();
        $this->jsonResponse = new JsonResponse();

        if (in_array($this->router->getCurrentMethod(), ['DELETE', 'PATCH'])) {
            $this->handleRequest();
        }
    }

    /**
     * Kezeli a HTTP kéréseket és ellenőrzi, hogy csak 'id' paraméter szerepel, és hogy az szám.
     */
    protected function handleRequest(): void {
        // Ellenőrizzük, hogy csak az 'id' paraméter szerepel
        if (count($this->routerParams) !== 1) {
            $this->jsonResponse
                ->setMessage("A használt metódushoz kizárólag egy 'id' paraméter engedélyezett.")
                ->send();
            exit;
        }

        // Ellenőrizzük, hogy az 'id' numerikus
        $id = $this->routerParams[0];
        if (!ctype_digit($id)) {
            $this->jsonResponse
                ->setMessage("Az 'id' paraméter csak pozitív egész szám lehet.")
                ->send();
            exit;
        }
    }

    protected function validateHTTPMethod(array $allowedMethods): void {
        // Csak a megadott metódusok engedélyezése az API végpont számára
        $this->router->setAllowedMethods($allowedMethods);

        // Metódus ellenőrzése, ha nem megfelelő, JSON hibaüzenettel kilép
        if (!$this->router->validateMethod()) {
            $this->jsonResponse
                ->setMessage("A jelenlegi végpontra a választott metódus nem engedélyezett")
                ->send();
        }
    }

}
