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
use kv6002\validators;

/**
 * provide a list of instruments
 * 
 * @author Callum
 */
class Instruments extends BasicResource {
    private $dao;
    private $validator;
    private $loggerDAO;

    public function __construct($db) {
        $this->dao = new daos\Instruments($db);
        $this->validator = new validators\Instrument();
        $this->loggerDAO = new daos\EventLog();

        $actions = [
            "add" => Dispatcher::funcToPipeOf([
                // Extract and validate input
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
                    $instrumentData = [
                        "garageID" => $requiredPrivateParam("garageID"),
                        "name" => $requiredPrivateParam("name"),
                        "serialNumber" => $requiredPrivateParam("serialNumber"),
                        "officialCheckExpiryDate" => $requiredPrivateParam("officialCheckExpiryDate"),
                        "ourCheckStatus" => $requiredPrivateParam("ourCheckStatus"),
                        "ourCheckDate" => $requiredPrivateParam("ourCheckDate")    
                    ];

                    // Validate
                    $instrumentData = $this->validator->validate(
                        ...$instrumentData
                    );

                    try {
                        $this->loggerDAO->add(
                            daos\EventLog::DATA_CREATED_EVENT,
                            daos\EventLog::INFO_LEVEL,
                            "Instrument added: " . $instrumentData['serialNumber'] ."'",
                            new DateTimeImmutable("now")
                        );
                    } catch (DatabaseError $e) { /*Do nothing*/ }

                    // Return
                    return [
                        $request,
                        $instrumentData
                    ];
                },

                // Process Request
                function ($request, $instrumentData) {
                    try {
                        $this->loggerDAO->add(
                            daos\EventLog::DATA_CREATED_EVENT,
                            daos\EventLog::INFO_LEVEL,
                            "New garage created" . $garageData['vts'],
                            new DateTimeImmutable("now")
                        );
                    } catch (DatabaseError $e) { /*Do nothing*/ }

                    try {
                        $instrument = $this->dao->add(
                            ...$instrumentData
                        );
                    } catch (DatabaseError $e) {
                        throw new HTTPError(409,
                            "An instrument with that serial number already"
                            ." exists"
                        );
                    }

                    return [$request, $instrument];
                },
                JSONBuilder::typeSelector(
                    function ($request, $instrument) {
                        $request->setExpectedResponseStatusCode(201);
                        return ["id" => $instrument->id()];
                    }
                )
            ]),

            "update" => Dispatcher::funcToPipeOf([
                // Extract and validate input
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
                    $instrumentData = [
                        "name" => $requiredPrivateParam("name"),
                        "serialNumber" => $requiredPrivateParam("serialNumber"),
                        "officialCheckExpiryDate" => $requiredPrivateParam("officialCheckExpiryDate"),
                        "ourCheckStatus" => $requiredPrivateParam("ourCheckStatus"),
                        "ourCheckDate" => $requiredPrivateParam("ourCheckDate")
                    ];

                    // Validate
                    if ($id === null) {
                        throw new HTTPError(422,
                            "Cannot update the whole collection of instruments"
                            ." (did you mean to `PATCH /api/instruments/:id`?)"
                        );
                    }

                    $instrumentData = $this->validator->validate(
                        ...$instrumentData
                    );

                    try {
                        $this->loggerDAO->add(
                            daos\EventLog::DATA_UPDATED_EVENT,
                            daos\EventLog::INFO_LEVEL,
                            "Instrument updated: " . $instrumentData['serialNumber'] . "'",
                            new DateTimeImmutable("now")
                        );
                    } catch (DatabaseError $e) { /*Do nothing*/ }

                    // Return
                    return [
                        $request,
                        $id,
                        $instrumentData
                    ];
                },

                // Process Request
                function ($request, $id, $instrumentData) {
                    try {
                        $this->dao->update(
                            ...[$id],
                            ...$instrumentData
                        );
                    } catch (DatabaseError $e) {
                        throw new HTTPError(409,
                            "An instrument with that serial number already"
                            ." exists"
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
                        $this->dao->remove($id);
                    } catch (DatabaseError $e) {
                        throw new HTTPError(404, 
                            "No instrument with that ID exists."
                        );  
                    }

                    try {
                        $this->loggerDAO->add(
                            daos\EventLog::DATA_UPDATED_EVENT,
                            daos\EventLog::INFO_LEVEL,
                            "Instrument removed: '$id'",
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
            "POST" => $getAction("add"),
            "PATCH" => $getAction("update"),
            "DELETE" => $getAction("remove"),

            "OPTIONS" => $getAction("cors_preflight")
        ]);
    }
}