<?php

namespace core;

/**
 * Osztály, amely kezeli a HTTP kérés útvonalának (route) feldolgozását és
 * az engedélyezett HTTP metódusok validálását.
 */
class Router {

    /**
     * A lekérdezés típusa (pl. 'default', 'ajax' stb.), a $_GET['t'] paraméterből.
     * @var string
     */
    private string $queryType;

    /**
     * A jelenlegi HTTP kérés metódusa (GET, POST, stb.).
     * @var string
     */
    private string $currentMethod;

    /**
     * Az engedélyezett HTTP metódusok listája.
     * @var array
     */
    private array $allowedMethods;

    /**
     * Az aktuális vezérlő neve az útvonal alapján (pl. 'home').
     * @var string
     */
    protected string $controller = 'home';

    /**
     * Az aktuális művelet (akció) neve az útvonal alapján (pl. 'index').
     * @var string
     */
    protected string $action = 'index';

    /**
     * Az útvonal további paraméterei tömb formájában.
     * @var array
     */
    protected array $params = [];

    /**
     * Konstruktor, amely beállítja az aktuális HTTP metódust és a lekérdezés típusát,
     * majd feldolgozza az útvonalat ($_GET['q']).
     */
    public function __construct() {
        $this->currentMethod = strtoupper($_SERVER['REQUEST_METHOD']); // Például GET, POST
        $this->queryType = $_GET['t'] ?? 'default';                    // Alapértelmezett lekérdezéstípus

        $this->parseRoute($_GET['q'] ?? '');
    }

    /**
     * Ellenőrzi, hogy a lekérdezés típusa megegyezik-e a megadottal.
     *
     * @param string $ref A lekérdezéstípus összehasonlítási értéke.
     * @return bool       Igaz, ha egyezik, különben hamis.
     */
    public function checkQueryType(string $ref): bool {
        return $this->queryType === $ref;
    }

    /**
     * Visszaadja a lekérdezés típusát.
     *
     * @return string A lekérdezés típusa.
     */
    public function getQueryType(): string {
        return $this->queryType;
    }

    /**
     * Beállítja az engedélyezett HTTP metódusokat, amelyeket a router elfogad.
     *
     * @param array $allowedMethods Az engedélyezett HTTP metódusok tömbje (pl. ['GET', 'POST']).
     * @return $this               Az aktuális Router példány a láncolhatóságért.
     */
    public function setAllowedMethods(array $allowedMethods): self {
        $this->allowedMethods = $allowedMethods;
        return $this;
    }

    /**
     * Ellenőrzi, hogy a jelenlegi HTTP metódus engedélyezett-e.
     *
     * @return bool Igaz, ha engedélyezett, különben hamis.
     */
    public function validateMethod(): bool {
        return in_array($this->currentMethod, $this->allowedMethods, TRUE);
    }

    /**
     * Feldolgozza az útvonal stringet, és beállítja a vezérlő, akció és paramétereket.
     *
     * @param string $route Az útvonal (pl. 'city/add/123').
     * @return void
     */
    private function parseRoute(string $route): void {
        $route = trim($route, '/'); // Levágja a kezdő és végző perjeleket

        if ($route !== '') {
            $segments = explode('/', $route);

            $this->controller = $segments[0] ?? 'home';
            $this->action = $segments[1] ?? 'index';
            $this->params = array_slice($segments, 2);
        }
    }

    /**
     * Visszaadja az aktuális vezérlő nevét.
     *
     * @return string A vezérlő neve.
     */
    public function getController(): string {
        return $this->controller;
    }

    /**
     * Visszaadja az aktuális akció nevét.
     *
     * @return string Az akció neve.
     */
    public function getAction(): string {
        return $this->action;
    }

    /**
     * Visszaadja az aktuális útvonalhoz tartozó paramétereket tömbként.
     *
     * @return array Paraméterek tömbje.
     */
    public function getParams(): array {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getCurrentMethod(): string {
        return $this->currentMethod;
    }

}