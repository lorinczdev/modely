<?php

namespace Lorinczdev\Modely\Routing;

use Exception;

class UnknownRouteException extends Exception
{
    public function __construct(string $modelClass, string $action, string $method = null)
    {
        $methodString = '';

        if ($method) {
            $methodString = "(" . strtoupper($method) . ")";
        }

        $message = "Route for action " . $methodString . "$action on model [{$modelClass}] was not registered.";

        parent::__construct($message);
    }
}
