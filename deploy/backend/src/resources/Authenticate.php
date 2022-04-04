<?php
namespace kv6002\resources;

use dispatcher\Dispatcher;
use router\resource\BasicResource;
use router\exceptions\HTTPError;
use kv6002\standard\builders\JSONBuilder;

use router\Request;
use router\resource\WithMetadata;
use router\resource\MetadataUtils;

use kv6002\daos;

/**
 * 
 * 
 * @author William Taylor (19009576)
 */
class Authenticate extends BasicResource implements WithMetadata {
    private static $INVALID_AUTH_ERR_STR = "Username or password incorrect";

    private $authenticator;

    public function __construct($db, $authenticator) {
        $this->authenticator = $authenticator;

        // Define action
        $contentBuilder = Dispatcher::funcToPipeOf([
            function ($request) {
                return [
                    $request,
                    explode(",", $request->privateParam("types")),
                    $request->privateParam("username"),
                    $request->privateParam("password")
                ];
            },
            function ($request, $types, $username, $password) use ($db) {
                $dao = new daos\Users($db);
                
                // Try each user type in turn
                foreach ($types as $type) {
                    $user = $dao->getUserByUsername($type, $username);
                    if ($user !== null) break; // If one is found, use it.
                }

                // If the user does not exist as any of the given types, or
                // auth fails, then return an error.
                if (
                        $user === null ||
                        !password_verify($password, $user->password())
                ) {
                    throw new HTTPError(401, self::$INVALID_AUTH_ERR_STR);
                }

                // Construct a JWT from the user.
                $jwt = [
                    "token" => $this->authenticator->standardAuthToken($user)
                ];
                return [$request, $jwt];
            },
            JSONBuilder::typeSelector(
                function ($request, $jwt) {
                    return $jwt;
                }
            )
        ]);

        // Compose (Always add CORS headers)
        $headers = ["Access-Control-Allow-Origin" => "*"];
        parent::__construct([
            "POST" => Dispatcher::funcToPipeOf([
                $contentBuilder,
                BasicResource::addHeaders($headers)
            ])
        ]);
    }

    /* Implement Resource (Override BasicResource)
    -------------------------------------------------- */

    /**
     * This Resource defaults to returning application/json.
     * 
     * @return string "application/json"
     */
    public function getDefaultContentType() {
        return "application/json";
    }

    /* Implement WithMetadata
    -------------------------------------------------- */

    // For documentation of what these mean and how they are used, see
    // \router\resource\WithMetadata.

    public function getName() {
        return "Authentication";
    }

    public function getDescription() {
        return (
            "Returns login JSON Web Tokens (JWTs) to clients who need to"
            ." authenticate."
        );
    }

    // getSupportedMethods() is implemented by BasicResource

    public function isAuthenticated($method) {
        MetadataUtils::checkSupportsMethod($this, $method);
        return false;
    }

    public function getSupportedParamsFor($method) {
        MetadataUtils::checkSupportsMethod($this, $method);
        MetadataUtils::checkParamsAllowedFor($method);
        return [];
    }

    /**
     * Require sending username and password in the body (not the URL) for
     * security.
     */
    public function getBodySpecFor($method) {
        MetadataUtils::checkSupportsMethod($this, $method);
        MetadataUtils::checkBodyAllowedFor($method);

        $bodySchema =  [
            "username" => (
                "(string) The username (email address) to check to get a JWT."
            ),
            "password" => "(string) The hashed password to check to get a JWT."
        ];

        return (
            "The body must be be form data-encoded and conform to the following"
            ." schema:"
            ."\n".json_encode($bodySchema, JSON_PRETTY_PRINT)
        );
    }

    public function getResponseDescriptionFor($method) {
        MetadataUtils::checkSupportsMethod($this, $method);

        $schema = [
            "token" => "(string) A base64-encoded JWT."
        ];

        return (
            "200 (OK) - JSON response containing a JWT. The response follows"
            ." the following schema:"
            ."\n".json_encode($schema, JSON_PRETTY_PRINT)
            ."\n"
            ."\n405, 406, 500 - See the Default Global Error Handler."
        );
    }

    public function getExampleRequestsFor($method) {
        MetadataUtils::checkSupportsMethod($this, $method);

        return [
            new Request("POST", null, null, [
                "username" => "john@example.com",
                "password" => "johnpassword"
            ])
        ];
    }
}
