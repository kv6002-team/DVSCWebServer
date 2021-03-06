<?php
namespace kv6002\resources;

use dispatcher\Dispatcher;
use router\resource\BasicResource;
use kv6002\standard\builders\JSONBuilder;

/**
 * A ping API. Allows you to check for connectivity to the API.
 * 
 * @author William Taylor (19009576)
 */
class Ping extends BasicResource {
    public function __construct() {
        $responder = JSONBuilder::typeSelector(
            function ($request) {
                return ["pong"];
            }
        );
        parent::__construct([
            "GET" => $responder,
            "POST" => $responder,
            "PUT" => $responder,
            "PATCH" => $responder,
            "DELETE" => $responder,
            "OPTIONS" => $responder,
        ]);
    }
}
