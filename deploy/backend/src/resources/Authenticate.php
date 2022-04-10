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
 * Authenticate a user with username and password credentials.
 * 
 * @author William Taylor (19009576)
 */
class Authenticate extends BasicResource implements WithMetadata {
    private static $TYPES_NOT_GIVEN_ERR_STR = "Account type not given";
    private static $TYPES_INVALID_ERR_STR = "Account type invalid";
    private static $CREDS_NOT_GIVEN_ERR_STR = "Username or password not given";
    private static $AUTH_INVALID_ERR_STR = "Username or password incorrect";

    private $authenticator;

    public function __construct($db, $authenticator) {
        $this->authenticator = $authenticator;

        $dao = new daos\Users($db);

        // Define action
        $contentBuilder = Dispatcher::funcToPipeOf([
            // Try to get the credentials
            function ($request) use ($dao) {
                $credentialsStrEncoded = $request->authValue();
                if ($credentialsStrEncoded === null) {
                    throw new HTTPError(401, self::$CREDS_NOT_GIVEN_ERR_STR);
                }

                $credentialsStr = base64_decode($credentialsStrEncoded, true);
                if ($credentialsStr === false) {
                    throw new HTTPError(401, self::$CREDS_NOT_GIVEN_ERR_STR);
                }

                list($username, $password) = array_merge(
                    explode(":", $credentialsStr, 2),
                    [null] // Make sure that password contains a value
                );
                if (
                    $username === "" ||
                    $password === "" ||
                    $password === null
                ) {
                    throw new HTTPError(401, self::$CREDS_NOT_GIVEN_ERR_STR);
                }

                // Try to get the types
                $typesStr = $request->privateParam("types");
                if ($typesStr === null || $typesStr === "") {
                    throw new HTTPError(401, self::$TYPES_NOT_GIVEN_ERR_STR);
                }

                $types = explode(",", $typesStr);
                foreach ($types as $type) {
                    $supportedUserTypes = $dao->getSupportedUserTypes();
                    if (!in_array($type, $supportedUserTypes, true)) {
                        throw new HTTPError(401, self::$TYPES_INVALID_ERR_STR);
                    }
                }

                // Return everything
                return [
                    $request,
                    $types,
                    $username,
                    $password
                ];
            },
            function ($request, $types, $username, $password) use ($dao) {
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
                    throw new HTTPError(401, self::$AUTH_INVALID_ERR_STR);
                }

                // Construct a JWT from the user.
                $jwt = [
                    "token_type" => "bearer",
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
