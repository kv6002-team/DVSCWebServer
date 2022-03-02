<?php
namespace kv6002\standard\builders;

use router\RequestHandler;
use router\ErroredRequestHandler;

/**
 * Acts as a class-based Closure over a callable. When called, Builders provide
 * generic wrappers (mainly object creation) around common client Response
 * formats. Builders have the common "take a Request (and possibly other
 * arguments) and return a Response" interface from the router package, and are
 * useful when interacting with the generic router classes (such as
 * BasicResource and ContentTypeSelector).
 */
interface Builder extends RequestHandler, ErroredRequestHandler {
    /**
     * Handle a request, or an error caused while handling the request, by
     * allowing the user to build the specific response, while handling the
     * generic parts of constructing a Response for the relevant content type.
     * 
     * @param Request $request The HTTP request to handle, or whose processing
     *   caused the error to handle (may be an ErroredRequest).
     * @param mixed $args Any other data to pass down to the handlers.
     * @return Response The response (valid or error) to return to the client.
     */
    public function __invoke($request, ...$args);
    /* To allow this to handle ordinary requests and errors, while allowing
     * passing down any other dependencies (eg. fetched data to view), the
     * method signature should be changed from the above. Instead, new kinds of
     * Request should be made to indicate additional information about the
     * current status of the request-handling process. THis is why
     * ErroredRequest was created, rather than passing in the error code
     * separately.
     */
}
