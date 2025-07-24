<?php

namespace core;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Class Database
 * Egyszerű PDO wrapper osztály az adatbázis kapcsolathoz és lekérdezésekhez.
 */
class Database {
    private PDO $pdo;

    /**
     * Database constructor.
     * @param string $host Adatbázis szerver címe
     * @param string $dbname Adatbázis neve
     * @param string $user Felhasználónév
     * @param string $password Jelszó
     * @param array $options PDO opciók (opcionális)
     * @throws PDOException
     */
    public function __construct(string $host, string $dbname, string $user, string $password, array $options = []) {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $defaultOptions = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => FALSE,
        ];
        $options = array_replace($defaultOptions, $options);

        $this->pdo = new PDO($dsn, $user, $password, $options);
    }

    /**
     * Egyszerű SELECT lekérdezés végrehajtása.
     * @param string $sql SQL lekérdezés, paraméterezett
     * @param array $params Paraméterek az SQL-hez
     * @return array        Eredmény tömb formában
     */
    public function query(string $sql, array $params = []): array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Egyszerű INSERT/UPDATE/DELETE végrehajtás.
     * @param string $sql
     * @param array $params
     * @return int          A hatott sorok száma
     */
    public function execute(string $sql, array $params = []): int {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Az utolsó beszúrt rekord ID-ja.
     * @return string
     */
    public function lastInsertId(): string {
        return $this->pdo->lastInsertId();
    }

    /**
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    public function fetchOne(string $sql, array $params = []): mixed {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * @param string $query
     * @return PDOStatement
     */
    public function prepare(string $query): PDOStatement {
        return $this->pdo->prepare($query);
    }
}
