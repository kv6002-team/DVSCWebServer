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
 * provide a list of instruments
 * 
 * @author Callum
 */
class Instruments extends BasicResource {
    
    private $dao;

    public function __construct($db) {
        $this->dao = new daos\Instruments($db);

        $actions = [
            "create" => Dispatcher::funcToPipeOf([
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
                    if (!preg_match(
                        "([A-Za-z0-9]+)",
                        $instrumentData['serialNumber']
                    )) {
                        throw new HTTPError(422,
                            "serialNumber is not a valid serial number"
                        );
                    }
                    
                    try {
                        $instrumentData["officialCheckExpiryDate"] = DateTime::parse(
                            $instrumentData["officialCheckExpiryDate"]
                        );
                    } catch (Exception $e) {
                        throw new HTTPError(422,
                            "Must provide officialCheckExpiryDate in a correct format"
                            ."(eg. YYYY-MM-DD HH:MM:SS)"
                        );
                    }

                    if($instrumentData["officialCheckExpiryDate"] < new DateTime('today midnight')) {
                        throw new HTTPError(422,
                            "Must provide a date from tomorrow for officialCheckExpiryDate"
                        );
                    }

                    try {
                        $instrumentData["ourCheckDate"] = DateTime::parse(
                            $instrumentData["ourCheckDate"]
                        );
                    } catch (Exception $e) {
                        throw new HTTPError(422,
                            "Must provide ourCheckDate in a correct format"
                            ."(eg. YYYY-MM-DD HH:MM:SS)"
                        );
                    }

                    if($instrumentData["ourCheckDate"] < new DateTime('yesterday midnight')) {
                        throw new HTTPError(422,
                            "Must provide a date from today for ourCheckDate"
                        );
                    }

                    // Return
                    return [
                        $request,
                        $instrumentData
                    ];
                },

                // Process Request
                function ($request, $instrumentData) {
                    try {
                        $instrument = $this->dao->add(
                            ...$instrumentData
                        );
                    } catch (DatabaseError $e) {
                        var_dump($e);
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

            "remove" => Dispatcher::funcToPipeOf([
                function ($request) {
                    $id = $request->endpointParam("id");
                    if($id === null) {
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

            "POST" => $getAction("create"),
            "DELETE" => $getAction("remove"),
            
            "OPTIONS" => $getAction("cors_preflight")
        ]);
    }
}