<?php
namespace kv6002\standard\builders;

use router\Response;
use router\resource\ContentTypeSelector;

/**
 * A Builder for generating JSON.
 */
class JSONBuilder implements Builder {
    private $builderFn;

    /**
     * Make a JSONBuilder
     * 
     * @param RequestHandler $builderFn A request handler that returns formatted
     *   JSON content.
     */
    public function __construct($builderFn) {
        $this->builderFn = $builderFn;
    }

    /**
     * Allow the builder function to construct the JSON's object representation,
     * then construct then construct a response from the return value.
     * 
     * The status code is determined by the state of the request.
     * 
     * @param Request $request The HTTP request to handle, or whose processing
     *   caused the error to handle (may be an ErroredRequest).
     * @param mixed $args Any other data to pass down to the handlers.
     * @return Response The response (valid or error) to return to the client.
     */
    public function __invoke($request, ...$args) {
        $builderFn = $this->builderFn; // PHP <=5.6
        $content = $builderFn($request, ...$args);

        // Get the expected response code after the builder is called so that
        // we respect what the builder sets it to (if possible for that kind of
        // request object).
        $code = $request->expectedResponseStatusCode();

        return new Response(
            $code,
            ["Content-Type" => "application/json"],
            json_encode($content)
        );
    }

    /* Static Factory
    -------------------------------------------------- */

    /**
     * A utility method to create a ContentTypeSelector that only supports JSON.
     * 
     * @param RequestHandler $builderFn A request handler that returns formatted
     *   JSON content.
     * @return ContentTypeSelector A ContentTypeSelector that only supports
     *   JSON.
     */
    public static function typeSelector($builderFn) {
        return new ContentTypeSelector([
            "application/json" => new self($builderFn)
        ], "application/json");
    }
}
