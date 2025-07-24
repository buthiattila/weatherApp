<?php

namespace controller;

use core\Environment;
use core\Logger;
use core\Router;
use Cron\CronExpression;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * Alaposztály (szülőosztály) a CRON végpontokat kezelő osztályok számára.
 *
 * Ez az osztály biztosítja az alapvető közös funkciókat, mint a router kezelését
 * a logger példányosítását és a cron-analóg feldolgozót.
 */
abstract class CronBaseController {

    protected Router $router;
    protected Logger $logger;
    protected DateTime $now;

    /**
     * @param Router $router
     * @throws Exception
     */
    public function __construct(Router $router) {
        $this->router = $router;
        $this->logger = new Logger(Environment::get('cron|logFile'));
        $this->now = new DateTime('now', new DateTimeZone('Europe/Budapest'));
    }

    /**
     * Eldönti, hogy egy adott cron kifejezés alapján aktuális-e a futtatás ideje.
     *
     * @param string $cronExpression
     * @param DateTime|null $now Opció, ha most mikor nézzük (alapértelmezett: most)
     * @return bool
     * @throws Exception
     */
    public function schedulerIsDue(string $cronExpression, ?DateTime $now = NULL): bool {
        $now = $now ?? new DateTime('now', new DateTimeZone('Europe/Budapest'));

        try {
            $cron = CronExpression::factory($cronExpression);

            return $cron->isDue($now);
        }
        catch (Exception $e) {
            // Hibás cron kifejezés esetén false
            return FALSE;
        }
    }

}
