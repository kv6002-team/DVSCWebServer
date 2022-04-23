<?php
namespace kv6002\resources;

use dispatcher\Dispatcher;
use router\resource\BasicResource;
use router\exceptions\HTTPError;
use kv6002\standard\builders\JSONBuilder;
use kv6002\standard\builders\NoContentBuilder;

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
    private static $TYPES_NOT_GIVEN_ERR_STR = "Account types not given";
    private static $TYPES_INVALID_ERR_STR = "Account types invalid";
    private static $CREDS_NOT_GIVEN_ERR_STR = "Username or password not given";
    private static $AUTH_INVALID_ERR_STR = "Username or password incorrect";

    private $authenticator;
    private $dao;
    private $loggerDAO;

    public function __construct($db, $authenticator) {
        $this->authenticator = $authenticator;
        $this->dao = new daos\Users($db);
        $this->loggerDAO = new daos\EventLog($db);

        // Define actions
        $actions = [
            "auth" => Dispatcher::funcToPipeOf([
                function ($request) {
                    // Try to get the credentials
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
                        $supportedUserTypes = $this->dao->getSupportedUserTypes();
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
                function ($request, $types, $username, $password) {
                    // Try each user type in turn.
                    foreach ($types as $type) {
                        $user = $this->dao->getByUsername($type, $username);
                        if ($user !== null) break; // If one is found, use it.
                    }

                    // If the user does not exist as any of the given types, or
                    // auth fails, then return an error.
                    if (
                            $user === null ||
                            !password_verify($password, $user->password())
                    ) {
                        try {
                            $loggerDAO->add(
                                daos\EventLog::LOGIN_EVENT,
                                daos\EventLog::WARN_LEVEL,
                                "Failed login attempt for user '$username'",
                                new DateTimeImmutable("now")
                            );
                        } catch (DatabaseError $e) { /*Do nothing*/ }

                        throw new HTTPError(401, self::$AUTH_INVALID_ERR_STR);
                    }

                    try {
                        $loggerDAO->add(
                            daos\EventLog::LOGIN_EVENT,
                            daos\EventLog::INFO_LEVEL,
                            "Successful login of '$username'",
                            new DateTimeImmutable("now")
                        );
                    } catch (DatabaseError $e) { /*Do nothing*/ }

                    // Construct a JWT for that user. For general use if they don't
                    // require a password reset, for password reset only if they do.
                    $jwt = [
                        "token_type" => "bearer",
                        "token" => $this->authenticator->standardAuthToken(
                            $user,
                            $user->passwordResetRequired() ?
                                ["password_reset__password_auth"] :
                                ["general", $user->type()]
                        )
                    ];
                    return [$request, $jwt];
                },
                JSONBuilder::typeSelector(
                    function ($request, $jwt) {
                        return $jwt;
                    }
                )
            ]),

            "cors_preflight" => Dispatcher::funcToPipeOf([
                function ($request) {
                    $origin = $request->header("Origin");
                    $corsMethod =
                        $request->header("Access-Control-Request-Method");

                    if ($origin === null || $corsMethod === null) {
                        throw new HTTPError(405,
                            "OPTIONS is only supported for CORS preflight"
                            ." requests"
                        );
                    }

                    return [$request];
                },
                new NoContentBuilder()
            ])
        ];

        /**
         * Get the given action, as put through a common pipeline.
         * 
         * 'Bottleneck' all actions to into a middle pipeline that adds headers,
         * ie.
         *   method ---\                              /--- action
         *              \                            /
         *     method ---+-- getAction() pipeline --+--- action
         *              /                            \
         *   method ---/                              \--- action
         */
        $getAction = function ($actionKey) use ($actions) {
            return Dispatcher::funcToPipeOf([
                Dispatcher::funcToKeyOf($actions, $actionKey),
                function ($response) {
                    $headers = [
                        "Access-Control-Allow-Origin" => "*",
                        "Access-Control-Allow-Methods" =>
                            // Put this in the invocation pipeline to calculate
                            // the headers only after the parent class has been
                            // initialised.
                            implode(", ", $this->getSupportedMethods()),
                        "Access-Control-Allow-Headers" => "Authorization"
                    ];

                    // PHP 5.6 doesn't support `C::func()()` (double-call)
                    // syntax.
                    $addHeadersFn = BasicResource::addHeaders($headers);
                    return $addHeadersFn($response);
                }
            ]);
        };

        // Compose
        parent::__construct([
            "POST" => $getAction("auth"),
            "OPTIONS" => $getAction("cors_preflight")
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
