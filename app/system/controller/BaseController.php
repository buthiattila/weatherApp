<?php

namespace controller;

use core\JsonResponse;
use core\Router;

abstract class BaseController {

    protected Router $router;
    protected JsonResponse $jsonResponse;

    public function __construct(Router $router) {
        $this->router = $router;
        $this->jsonResponse = new JsonResponse();
    }

}
