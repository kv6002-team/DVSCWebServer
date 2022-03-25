<?php
namespace router;

use util\Util;

/**
 * An request from the client for a specified resource.
 * 
 * Includes information about the requested URL and important headers.
 * 
 * @author William Taylor (19009576)
 */
class Request {
    private static $BASIC_FORM_TYPE = "application/x-www-form-urlencoded";
    private static $MULTIPART_FORM_TYPE = "multipart/form-data";

    // GET /some/endpoint
    private $method;
    private $endpoint;
    private $endpointScheme;

    // The inputs and selected outputs
    private $params;
    private $privateParams; // 'private' because they can be encrypted (HTTPS)
    private $endpointParams; // from the URL, eg. /api/endpoint/1523
    private $body;
    private $fragment;

    // Headers
    private $headers;

    /* Constructors
    -------------------------------------------------- */

    /**
     * Construct an immutable Request.
     * 
     * @param string $method The Request's method. Must be a valid HTTP request
     *   method.
     * @param string $endpoint The requested resource's endpoint.
     * @param array<string,string> $params (Optional) The requested parameters
     *   for the resource.
     * @param array<string,string> $privateParams (Optional) The requested
     *   private parameters for the resource. These are those passed in the body
     *   as url-encoded form data.
     * @param mixed $body The body for this request.
     * @param string $fragment (Optional) The requested fragment of the
     *   resource.
     * @param array<string,string> $headers (Optional) The headers sent with
     *   this request.
     */
    public function __construct(
            $method,
            $endpoint,
            $endpointScheme = null,
            $params = null,
            $privateParams = null,
            $endpointParams = null,
            $body = null,
            $fragment = null,
            $headers = null
    ) {
        if ($params === null) $params = [];
        if ($privateParams === null) $privateParams = [];
        if ($endpointParams === null) $endpointParams = [];
        if ($headers === null) $headers = [];

        $this->method = $method;
        $this->endpoint = $endpoint;

        $this->endpointScheme = $endpointScheme;
        $this->params = $params;
        $this->privateParams = $privateParams;
        $this->endpointParams = $endpointParams;
        $this->body = $body;
        $this->fragment = $fragment;
        $this->headers = $headers;
    }

    /**
     * Construct and return a Request from global PHP state.
     */
    public static function fromPHPGlobalState() {
        $method = $_SERVER["REQUEST_METHOD"];

        $url = parse_url($_SERVER["REQUEST_URI"]);
        $headers = getallheaders();
        $body = file_get_contents("php://input");

        $endpoint = rtrim($url["path"], "/");
        $params = isset($url["query"]) ? self::parseQueryStr($url["query"]) : [];
        $fragment = isset($url["fragment"]) ? $url["fragment"] : null;

        // PHP special-cases some method/content-type combinations and parses
        // them automatically for 'convenience', so I have to as well.
        // They can't be un-parsed without significant effort and special-
        // casing, and this class shouldn't be responsible for decoding all
        // of the possible content types. Splitting those responsibilities out
        // into other classes and an interface would be over-engineering for
        // this project.
        // This is the best I could come up with to break apart (yet leaverage)
        // PHP's magic parsing.
        switch ($_SERVER["REQUEST_METHOD"]) {
            case "POST":
                $privateParams = array_diff_key($_POST, $params);
                break;

            default:
                if (
                        isset($_SERVER["CONTENT_TYPE"]) &&
                        $_SERVER["CONTENT_TYPE"] === self::$BASIC_FORM_TYPE
                ) {
                    $privateParams = parseQueryStr($body);

                } else {
                    // A self::$MULTIPART_FORM_TYPE parser is too complex to
                    // implement here. This effectively makes multipart form
                    // data ignored by Request.
                    // FIXME: This should be fixed at some point, but isn't
                    //        required for this assignment.
                    $privateParams = [];
                }
        }

        return new self(
            $method,
            $endpoint,

            null, // endpointScheme - we don't know yet
            $params,
            $privateParams,
            null, // endpointParams - we don't know yet
            $body,
            $fragment,

            $headers
        );
    }

    /* Mutators
    -------------------------------------------------- */

    /**
     * Parse the stored endpoint based on the given endpoint scheme and, if the
     * scheme is valid for the requested enpoint, add the endpoint scheme and
     * parsed endpoint params to the request.
     * 
     * @param string $scheme The endpoint scheme to attempt to set.
     * @return bool True if setting the endpoint scheme was successful, false
     *   otherwise.
     */
    public function setEndpointScheme($scheme) {
        // Split into URL segments
        $schemeParts = explode("/", ltrim(rtrim($scheme, "/"), "/"));
        $endpointParts = explode("/", ltrim($this->endpoint, "/")); // Already rtrimed

        // Cannot match if different lengths (number of parts)
        if (count($schemeParts) !== count($endpointParts)) {
            return false;
        }

        // Match scheme <-> endpoint
        $endpointParams = [];
        for ($i = 0; $i < count($schemeParts); $i++) {
            if (strlen($schemeParts[$i]) > 0 && $schemeParts[$i][0] === ":") {
                // Remove ID tag
                $schemeParts[$i] = ltrim($schemeParts[$i], ":");

                // Extract tag type if exists
                $type = null;
                if ($schemeParts[$i][strlen($schemeParts[$i])-1] === ">") {
                    $schemeParts[$i] = rtrim($schemeParts[$i], ">");
                    list($name, $type) = explode("<", $schemeParts[$i]);
                    $schemeParts[$i] = $name;
                }

                // Extract tag name and value
                $name = $schemeParts[$i];
                $value = $endpointParts[$i];

                // Parse type
                switch ($type) {
                    case null:
                    case "str":
                    case "string":
                        break; // Already a string

                    case "int":
                    case "integer":
                        if (!is_numeric($value) || str_contains($value, ".")) {
                            return false;
                        }
                        $value = intval($value);
                        break;

                    default:
                        break; // Don't know how to parse it - keep as a string
                }

                // Add param
                $endpointParams[$name] = $value;

            // If not a parameter, and the parts don't match exactly, then fail
            } else if ($schemeParts[$i] !== $endpointParts[$i]) {
                return false;
            }
        }

        $this->endpointScheme = $scheme;
        $this->endpointParams = $endpointParams;
        return true;
    }

    /* Getters
    -------------------------------------------------- */

    /**
     * Return the HTTP method of the request.
     * 
     * @return string The HTTP method of the request.
     */
    public function method() {
        return $this->method;
    }

    /**
     * Return the endpoint reqested.
     * 
     * @return string The endpoint requested.
     */
    public function endpoint() {
        return $this->endpoint;
    }

    /**
     * Return the parameters for this request.
     * 
     * @return array<string,string> The parameters for this request.
     */
    public function params() {
        return $this->params;
    }

    /**
     * Return the given parameter for this request.
     * 
     * @param string $name The name of the parameter to get.
     * @return string The value of the requested parameter, or null if it was
     *   not given.
     */
    public function param($name) {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

    /**
     * Return the private parameters for this request.
     * 
     * Private parameters are those passed in the body as url-encoded form data.
     * 
     * @return array<string,string> The private parameters for this request.
     */
    public function privateParams() {
        return $this->privateParams;
    }

    /**
     * Return the given private parameter for this request.
     * 
     * Private parameters are those passed in the body as url-encoded form data.
     * 
     * @param string $name The name of the private parameter to get.
     * @return string The value of the requested private parameter, or null if
     *   was not given.
     */
    public function privateParam($name) {
        // Returns null if $name doesn't exist
        return isset($this->privateParams[$name]) ?
            $this->privateParams[$name] :
            null;
    }

    /**
     * Return the endpoint parameters for this request.
     * 
     * Endpoint parameters are those passed in the URL itself, eg.
     * "/api/things/154" if parsed using the endpoint scheme
     * "/api/things/:id<int>" will translate:
     * - the endpoint into the endpoint scheme - "/api/things/:id<int>"
     * - the endpoint params into ["id" => 154]
     * 
     * @return array<string,string> The endpoint parameters for this request.
     */
    public function endpointParams() {
        return $this->endpointParams;
    }

    /**
     * Return the given endpoint parameter for this request.
     * 
     * Endpoint parameters are those passed in the URL itself, eg.
     * "/api/things/154" if parsed using the endpoint scheme
     * "/api/things/:id<int>" will translate:
     * - the endpoint into the endpoint scheme - "/api/things/:id<int>"
     * - the endpoint params into ["id" => 154]
     * 
     * @param string $name The name of the endpoint parameter to get.
     * @return string The value of the requested endpoint parameter, or null if
     *   was not given.
     */
    public function endpointParam($name) {
        // Returns null if $name doesn't exist
        return isset($this->endpointParams[$name]) ?
            $this->endpointParams[$name] :
            null;
    }

    /**
     * Return the fragment identifier.
     * 
     * @return string The fragment identifier.
     */
    public function fragment() {
        return $this->fragment;
    }

    /**
     * Return the raw body in this request.
     * 
     * This excludes private parameters (those passed in the body as url-encoded
     * form data.)
     * 
     * @return string The raw body in this Request.
     */
    public function body() {
        return $this->body;
    }

    /**
     * Return the raw headers in this request.
     * 
     * This is needed to construct a subclass of Request.
     * 
     * @return array<string,string> The raw headers in this Request.
     */
    protected function headers() {
        return $this->headers;
    }

    /**
     * Return the value of the given named header, or null if it was not sent in
     * the request.
     * 
     * @return string The value of the given header, or null if the header was
     *   not given.
     */
    public function header($header) {
        return isset($this->headers[$header]) ? $this->headers[$header] : null;
    }

    /* Header-Parsing Utils 
    -------------------------------------------------- */

    /**
     * Return the list of accepted content types, in descending order of
     * preference.
     * 
     * @return array<string> The list of accepted content types, in descending
     *   order of preference. Returns an empty list if no content types were
     *   requested.
     */
    public function acceptedContentTypes() {
        $accept = isset($this->headers["Accept"]) ?
            $this->headers["Accept"] :
            null;

        if (empty($accept)) return [];

        $rawParts = explode(",", $accept);

        $contentTypesUnordered = Util::mapValues($rawParts, function ($part) {
            $typeAndQuality = explode(";q=", $part);
            if (count($typeAndQuality) < 2) {
                $typeAndQuality[1] = 1;
            } else {
                // This doesn't enforce the letter of the spec (Q-values should
                // be between 0 and 1), but does it matter that much?
                $typeAndQuality[1] = floatval($typeAndQuality[1]);
            }
            return $typeAndQuality;
        });

        $contentTypesOrdered = Util::sortValues(
            $contentTypesUnordered,
            function ($a, $b) {
                return $b[1] - $a[1]; // Reverse order of quality factor
            }
        );

        $contentTypes = Util::mapValues(
            $contentTypesOrdered,
            function ($contentType) {
                return $contentType[0]; // The content types
            }
        );

        return $contentTypes;
    }

    /**
     * Return the type from the 'Authorization' header, or null if there is no
     * 'Authorization' header.
     * 
     * @return string The auth type from the 'Authorization' header, or null if
     *   no authorisation was given.
     */
    public function authType() {
        $auth = isset($this->headers["Authorization"]) ?
            $this->headers["Authorization"] :
            null;
        if ($auth === null) return null; // No auth

        list($type, $value) = explode(" ", $auth, 2);
        return $type;
    }

    /**
     * Return the value from the 'Authorization' header, or null if there is no
     * 'Authorization' header.
     * 
     * @return string The encoded value from the 'Authorization' header, or
     *   null if no authorisation was given.
     */
    public function authValue() {
        $auth = isset($this->headers["Authorization"]) ?
            $this->headers["Authorization"] :
            null;
        if ($auth === null) return null; // No auth
        
        list($type, $value) = explode(" ", $auth, 2);
        return $value;
    }

    /* Other
    -------------------------------------------------- */

    /**
     * Return the expected response status code for this request, ie. 200 OK.
     * 
     * @return int 200 (OK).
     */
    public function expectedResponseStatusCode() {
        return 200; // OK
    }

    /* Utils
    -------------------------------------------------- */

    private static function parseQueryStr($queryStr) {
        return Util::parseAttrsStr($queryStr, "&", "=", "");
    }
}
