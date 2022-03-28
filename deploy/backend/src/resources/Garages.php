<?php
namespace kv6002\resources;

use dispatcher\Dispatcher;
use router\resource\BasicResource;
use router\exceptions\HTTPError;
use kv6002\standard\builders\JSONBuilder;
use kv6002\standard\builders\NoContentBuilder;
use kv6002\standard\DateTime;

use kv6002\daos;
use kv6002\views;

/**
 * provide a list of garages
 * 
 * @author Callum
 */
class Garages extends BasicResource {
    public function __construct($db) {
        $dao = new daos\Garages($db);

        $this->view = new views\GaragesJSON();

        $actions = [
            "get_all_simple" => Dispatcher::funcToPipeOf([
                function($request) use ($dao){
                    return [
                        $request,
                        $dao->getGarages()
                    ];
                },
                JSONBuilder::typeSelector(
                    function($request, $garages){
                        return $this->view->simpleGarages($garages);
                    }
                )
            ]),

            "get_one_full" => Dispatcher::funcToPipeOf([
                function($request){
                    return [$request, $request->endpointParam("id")];
                },
                function($request, $id) use ($dao){
                    $garage = $dao->getGarage($id);
                    if($garage === null){
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
                                "Must provide $name parameter"
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

                    if ($password === null) {
                        throw new HTTPError(422,
                            "Must provide password parameter"
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
                function (
                        $request,
                        $garageData,
                        $password
                ) use ($dao) {
                    $dao->createGarage(
                        ...$garageData,
                        ...[
                            password_hash($password, PASSWORD_DEFAULT),
                            false
                        ]
                    );
                    return [$request];
                },

                // Return Success
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