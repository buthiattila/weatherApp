<?php

namespace controller;

use core\Database;
use core\Environment;
use core\Logger;

abstract class ObjectBaseController {

    protected mixed $item = [];
    protected Logger $logger;
    protected Database $db;

    public function __construct() {
        $dbConfig = Environment::get('db');

        $this->logger = new Logger();
        $this->db = new Database(...$dbConfig);
    }

}
