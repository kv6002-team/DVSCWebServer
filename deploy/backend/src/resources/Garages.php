<?php
namespace kv6002\resources;

use util\Util;

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
use kv6002\validators;

/**
 * provide a list of garages
 * 
 * @author Callum
 */
class Garages extends BasicResource {
    private const USER_TYPE = domain\Garage::USER_TYPE;

    private $garageValidator;
    private $instrumentValidator;

    private $usersDAO; // Cat
    private $instrumentsDAO;
    private $loggerDAO;
    
    private $view;

    public function __construct($db, $authenticator) {
        $this->garageValidator = new validators\Garage();
        $this->instrumentValidator = new validators\Instrument();

        $this->usersDAO = new daos\Users($db);
        $this->instrumentsDAO = new daos\Instruments($db);
        $this->loggerDAO = new daos\EventLog($db);

        $this->view = new views\GaragesJSON();

        $actions = [
            "get_all_simple" => Dispatcher::funcToPipeOf([
                function ($request) {
                    return [$request, $this->usersDAO->getAll(self::USER_TYPE)];
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
                    $garage = $this->usersDAO->get(self::USER_TYPE, $id);
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

            "add" => Dispatcher::funcToPipeOf([
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

                    // Validate
                    $garageData = $this->garageValidator->validate(
                        ...$garageData
                    );

                    try {
                        $this->loggerDAO->add(
                            daos\EventLog::DATA_CREATED_EVENT,
                            daos\EventLog::INFO_LEVEL,
                            "Garage added: '" . $garageData['vts'] . "'",
                            new DateTimeImmutable("now")
                        );
                    } catch (DatabaseError $e) { /*Do nothing*/ }

                    // Return
                    return [$request, $garageData];
                },

                // Process Request
                function ($request, $garageData) {
                    try {
                        $defaultPass = (new DateTime())->format("dmy");
                        $garage = $this->usersDAO->add(
                            ...[self::USER_TYPE],
                            ...[
                                password_hash($defaultPass, PASSWORD_DEFAULT),
                                true
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
                    if ($id === null) {
                        throw new HTTPError(422,
                            "Cannot update the whole collection of garages"
                            ." (did you mean to `PATCH /api/garages/:id`?)"
                        );
                    }

                    $garageData =  $this->garageValidator->validate(
                        ...$garageData
                    );

                    try {
                        $this->loggerDAO->add(
                            daos\EventLog::DATA_UPDATED_EVENT,
                            daos\EventLog::INFO_LEVEL,
                            "Garage modified: '" . $garageData['vts'] ."'",
                            new DateTimeImmutable("now")
                        );
                    } catch (DatabaseError $e) { /*Do nothing*/ }

                    // Return
                    return [
                        $request,
                        $id,
                        $garageData
                    ];
                },
                function ($request, $id, $garageData) {
                    try {
                        $this->usersDAO->update(
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
                        throw new HTTPError(404,
                            "No garage with that ID exists."
                        );
                    }
                    return [$request];
                },
                new NoContentBuilder()
            ]),

            "updateJSON" => Dispatcher::funcToPipeOf([ 
                function ($request) {
                    // Utility
                    $requiredAttr = function ($obj, $name) {
                        if(!isset($obj[$name])) {
                            throw new HTTPError(422,
                                "Must provide $name attribute of Garage"
                            );
                        }
                        return $obj[$name];
                    };
                    
                    // Parse JSON
                    try {
                        $body = Util::toJSON($request->body());
                    } catch (JsonException $e) {
                        throw new HTTPError(422,
                            "Requested Garage JSON is invalid"
                        );
                    }

                    // Extract Garage
                    $garageID = $request->endpointParam("id");
                    $garageData = [
                        "vts" => $requiredAttr($body, "vts"),
                        "name" => $requiredAttr($body, "name"),
                        "ownerName" => $requiredAttr($body, "ownerName"),
                        "emailAddress" => $requiredAttr($body, "emailAddress"),
                        "telephoneNumber" => $requiredAttr($body, "telephoneNumber"),
                        "paidUntil" => $requiredAttr($body, "paidUntil")
                    ];
                    
                    // Validate Garage
                    if ($garageID === null) {
                        throw new HTTPError(422,
                            "Cannot update the whole collection of garages"
                            ." (did you mean to `PATCH /api/garages/:id`?)"
                        );
                    }

                    if ($this->usersDAO->get(self::USER_TYPE, $garageID) === null) {
                        throw new HTTPError(404,
                            "Requested Garage not found"
                        );
                    }

                    $garageData = $this->garageValidator->validate(...$garageData);

                    // Extract Instruments
                    $instrumentsDataRaw = $requiredAttr($body, "instruments");
                    if (!is_array($instrumentsDataRaw)) {
                        throw new HTTPError(422,
                            "InstrumentData must be a JSONArray"
                        );
                    }

                    $instrumentsData = [];
                    foreach ($instrumentsDataRaw as $instrumentDataRaw) {
                        $instrumentID = $requiredAttr($instrumentDataRaw, "id");
                        $instrumentData = [
                            "name" => $requiredAttr($instrumentDataRaw, "name"),
                            "officialCheckExpiryDate" => $requiredAttr($instrumentDataRaw, "officialCheckExpiryDate"),
                            "ourCheckStatus" => $requiredAttr($instrumentDataRaw, "ourCheckStatus"),
                            "ourCheckDate" => $requiredAttr($instrumentDataRaw, "ourCheckDate")
                        ];

                        // Validate Instruments
                        if ($instrumentID === null) {
                            throw new HTTPError(422,
                                "Instrument ID invalid"
                            );
                        }
                        
                        if ($this->instrumentsDAO->get($instrumentID) === null) {
                            throw new HTTPError(404,
                                "Requested Instrument not found"
                            );
                        }

                        array_push(
                            $instrumentsData,
                            array_merge(
                                ["id" => $instrumentID],
                                $this->instrumentValidator->validate(
                                    ...$instrumentData
                                )
                            )                            
                        );
                    }
                    return [$request, $garageID, $garageData, $instrumentsData];
                },
                function ($request, $garageID, $garageData, $instrumentsData) {
                    try {
        
                        foreach ($instrumentsData as $instrument) {
                            $this->instrumentsDAO->updateRaw(
                                $instrument["id"],
                                $instrument["name"],
                                $instrument["officialCheckExpiryDate"],
                                $instrument["ourCheckStatus"],
                                $instrument["ourCheckDate"]
                            );
                        }
                    } catch (DatabaseError $e) {
                        throw new HTTPError(404,
                            "Instrument with id '".$instrument["id"]."' does not exist."
                        );
                    }

                    try {
                        $this->usersDAO->update(
                            self::USER_TYPE,
                            $garageID,
                            $garageData["vts"],
                            $garageData["name"],
                            $garageData["ownerName"],
                            $garageData["emailAddress"],
                            $garageData["telephoneNumber"],
                            $garageData["paidUntil"]
                        );
                    } catch (DatabaseError $e) {
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
                        $this->usersDAO->remove(self::USER_TYPE, $id);
                    } catch (DatabaseError $e) {
                        throw new HTTPError(404,
                            "No garage with that ID exists."
                        );
                    }

                    try {
                        $this->loggerDAO->add(
                            daos\EventLog::DATA_DELETED_EVENT,
                            daos\EventLog::INFO_LEVEL,
                            "Garage removed: '$id'",
                            new DateTimeImmutable("now")
                        );
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

            "POST" => $getAction("add"),
            "PATCH" => $getAction(
                function ($request) {
                    if ($request->contentType() === "application/json") {
                        return "updateJSON";
                    }
                    return "update";
                }
            ),
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