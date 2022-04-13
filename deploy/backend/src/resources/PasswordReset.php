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
 * Allow authenticated and unauthenticated users to change/reset their password.
 * 
 * @author William Taylor (19009576)
 */
class PasswordReset extends BasicResource {
    private static $TYPES_NOT_GIVEN_ERR_STR = "Account types not given";
    private static $TYPES_INVALID_ERR_STR = "Account types invalid";
    private static $USERNAME_NOT_GIVEN_ERR_STR = "Username not given";

    private $authenticator;

    public function __construct($db, $authenticator) {
        $this->authenticator = $authenticator;
        
        $dao = new daos\Users($db);

        // Which actions can we take?
        $actions = [
            "send_verification" => Dispatcher::funcToPipeOf([
                function ($request) use ($dao) {
                    // Try to get the username a change is being requested for
                    $username = $request->privateParam("username");
                    if ($username === "") {
                        throw new HTTPError(422,
                            self::$USERNAME_NOT_GIVEN_ERR_STR
                        );
                    }

                    // Try to get the types
                    $typesStr = $request->privateParam("types");
                    if ($typesStr === null || $typesStr === "") {
                        throw new HTTPError(422,
                            self::$TYPES_NOT_GIVEN_ERR_STR
                        );
                    }

                    $types = explode(",", $typesStr);
                    foreach ($types as $type) {
                        $supportedUserTypes = $dao->getSupportedUserTypes();
                        if (!in_array($type, $supportedUserTypes, true)) {
                            throw new HTTPError(401,
                                self::$TYPES_INVALID_ERR_STR
                            );
                        }
                    }

                    // Return everything
                    return [$request, $types, $username];
                },
                function ($request, $types, $username) use ($dao) {
                    // Try each user type in turn.
                    foreach ($types as $type) {
                        $user = $dao->getUserByUsername($type, $username);
                        if ($user !== null) break; // If one is found, use it.
                    }

                    // If the user does not exist as any of the given types,
                    // then return an error.
                    if ($user === null) {
                        throw new HTTPError(422,
                            "No user exists with the given username of any of "
                            ."the requested types"
                        );
                    }

                    // FIXME: Just give the token back directly for now.
                    // Eventually, send it in a link in an email to the email
                    // registered for that user.

                    // Construct a JWT for that user for specialised use
                    // (account verification only).
                    $jwt = [
                        "token_type" => "bearer",
                        "token" => $this->authenticator->standardAuthToken(
                            $user, ["account_verification"]
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

            "change_password" => Dispatcher::funcToPipeOf([
                $authenticator->requireAuthorisation([
                    "general", "account_verification"
                ]),
                function ($request, $user) {
                    $newPassword = $request->privateParam("newPassword");
                    if ($newPassword === null) {
                        throw new HTTPError(422, "New password not given");
                    }
                    return [$request, $user, $newPassword];
                },
                function ($request, $user, $newPassword) use ($dao) {
                    $dao->changePassword(
                        $user,
                        password_hash($newPassword, PASSWORD_DEFAULT)
                    );
                    return [$request];
                },
                new NoContentBuilder()
            ])
        ];

        // Compose (Always add CORS headers)
        parent::__construct([
            "POST" => Dispatcher::funcToPipeOf([
                $authenticator->auth(),
                Dispatcher::funcToKeyOf(
                    $actions,
                    function ($request, $user, $authorisations) {
                        if (
                            // Token not sent, or not authenticated
                            $user === null ||

                            // Token not sent
                            $authorisations === null ||

                            // Not authorised
                            !(
                            in_array("account_verification", $authorisations) ||
                            in_array("general", $authorisations)
                            )
                        ) {
                            // Not authenticated/authorised, only requesting it
                            return "send_verification";
                        }
                        return "change_password";
                    }
                ),
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
}
