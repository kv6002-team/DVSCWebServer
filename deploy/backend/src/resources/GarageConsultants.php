<?php
namespace kv6002\resources;

use database\exceptions\DatabaseError;

use dispatcher\Dispatcher;
use router\resource\BasicResource;
use router\exceptions\HTTPError;
use kv6002\standard\builders\JSONBuilder;
use kv6002\standard\builders\NoContentBuilder;

use router\Request;
use router\resource\WithMetadata;
use router\resource\MetadataUtils;

use kv6002\domain;
use kv6002\daos;
use kv6002\views;

/**
 * Provide a list of garage consultants.
 * 
 * @author William Taylor (19009576)
 */
class GarageConsultants extends BasicResource implements WithMetadata {
    private const USER_TYPE = domain\GarageConsultant::USER_TYPE;

    private $dao;
    private $view;
    private $loggerDAO;

    public function __construct($db, $authenticator) {
        $this->dao = new daos\Users($db);
        $this->view = new views\GarageConsultantsJSON();
        $this->loggerDAO = new daos\EventLog($db);

        // Which actions can we take?
        $actions = [
            "get_all" => Dispatcher::funcToPipeOf([
                function ($request) {
                    return [$request, $this->dao->getAll(self::USER_TYPE)];
                },
                JSONBuilder::typeSelector(
                    function ($request, $consultants) {
                        return $this->view->garageConsultants($consultants);
                    }
                )
            ]),

            "get_one" => Dispatcher::funcToPipeOf([
                function ($request) {
                    return [$request, $request->endpointParam("id")];
                },
                function ($request, $id) {
                    $consultant = $this->dao->get(self::USER_TYPE, $id);
                    if ($consultant === null) {
                        throw new HTTPError(404,
                            "Requested garage consultant does not exist"
                        );
                    }
                    return [$request, $consultant];
                },
                JSONBuilder::typeSelector(
                    function ($request, $consultant) {
                        return $this->view->garageConsultant($consultant);
                    }
                )
            ]),

            "add" => Dispatcher::funcToPipeOf([
                function ($request) {
                    $emailAddress = $request->privateParam("emailAddress");
                    if ($emailAddress === null || $emailAddress === "") {
                        throw new HTTPError(422,
                            "Must provide a non-empty emailAddress parameter"
                        );
                    }
                    // Must not contain a colon
                    if (!preg_match(
                        "/^"                   // From start of string
                        ."(?=.{1,128}@)"       // Before @ must be 1-128 chars
                        ."[A-Za-z0-9_-]+"      // First '.'-delimited segment
                        ."(\.[A-Za-z0-9_-]+)*" // Other '.'-delimited segments
                        ."@"                   // @ symbol
                        ."(?=.{1,128})"        // After @ must be 1-128 chars
                        ."[A-Za-z0-9]"         // First char of domain name
                        ."[A-Za-z0-9-]*"       // Bottom level domain name
                        ."(\.[A-Za-z0-9-]+)*"  // Intermediate domain names
                        ."(\.[A-Za-z]{2,})"    // Top level domain name (TLD)
                        ."$/"                  // To end of string
                        ."u",                  // FLAGS: Use Unicode matching
                        $emailAddress
                    )) {
                        throw new HTTPError(422,
                            "emailAddress is not a valid email address"
                        );
                    }

                    return [$request, $emailAddress];
                },
                function ($request, $emailAddress) {
                    try {
                        $defaultPass = (new DateTime())->format("dmy");
                        $garageConsultant = $this->dao->add(
                            self::USER_TYPE,
                            password_hash($defaultPass, PASSWORD_DEFAULT),
                            true,
                            $emailAddress
                        );
                    } catch (DatabaseError $e) {
                        throw new HTTPError(409,
                            "A garage consultant with that email address is "+
                            "already registered"
                        );
                    }

                    try {
                        $this->loggerDAO->add(
                            daos\EventLog::DATA_CREATED_EVENT,
                            daos\EventLog::INFO_LEVEL,
                            "Garage Consultant added: '$emailAddress'",
                            new \DateTimeImmutable("now")
                        );
                    } catch (DatabaseError $e) { /*Do nothing*/ }

                    return [$request, $garageConsultant];
                },
                JSONBuilder::typeSelector(
                    function ($request, $garageConsultant) {
                        $request->setExpectedResponseStatusCode(201);
                        return ["id" => $garageConsultant->id()];
                    }
                )
            ]),

            "update" => Dispatcher::funcToPipeOf([
                function ($request) {
                    $id = $request->endpointParam("id");
                    if ($id === null) {
                        throw new HTTPError(422,
                            "Must provide an id parameter"
                        );
                    }

                    $username = $request->privateParam("emailAddress");
                    if ($username === null || $username === "") {
                        throw new HTTPError(422,
                            "Must provide a non-empty emailAddress parameter"
                        );
                    }
                    if (str_contains($username, ":")) {
                        throw new HTTPError(422,
                            "emailAddress must not contain a colon"
                        );
                    }

                    return [$request, $id, $username];
                },
                function ($request, $id, $emailAddress) {
                    try {
                        $this->dao->update(self::USER_TYPE, $id, $emailAddress);
                    } catch (DatabaseError $e) {
                        throw new HTTPError(404,
                            "No garage consultant with that ID exists."
                        );
                    }

                    try {
                        $this->loggerDAO->add(
                            daos\EventLog::DATA_UPDATED_EVENT,
                            daos\EventLog::INFO_LEVEL,
                            "Garage Consultant modified: '$emailAddress'",
                            new \DateTimeImmutable("now")
                        );
                    } catch (DatabaseError $e) { /*Do nothing*/ }

                    return [$request];
                },
                new NoContentBuilder()
            ]),

            "remove" => Dispatcher::funcToPipeOf([
                function ($request) {
                    $id = $request->endpointParam("id");
                    if ($id === null) {
                        throw new HTTPError(422,
                            "Must provide an id parameter"
                        );
                    }
                    return [$request, $id];
                },
                function ($request, $id) {
                    try {
                        $this->dao->remove(self::USER_TYPE, $id);
                    } catch (DatabaseError $e) {
                        throw new HTTPError(404,
                            "No garage consultant with that ID exists."
                        );
                    }

                    try {
                        $user = $this->dao->get(self::USER_TYPE, $id);
                        if ($user !== null) {
                            $this->loggerDAO->add(
                                daos\EventLog::DATA_DELETED_EVENT,
                                daos\EventLog::INFO_LEVEL,
                                "Garage Consultant removed: '" . $user->username() . "'",
                                new \DateTimeImmutable("now")
                            );
                        }
                    } catch (DatabaseError $e) { /*Do nothing*/ }

                    return [$request];
                },
                new NoContentBuilder()
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
        $getAction = function ($actionKey) use ($actions, $authenticator) {
            return Dispatcher::funcToPipeOf([
                $authenticator->auth(),
                $authenticator->requireAuthentication(),
                $authenticator->requireAuthorisation("general"),
                $authenticator->requireAuthorisation(
                    domain\GarageConsultant::USER_TYPE
                ),
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

        // Compose (Always add CORS headers)
        $headers = ["Access-Control-Allow-Origin" => "*"];
        parent::__construct([
            "GET" => $getAction(
                function ($request) {
                    if ($request->endpointParam("id") !== null) {
                        return "get_one";
                    }
                    return "get_all";
                }
            ),

            "POST" => $getAction("add"),
            "DELETE" => $getAction("remove"),
            "PATCH" => $getAction("update"),

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
        return "Garage Consultants";
    }

    public function getDescription() {
        return (
            "Gets a list of all garage consultants, or one consultant by"
            ." username."
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

        switch ($method) {
            case "GET":
                return [
                    "id" => "(string) the ID of the garage consultant to get."
                ];

            default:
                return [];
        }
    }

    /**
     * Require sending username and password in the body (not the URL) for
     * security.
     */
    public function getBodySpecFor($method) {
        MetadataUtils::checkSupportsMethod($this, $method);
        MetadataUtils::checkBodyAllowedFor($method);

        switch ($method) {
            case "POST":
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

            default:
                return [];
        }
    }

    public function getResponseDescriptionFor($method) {
        MetadataUtils::checkSupportsMethod($this, $method);

        switch ($method) {
            case "GET":
                $schema = [
                    //
                ];

                return (
                    "200 (OK) - JSON response containing ???."
                    ." The response follows the following schema:"
                    ."\n".json_encode($schema, JSON_PRETTY_PRINT)
                    ."\n"
                    ."\n405, 406, 500 - See the Default Global Error Handler."
                );

            default:
                return [];
        }
    }

    public function getExampleRequestsFor($method) {
        MetadataUtils::checkSupportsMethod($this, $method);

        switch ($method) {
            case "POST":
                return [
                    new Request("POST", null, null, [
                        "emailAddress" => "john@example.com",
                        "password" => "johnpassword"
                    ])
                ];

            default:
                return [];
        }
    }
}
