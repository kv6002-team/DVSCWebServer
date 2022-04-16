<?php
namespace kv6002\resources;

use database\exceptions\DatabaseError;

use dispatcher\Dispatcher;
use router\resource\BasicResource;
use router\exceptions\HTTPError;
use kv6002\standard\builders\JSONBuilder;
use kv6002\standard\builders\NoContentBuilder;
use kv6002\standard\DateTime;

use kv6002\domain;
use kv6002\daos;
use kv6002\views;

/**
 * provide a list of garages
 * 
 * @author Callum
 */
class Garages extends BasicResource {
    private const USER_TYPE = domain\Garage::USER_TYPE;

    private $dao; // Cat
    private $view;

    public function __construct($db) {
        $this->dao = new daos\Users($db);
        $this->view = new views\GaragesJSON();

        $actions = [
            "get_all_simple" => Dispatcher::funcToPipeOf([
                function ($request) {
                    return [$request, $this->dao->getAll(self::USER_TYPE)];
                },
                JSONBuilder::typeSelector(
                    function ($request, $garages){
                        return $this->view->simpleGarages($garages);
                    }
                )
            ]),

            "get_one_full" => Dispatcher::funcToPipeOf([
                function ($request) {
                    return [$request, $request->endpointParam("id")];
                },
                function ($request, $id) {
                    $garage = $this->dao->get(self::USER_TYPE, $id);
                    if ($garage === null) {
                        throw new HTTPError(404,
                            "Requested Garage does not exist"
                        );
                    }
                    return [$request, $garage];
                },
                JSONBuilder::typeSelector(
                    function($request, $garage){
                        return $this->view->garage($garage);
                    }
                )
            ]),

            "create" => Dispatcher::funcToPipeOf([
                // Extract and Validate Input
                function ($request) {
                    // Utility
                    $requiredPrivateParam = function ($name) use ($request) {
                        $value = $request->privateParam($name);
                        if ($value === null) {
                            throw new HTTPError(422,
                                "Must provide the $name parameter"
                            );
                        }
                        return $value;
                    };

                    // Extract
                    $garageData = [
                        "vts" => $requiredPrivateParam("vts"),
                        "name" => $requiredPrivateParam("name"),
                        "ownerName" => $requiredPrivateParam("ownerName"),
                        "emailAddress" => $requiredPrivateParam("emailAddress"),
                        "telephoneNumber" => $requiredPrivateParam(
                            "telephoneNumber"
                        ),
                        "paidUntil" => $requiredPrivateParam("paidUntil")
                    ];
                    $password = $requiredPrivateParam("password");

                    // Validate
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
                        $garageData["emailAddress"]
                    )) {
                        throw new HTTPError(422,
                            "emailAddress is not a valid email address"
                        );
                    }

                    try {
                        $garageData["paidUntil"] = DateTime::parse(
                            $garageData["paidUntil"]
                        );
                    } catch (Exception $e) {
                        throw new HTTPError(422,
                            "Must provide paidUntil in a correct format (eg."
                            ." YYYY-MM-DD HH:MM:SS)"
                        );
                    }

                    if (str_contains($garageData["vts"], ":")) {
                        throw new HTTPError(422,
                            "VTS number must not contain a colon"
                        );
                    }

                    if ($password === "") {
                        throw new HTTPError(422,
                            "Must provide a non-empty password"
                        );
                    }

                    // Return
                    return [
                        $request,
                        $garageData,
                        $password
                    ];
                },

                // Process Request
                function ($request, $garageData, $password) {
                    try {
                        $garage = $this->dao->add(
                            ...[self::USER_TYPE],
                            ...[
                                password_hash($password, PASSWORD_DEFAULT),
                                false
                            ],
                            ...$garageData
                        );
                    } catch (DatabaseError $e) {
                        throw new HTTPError(409,
                            "A garage with that VTS number is already "
                            ."registered"
                        );
                    }

                    return [$request, $garage];
                },
                JSONBuilder::typeSelector(
                    function ($request, $garage) {
                        $request->setExpectedResponseStatusCode(201);
                        return ["id" => $garage->id()];
                    }
                )
            ]),

            "update" => Dispatcher::funcToPipeOf([
                function ($request) {
                    // Utility
                    $requiredPrivateParam = function ($name) use ($request) {
                        $value = $request->privateParam($name);
                        if ($value === null) {
                            throw new HTTPError(422,
                                "Must provide the $name parameter"
                            );
                        }
                        return $value;
                    };

                    // Extract
                    $id = $request->endpointParam("id");
                    $garageData = [
                        "vts" => $requiredPrivateParam("vts"),
                        "name" => $requiredPrivateParam("name"),
                        "ownerName" => $requiredPrivateParam("ownerName"),
                        "emailAddress" => $requiredPrivateParam("emailAddress"),
                        "telephoneNumber" => $requiredPrivateParam(
                            "telephoneNumber"
                        ),
                        "paidUntil" => $requiredPrivateParam("paidUntil")
                    ];

                    // Validate
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
                        $garageData["emailAddress"]
                    )) {
                        throw new HTTPError(422,
                            "emailAddress is not a valid email address"
                        );
                    }

                    try {
                        $garageData["paidUntil"] = DateTime::parse(
                            $garageData["paidUntil"]
                        );
                    } catch (Exception $e) {
                        throw new HTTPError(422,
                            "Must provide paidUntil in a correct format (eg."
                            ." YYYY-MM-DD HH:MM:SS)"
                        );
                    }

                    if (str_contains($garageData["vts"], ":")) {
                        throw new HTTPError(422,
                            "VTS number must not contain a colon"
                        );
                    }

                    // Return
                    return [
                        $request,
                        $id,
                        $garageData
                    ];
                },
                function ($request, $id, $garageData) {
                    try {
                        $this->dao->update(
                            self::USER_TYPE,
                            $id,
                            $garageData["vts"],
                            $garageData["name"],
                            $garageData["ownerName"],
                            $garageData["emailAddress"],
                            $garageData["telephoneNumber"],
                            $garageData["paidUntil"]
                        );
                    } catch (DatabaseError $e) {
                        var_dump($e);
                        throw new HTTPError(404,
                            "No garage with that ID exists."
                        );
                    }

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
                            "No garage with that ID exists."
                        );
                    }

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

        // Compose (Always add CORS headers)
        $headers = ["Access-Control-Allow-Origin" => "*"];
        parent::__construct([
            "GET" => $getAction(
                function ($request) {
                    if ($request->endpointParam("id") !== null) {
                        return "get_one_full";
                    }
                    return "get_all_simple";
                }
            ),

            "POST" => $getAction("create"),
            "PATCH" => $getAction("update"),
            "DELETE" => $getAction("remove"),
            
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

    //TODO: add metadata
}