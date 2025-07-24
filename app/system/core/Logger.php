<?php

namespace core;

use DateTime;
use InvalidArgumentException;

/**
 * Class Logger
 * Egyszerű logolási osztály fájlba íráshoz.
 */
class Logger {
    private const ALLOWED_LEVELS = ['DEBUG', 'INFO', 'WARNING', 'ERROR'];

    private string $logDir;
    private string $logFile;
    private array $config;
    private array $allowedLevels;

    /**
     * Logger constructor.
     *
     * @param string|null $logFile A logfájl neve (pl. "app.log")
     */
    public function __construct(?string $logFile = NULL) {
        $this->config = Environment::get('log', []);

        $this->setAllowedLevels($this->config['allowedLevels'] ?? []);
        $this->setDir($this->config['dir'] ?? '');
        $this->setFilePath($logFile ?? ($this->config['file'] ?? ''));
    }

    /**
     * Beállítja az engedélyezett naplózási szinteket.
     *
     * @param array $levels Pl.: ['ERROR', 'INFO']
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setAllowedLevels(array $levels): self {
        foreach ($levels as $level) {
            if (!in_array($level, self::ALLOWED_LEVELS, TRUE)) {
                throw new InvalidArgumentException("Érvénytelen naplózási szint megadva: '$level'");
            }
        }

        $this->allowedLevels = $levels;

        return $this;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setDir(string $path): self {
        $this->logDir = $path;

        if (!is_dir($path)) {
            mkdir($path, 0775, TRUE);
        }

        return $this;
    }

    /**
     * Beállítja a log fájl elérési útját.
     *
     * @param string $fileName
     * @return void
     */
    private function setFilePath(string $fileName): void {
        $this->logFile = rtrim($this->logDir, '/') . '/' . ltrim($fileName, '/');
    }

    /**
     * @param string $msg
     */
    public function debug(string $msg): void {
        $this->log('DEBUG', $msg);
    }

    /**
     * @param string $msg
     */
    public function info(string $msg): void {
        $this->log('INFO', $msg);
    }

    /**
     * @param string $msg
     */
    public function warning(string $msg): void {
        $this->log('WARNING', $msg);
    }

    /**
     * @param string $msg
     */
    public function error(string $msg): void {
        $this->log('ERROR', $msg);
    }

    /**
     * Naplózás a megadott szinten.
     *
     * @param string $level A naplózási szint (DEBUG, INFO, stb.)
     * @param string $message A naplózandó üzenet
     */
    private function log(string $level, string $message): void {
        if (!in_array($level, $this->allowedLevels, TRUE)) {
            return;
        }

        $date = (new DateTime())->format('Y-m-d H:i:s');
        $logMessage = "[$date][$level] $message" . PHP_EOL;

        // Hozzáfűzés a logfájlhoz
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

}
