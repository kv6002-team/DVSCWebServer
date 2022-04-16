<?php
namespace kv6002\standard\builders;

use router\ErroredRequest;
use router\Response;
use router\resource\ContentTypeSelector;
use kv6002\standard\Page;

/**
 * A Builder for generating standard HTML pages.
 */
class HTMLBuilder implements Builder {
    private $builderFn;
    private $pathfinder;

    /**
     * Make a HTMLBuilder.
     * 
     * @param RequestHandler $builderFn A request handler that returns HTML-
     *   nestable content ({@see \html\Element}).
     * @param Pathfinder $pathfinder The pathfinder to use for the HTML page
     *   template (eg. for navigation links).
     */
    public function __construct($builderFn, $pathfinder) {
        $this->builderFn = $builderFn;
        $this->pathfinder = $pathfinder;
    }

    /**
     * Crete a standard page, allow the builder function to construct the page's
     * body, then construct a response from the return value.
     * 
     * The status code is determined by the state of the request.
     * 
     * @param Request $request The HTTP request to handle, or whose processing
     *   caused the error to handle (may be an ErroredRequest).
     * @param mixed $args Any other data to pass down to the handler.
     * @return Response The response (valid or error) to return to the client.
     */
    public function __invoke($request, ...$args) {
        $builderFn = $this->builderFn; // PHP <=5.6
        $content = $builderFn($request, ...$args);

        // Get the expected response code after the builder is called so that
        // we respect what the builder sets it to (if possible for that kind of
        // request object).
        $code = $request->expectedResponseStatusCode();

        $page = new Page($this->pathfinder);
        if (is_array($content) && isset($content["content"])) {
            $page->getComponent("header")->setContent(
                isset($content["header"]) ? $content["header"] : null
            );
            $page->getComponent("content")->setContent($content["content"]);

        } else {
            $page->getComponent("content")->setContent($content);
        }

        return new Response($code, ["Content-Type" => "text/html"], $page);
    }

    /* Static Factory
    -------------------------------------------------- */

    /**
     * A utility method to create a ContentTypeSelector that only supports HTML.
     * 
     * @param RequestHandler $builderFn A request handler that returns HTML-
     *   nestable content ({@see \html\Element}).
     * @param Pathfinder $pathfinder The pathfinder to use for the HTML page
     *   template (eg. for navigation links).
     * 
     * @return ContentTypeSelector A ContentTypeSelector that only supports
     *   HTML.
     */
    public static function typeSelector($builderFn, $pathfinder) {
        return new ContentTypeSelector([
            "text/html" => new self($builderFn, $pathfinder)
        ], "text/html");
    }
}
