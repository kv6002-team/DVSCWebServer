<?php
namespace kv6002\resources;

use dispatcher\Dispatcher;
use dispatcher\exceptions\UndispatchableError;
use router\Response;
use router\resource\BasicResource;
use router\exceptions\HTTPError;
use kv6002\standard\builders\FileBuilder;
use kv6002\standard\builders\NoContentBuilder;

use kv6002\domain;

/**
 * A ping API. Allows you to check for connectivity to the API.
 * 
 * @author William Taylor (19009576)
 */
class Files extends BasicResource {
    public function __construct($pathfinder, $authenticator) {
        $getRawFile = function ($fileType, $fileName) use ($pathfinder) {
            return function ($request) use ($fileType, $fileName, $pathfinder) {
                return [$request, $fileType, file_get_contents(
                    $pathfinder->internalPathFor(
                        "/static-private/$fileName",
                        true // Enforce exists
                    )
                )];
            };
        };

        $fileFetchers = [
            "monthly-report" => function ($request) {
                return [$request, "application/pdf", ""];
            },

            "contract" => $getRawFile("application/pdf", "contract.pdf"),
            "monthly-check-sheet" => $getRawFile("application/pdf", "checklist.pdf"),
            "calibration-dates-document" => $getRawFile("application/pdf", "calibration_dates.pdf"),
            "defective-equipment-log" => $getRawFile("application/pdf", "defective_equipment_log.pdf"),
            "quality-control-sheet" => $getRawFile("application/pdf", "quality_control_checks.pdf"),
            "tyre-depth-check-sheet" => $getRawFile("application/pdf", "tyre_depth_gauge_check.pdf"),
        ];

        $actions = [
            "get_file" => Dispatcher::funcToPipeOf([
                $authenticator->auth(),
                $authenticator->requireAuthentication(),
                $authenticator->requireAuthorisation("general"),
                $authenticator->requireAuthorisation([
                    domain\GarageConsultant::USER_TYPE,
                    domain\Garage::USER_TYPE
                ]),
                function ($request) use ($fileFetchers) {
                    $filename = $request->endpointParam("filename");
                    if ($filename === null || $filename === "") {
                        throw new HTTPError(422,
                            "Must provide a filename to download"
                        );
                    }

                    $fileFetcher = Dispatcher::funcToKeyOf($fileFetchers, $filename);
                    try {
                        return $fileFetcher($request);
                    } catch (UndispatchableError $e) {
                        throw new HTTPError(404, "Requested file not found");
                    }
                },
                function ($request, $fileType, $fileData) {
                    return new Response(
                        200,
                        ["Content-Type" => $fileType],
                        $fileData
                    );
                }
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
         * 'Bottleneck' all actions to into a middle pipeline that requires auth
         * and adds headers, ie.
         *   method ---\                              /--- action
         *              \                            /
         *     method ---+-- getAction() pipeline --+--- action
         *              /                            \
         *   method ---/                              \--- action
         */
        $getAction = function ($actionKey) use ($actions, $authenticator) {
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

        parent::__construct([
            "GET" => $getAction("get_file"),
            "OPTIONS" => $getAction("cors_preflight") // Might not need it yet
        ]);
    }
}
