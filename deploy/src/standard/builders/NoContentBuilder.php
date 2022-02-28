<?php
namespace kv6002\standard\builders;

use router\Response;

/**
 * A Builder for generating 204 (No Content) responses.
 */
class NoContentBuilder implements Builder {
    /**
     * Return a 204 (No Content) Response.
     * 
     * @param Request $request The HTTP request to handle.
     * @param mixed $args Ignored.
     * @return Response The 204 response to return to the client.
     */
    public function __invoke($request, ...$args) {
        return new Response(204, ["Content-Length" => 0], null);
    }
}
