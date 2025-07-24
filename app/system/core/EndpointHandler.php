<?php

namespace core;

/**
 * Végpontválasztó osztály (dispatcher),
 * amely az URL alapján eldönti, melyik kontroller és metódus hajtódjon végre.
 */
class EndpointHandler {

    private Router $router;
    private Logger $logger;

    /**
     * Konstruktor: átveszi a router példányt, és inicializálja a naplózót.
     *
     * @param Router $router A kérés URL-jét értelmező osztály példánya
     */
    public function __construct(Router $router) {
        $this->router = $router;
        $this->logger = new Logger();
    }

    /**
     * Az aktuális kérés alapján meghívja a megfelelő kontroller osztály megfelelő metódusát.
     *
     * @return void
     */
    public function handle(): void {
        // Kontrollerosztály teljes neve, pl. 'module\city\Controller'
        $controllerPrefix = ((!$this->router->checkQueryType('default') && !$this->router->checkQueryType('ajax')) ? ucfirst($this->router->getQueryType()) : '');

        $controllerName = 'module\\' . lcfirst($this->router->getController()) . '\\' . $controllerPrefix . 'Controller';
        $methodName = $this->router->getAction();

        // Ha a kontroller osztály nem létezik, logol és hibaüzenetet ad vissza vagy kilép
        if (!class_exists($controllerName)) {
            $errorMessage = 'Ismeretlen kontroller hívás';
            $this->logger->debug($errorMessage . ': ' . $this->router->getController());

            if ($this->router->checkQueryType('ajax') || $this->router->checkQueryType('api')) {
                (new JsonResponse())->setMessage($errorMessage)->send();
            } else {
                die($errorMessage);
            }
        }

        // Ha a metódus nem létezik az adott kontroller osztályban logol és hibaüzenetet ad vissza vagy kilép
        if (!method_exists($controllerName, $methodName)) {
            $errorMessage = 'Ismeretlen metódus hívás';
            $this->logger->debug($errorMessage . ': ' . $this->router->getController());

            if ($this->router->checkQueryType('ajax') || $this->router->checkQueryType('api')) {
                (new JsonResponse())->setMessage($errorMessage)->send();
            } else {
                die($errorMessage);
            }
        }

        // Kontroller példányosítása, és a megfelelő metódus meghvása
        $instance = new $controllerName($this->router);
        $instance->{$methodName}();
    }
}
