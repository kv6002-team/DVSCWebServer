<?php
namespace kv6002\standard;

use util\Util;
use html\HTML;
use dispatcher\Dispatcher;
use router\GlobalErrorResource;
use router\Request;
use router\ErroredRequest;
use router\resource\BasicResource;
use router\resource\WithMetadata;
use router\resource\MetadataUtils;
use router\exceptions\HTTPError;
use kv6002\standard\builders\TextBuilder;
use kv6002\standard\builders\JSONBuilder;
use kv6002\standard\builders\HTMLBuilder;

/**
 * The Resource used to provide the examples for ErrorResource.
 * 
 * @author William Taylor (19009576)
 */
class ExampleResource extends BasicResource {
    public function __construct() {
        parent::__construct([
            "GET" => function ($request) {
                return new Response(
                    200,
                    '{"example" => "request"}',
                    ["Content-Type" => "application/json"]
                );
            }
        ]);
    }

    public function getDefaultContentType() {
        return "application/json";
    }
}

/**
 * The standard generic global error resource that returns user-friendly and
 * computer-readable Responses.
 * 
 * It aims to balance a uniform output format for all errors with usability for
 * some common ones.
 * 
 * Supports the following content types:
 * - text/plain       - plain text
 * - application/json - JSON
 * - text/html        - HTML document
 * 
 * Defaults to returning plain text.
 * 
 * If the error being handled has a source resource (the resource that caused
 * the error), then use that resource's default content type in preference to
 * this resource's default content type (plain text).
 * 
 * @author William Taylor (19009576)
 */
class ErrorResource implements GlobalErrorResource, WithMetadata {
    private static $defaultContentType = "text/plain";

    private $dispatch;
    private $pathfinder;
    private $developmentMode;

    /**
     * Create an ErrorResource.
     * 
     * @param Pathfinder $pathfinder The pathfinder to use.
     * @param bool $developmentMode Whether to provide error details to the
     *   client, or only to the log.
     */
    public function __construct(
            $pathfinder,
            $developmentMode = false
    ) {
        $this->pathfinder = $pathfinder;
        $this->developmentMode = $developmentMode;
        $this->dispatch = new Dispatcher([
            "text/plain" => new TextBuilder([$this, "plainTextGenerator"]),
            "application/json" => new JSONBuilder([$this, "jsonGenerator"]),
            "text/html" => new HTMLBuilder(
                [$this, "htmlGenerator"],
                $pathfinder
            )
        ], self::$defaultContentType);
    }

    /**
     * Return an error Response with an appropriate content type based on the
     * given errored request.
     * 
     * Return a response of the first content type found from the following, in
     * order:
     * 
     * - If the client has given an 'Accept' header, and any content type
     *   requested in that header is supported, then return a response of the
     *   first (most desirable) supported content type the client has requested.
     * 
     * - If the error being handled was caused during processing of the request
     *   by an 'original' resource, and the original resource's default response
     *   content type is supported, then return a response of that content type.
     * 
     * - Otherwise, return a response of the default response content type -
     *   text/plain.
     * 
     * @param ErroredRequest $erroredRequest The request whose processing caused
     *   the error to be represented by this resource.
     * @return Response The error response for this error.
     */
    public function __invoke($erroredRequest) {
        $acceptedContentTypes = $erroredRequest->acceptedContentTypes();

        // No need to try, as there is a global default and that global default
        // is guaranteed to exist (per the constructor).
        $sourceResource = $erroredRequest->getSourceResource();

        $response = $this->dispatch->toFirst(
            $acceptedContentTypes,
            [$erroredRequest],
            $sourceResource !== null ?
                $sourceResource->getDefaultContentType() :
                null
        );
        $response->addHeader("Access-Control-Allow-Origin", "*");
        return $response;
    }

    /* Implement ErrorResource
    -------------------------------------------------- */

    /**
     * Return the default content type for errors.
     * 
     * @return string The default content type.
     */
    public function getDefaultContentType() {
        return self::$defaultContentType;
    }

    /* Generators
    -------------------------------------------------- */

    /**
     * Generate an error message as plain text.
     * 
     * @param ErroredRequest $erroredRequest The errored request to generate an
     *   error response for.
     * @return string A plain text error message.
     */
    public function plainTextGenerator($erroredRequest) {
        $error = $erroredRequest->getError();

        $code = $error->getCode();
        $semantics = $this->getSemantics($code);
        $explanation = $this->getExplanation($code);
        $reason = $error->getReason();
        $detailedReason = $error->getDetailedReason();

        $detail = $this->developmentMode ?
            "\nDetail: $detailedReason" :
            "";

        return (
            "HTTP $code Error ($semantics)"
            ."\nExplanation: $explanation"
            ."\nReason: $reason"
            .$detail
        );
    }

    /**
     * Generate an error message as JSON.
     * 
     * @param ErroredRequest $erroredRequest The errored request to generate an
     *   error response for.
     * @return array<string,mixed> An object representing the error message to
     *   be JSON-encoded.
     */
    public function jsonGenerator($erroredRequest) {
        $error = $erroredRequest->getError();
        $code = $error->getCode();

        $retObj = [
            "code" => $code,
            "semantics" => $this->getSemantics($code),
            "explanation" => $this->getExplanation($code),
            "reason" => $error->getReason()
        ];
        if ($this->developmentMode) {
            $retObj["detailedReason"] = $error->getDetailedReason();
        }

        return $retObj;
    }

    /**
     * Generate an error message as HTML.
     * 
     * @param ErroredRequest $erroredRequest The errored request to generate an
     *   error response for.
     * @return mixed HTML-nestable content containing the error message
     *   ({@see \html\Element}).
     */
    public function htmlGenerator($erroredRequest) {
        $error = $erroredRequest->getError();
        $code = $error->getCode();
        $content = null;

        switch ($code) {
            // More humorous/user-friendly error messages for some common errors
            case 404:
                $header = "Hmm ...";
                $explanation = "We looked, but couldn't find what you were looking for. Sorry :/";
                break;

            case 500:
                $header = "It's not you, it's us :|";
                $explanation = $this->getExplanation(500);
                break;

            // Generic response for all other errors
            default:
                $header = "HTTP $code Error ({$this->getSemantics($code)})";
                $explanation = $this->getExplanation($code);
        }

        // Construct the full error message
        return [
            "header" => HTML::h(1, $header),
            "content" => HTML::div(["class" => "error"], [
                HTML::p($explanation),
                HTML::p("This may give more details:"),
                HTML::p(["class" => "error-reason"], $error->getReason()),
                $this->developmentMode ?
                    HTML::pre(["class" => "error-detailed-reason"],
                        $error->getDetailedReason()
                    ) :
                    null
            ])
        ];
    }

    /* Utilities
    -------------------------------------------------- */

    /**
     * Translate the given HTTP error status code to its semantic definition.
     * 
     * @param int $code The HTTP error code to translate.
     * @return string The semantic definition of the given error code.
     */
    private function getSemantics($code) {
        switch ($code) {
            case 400: $semantics = "Bad Request"; break;
            case 401: $semantics = "Unauthorised"; break;
            case 403: $semantics = "Forbidden"; break;
            case 404: $semantics = "Resource Not Found"; break;
            case 405: $semantics = "Method Not Allowed"; break;
            case 406: $semantics = "Not Acceptable"; break;
            case 422: $semantics = "Unprocessable Entity"; break;
            case 500: $semantics = "Internal Server Error"; break;
            case 501: $semantics = "Not Implemented"; break;

            default: $semantics = "Unknown Error Code"; break;
        }
        return $semantics;
    }

    /**
     * Translate the given HTTP error status code to a human-readable
     * description.
     * 
     * @param int $code The HTTP error code to translate.
     * @return string The human-readable description of the given error code.
     */
    private function getExplanation($code) {
        switch ($code) {
            case 400: $description = "The request was malformed. These kind of errors are caused by errors on our end. We will look into it!"; break;
            case 401: $description = "This resource requires authentication. Please log in before trying to access it. If it is an authentication resource, then try checking your credentials are correct."; break;
            case 403: $description = "Access to the requested resource is restricted. Your account is not authorised to access it."; break;
            case 404: $description = "Could not find the requested resource. Check the URL you typed/used."; break;
            case 405: $description = "Requested a HTTP method that the resource does not support. We will look into it!"; break;
            case 406: $description = "Requested a representation of the resource that it cannot produce. We will look into it!"; break;
            case 422: $description = "Your request was well-formed, but contained invalid data. If your request was for a JSON API, please check your request again. Otherwise, it's probably an error on our end - we will look into it."; break;
            case 500: $description = "Something went wrong on our end (we are not sure what). We will look into it!"; break;
            case 501: $description = "You've found something we haven't made yet. We will try to finish it soon!"; break;

            default: $description = "Unknown error. We didn't expect this to happen. We will look into it!"; break;
        }
        return $description;
    }

    /* Implement WithMetadata
    -------------------------------------------------- */

    // For documentation of what these mean and how they are used, see
    // \router\resource\WithMetadata.

    public function getName() {
        return "Default Global Error Handler";
    }

    public function getDescription() {
        return "Gracefully handles errors caused by other resources.";
    }

    public function getSupportedMethods() {
        return MetadataUtils::getAllMethods();
    }

    public function isAuthenticated($method) {
        MetadataUtils::checkSupportsMethod($this, $method);
        return false;
    }

    public function getSupportedParamsFor($method) {
        MetadataUtils::checkSupportsMethod($this, $method);
        MetadataUtils::checkParamsAllowedFor($method);
        return [];
    }

    public function getBodySpecFor($method) {
        MetadataUtils::checkSupportsMethod($this, $method);
        MetadataUtils::checkBodyAllowedFor($method);
        return "Any body. The body is not used.";
    }

    public function getResponseDescriptionFor($method) {
        MetadataUtils::checkSupportsMethod($this, $method);

        $schema = [
            "code" => "(integer) The HTTP response code",
            "semantics" => (
                "(string) The short descriptive meaning of the HTTP response"
                ." code. Provided for reference."
            ),
            "explanation" => (
                "(string) A simple explanation of the response code and any"
                ." common actions you may be able to take to rectify the"
                ." problem."
            ),
            "reason" => (
                "(string) A potentially more specific reason for the problem."
                ." This may help technical support or developers to rectify the"
                ." problem."
            )
        ];

        $message = [
            "Any non-2xx response, depending on the error raised.",
            "",
            "Various formats are supported for errors. Which format is returned"
            ." depends on the resource requested and any content types"
            ." requested in the 'Accept' header. Regardless, an error response"
            ." will always contain the following information:",
            json_encode($schema, JSON_PRETTY_PRINT)
        ];
        if ($method === "GET") {
            array_push($message,
                "",
                "Note 1: The examples are only provided here (for the GET"
                ." method), but the same kinds of responses are applicable to"
                ." all supported HTTP methods.",
                "Note 2: The examples assume a request was made for a (made up)"
                ." resource that defaults to returning JSON data."
            );
        }
        return implode("\n", $message);
    }

    public function getExampleRequestsFor($method) {
        MetadataUtils::checkSupportsMethod($this, $method);

        // Only provide examples for GET, but it's applicable to all methods
        if ($method !== "GET") return [];

        // An example Resource that could cause an error
        $resource = new ExampleResource();

        // An example exception that Resource could throw (be careful to hide
        // information about how ErrorResource is implemented if not in debug
        // mode).
        try {
            throw new \Exception("Some unexpected error");
        } catch (\Exception $e) {}

        $notFoundPath = "/does/not/exist";
        $notFoundServerPath = $this->pathfinder->serverPathFor($notFoundPath);

        // Examples list
        return [
            // Authentication - 401
            new ErroredRequest(
                new Request("GET", "/any/endpoint"),
                $resource,
                new HTTPError(401, "No authorisation token sent")
            ),
            new ErroredRequest(
                new Request("GET", "/any/endpoint", null, null, null, null, [
                    // The example is "Aladdin" / "open sesame" from:
                    //   https://datatracker.ietf.org/doc/html/rfc7617#section-2
                    "Authorization" => "Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ=="
                ]),
                $resource,
                new HTTPError(401,
                    "Authorisation scheme not supported: 'Basic'"
                )
            ),
            new ErroredRequest(
                new Request("GET", "/any/endpoint", null, null, null, null, [
                    /*
                    {
                        "id": "1",
                        "username": "john@example.com",
                        "iss": "http://unn-w19009576.newnumyspace.co.uk/year3/assignment/part1/api/auth",
                        "iat": 1672105073, <- 27th December, 2022
                        "nbf": 1672105073, <- 27th December, 2022
                        "exp": 1679881073  <- 27th March, 2023
                    }
                    */
                    "Authorization" => (
                        "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6Ij"
                        ."EiLCJ1c2VybmFtZSI6ImpvaG5AZXhhbXBsZS5jb20iLCJpc3MiOiJ"
                        ."odHRwOi8vdW5uLXcxOTAwOTU3Ni5uZXdudW15c3BhY2UuY28udWsv"
                        ."eWVhcjMvYXNzaWdubWVudC9wYXJ0MS9hcGkvYXV0aCIsImlhdCI6M"
                        ."TY3MjEwNTA3MywibmJmIjoxNjcyMTA1MDczLCJleHAiOjE2Nzk4OD"
                        ."EwNzN9.BH_McebG-190z_Enm2kRzlgNPuVTc-6lYoRkGs2iR7M"
                    )
                ]),
                $resource,
                new HTTPError(401,
                    "Auth token not yet valid (this is likely due to an"
                    ." incorrectly set clock)"
                )
            ),
            new ErroredRequest(
                new Request("GET", "/any/endpoint", null, null, null, null, [
                    /*
                    {
                        "id": "1",
                        "username": "john@example.com",
                        "iss": "http://unn-w19009576.newnumyspace.co.uk/year3/assignment/part1/api/auth",
                        "iat": 1609033073, <- 27th December, 2020
                        "nbf": 1609033073, <- 27th December, 2020
                        "exp": 1616809073  <- 27th March, 2021
                    }
                    */
                    "Authorization" => (
                        "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6Ij"
                        ."EiLCJ1c2VybmFtZSI6ImpvaG5AZXhhbXBsZS5jb20iLCJpc3MiOiJ"
                        ."odHRwOi8vdW5uLXcxOTAwOTU3Ni5uZXdudW15c3BhY2UuY28udWsv"
                        ."eWVhcjMvYXNzaWdubWVudC9wYXJ0MS9hcGkvYXV0aCIsImlhdCI6M"
                        ."TYwOTAzMzA3MywibmJmIjoxNjA5MDMzMDczLCJleHAiOjE2MTY4MD"
                        ."kwNzN9.nKy6Eq4eh7XE7F32sUh6wc4w2JJhjLqK7f8TQU26BUE"
                    )
                ]),
                $resource,
                new HTTPError(401, "Auth token expired")
            ),
            new ErroredRequest(
                new Request("GET", "/any/endpoint", null, null, null, null, [
                    /*
                    {
                        "id": "1",
                        "username": "john@example.com",
                        "iss": "http://unn-w19009576.newnumyspace.co.uk/year3/assignment/part1/api/auth",
                        "iat": 1672105073, <- 27th December, 2021
                        "nbf": 1672105073, <- 27th December, 2021
                        "exp": 1679881073  <- 27th March, 2022
                    }
                    ... with the final `q` of the secret replaced with `Q`
                    */
                    "Authorization" => (
                        "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6Ij"
                        ."EiLCJ1c2VybmFtZSI6ImpvaG5AZXhhbXBsZS5jb20iLCJpc3MiOiJ"
                        ."odHRwOi8vdW5uLXcxOTAwOTU3Ni5uZXdudW15c3BhY2UuY28udWsv"
                        ."eWVhcjMvYXNzaWdubWVudC9wYXJ0MS9hcGkvYXV0aCIsImlhdCI6M"
                        ."TY0MDU2OTA3MywibmJmIjoxNjQwNTY5MDczLCJleHAiOjE2NDgzND"
                        ."UwNzN9.bsOFiHxYutpknqlcSKxB3YNUCrwNqVk9sP3hFzBpfI4"
                    )
                ]),
                $resource,
                new HTTPError(401, "Auth token signature invalid")
            ),
            new ErroredRequest(
                new Request("GET", "/any/endpoint", null, null, null, null, [
                    /*
                    {
                        "id": "10",        <- User does not exist
                        "username": "john@example.com", <- Ignored - for human use only
                        "iss": "http://unn-w19009576.newnumyspace.co.uk/year3/assignment/part1/api/auth",
                        "iat": 1672105073, <- 27th December, 2021
                        "nbf": 1672105073, <- 27th December, 2021
                        "exp": 1679881073  <- 27th March, 2022
                    }
                    */
                    "Authorization" => (
                        "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6Ij"
                        ."EwIiwidXNlcm5hbWUiOiJqb2huQGV4YW1wbGUuY29tIiwiaXNzIjo"
                        ."iaHR0cDovL3Vubi13MTkwMDk1NzYubmV3bnVteXNwYWNlLmNvLnVr"
                        ."L3llYXIzL2Fzc2lnbm1lbnQvcGFydDEvYXBpL2F1dGgiLCJpYXQiO"
                        ."jE2NDA1NjkwNzMsIm5iZiI6MTY0MDU2OTA3MywiZXhwIjoxNjQ4Mz"
                        ."Q1MDczfQ.PtrEVOY3IXdxHjWGbbkiPc0FLBs6w3oQKbAOIoZ_h6M"
                    )
                ]),
                $resource,
                new HTTPError(401, "User given in auth token does not exist")
            ),

            // Others - 404, 405, 406, 500, 501
            new ErroredRequest(
                new Request("GET", $notFoundPath),
                $resource,
                new HTTPError(404,
                    "Resource at '{$notFoundServerPath}' not found"
                )
            ),
            new ErroredRequest(
                new Request("GET", "/any/endpoint"),
                $resource,
                new HTTPError(405,
                    "This resource does not support the GET method"
                )
            ),
            new ErroredRequest(
                new Request("GET", "/any/endpoint", null, null, null, null, [
                    "Accept" => "text/html"
                ]),
                $resource,
                new HTTPError(406,
                    "None of the content types in 'text/html' are supported by"
                    ." this resource"
                )
            ),
            new ErroredRequest(
                new Request("GET", "/any/endpoint", ["something" => "broken"]),
                $resource,
                new HTTPError(500,
                    "Unknown internal error",
                    $e->getMessage()
                    ."\n{$e->getTraceAsString()}"
                )
            ),
            new ErroredRequest(
                new Request("GET", "/any/endpoint"),
                $resource,
                new HTTPError(501,
                    "This resource is currently not implemented"
                )
            )
        ];
    }
}
