<?php
namespace kv6002\resources;

use email\EmailContent;

use dispatcher\Dispatcher;
use dispatcher\exceptions\UndispatchableError;
use router\Response;
use router\resource\BasicResource;
use router\exceptions\HTTPError;
use kv6002\standard\builders\PDFBuilder;
use kv6002\standard\builders\NoContentBuilder;

use kv6002\daos;
use kv6002\domain;

/**
 * A ping API. Allows you to check for connectivity to the API.
 * 
 * @author William Taylor (19009576)
 */
class Files extends BasicResource {
    private $usersDAO;

    public function __construct($db, $pathfinder, $authenticator) {
        $this->usersDAO = new daos\Users($db);

        $staticFiles = [
            "contract" => "contract.pdf",
            "monthly-check-sheet" => "checklist.pdf",
            "calibration-dates-document" => "calibration_dates.pdf",
            "defective-equipment-log" => "defective_equipment_log.pdf",
            "quality-control-sheet" => "quality_control_checks.pdf",
            "tyre-depth-check-sheet" => "tyre_depth_gauge_check.pdf"
        ];

        // Actions
        $actions = [
            "get_static_file" => Dispatcher::funcToPipeOf([
                $authenticator->auth(),
                $authenticator->requireAuthentication(),
                $authenticator->requireAuthorisation("general"),
                $authenticator->requireAuthorisation(
                    domain\GarageConsultant::USER_TYPE,
                    domain\Garage::USER_TYPE
                ),
                function ($request) use ($staticFiles) {
                    $fileName = $request->endpointParam("filename");
                    $realFileName = $staticFiles[$fileName];
                    $fileData = file_get_contents(
                        $pathfinder->internalPathFor(
                            "/static-private/$realFileName",
                            true // Enforce exists
                        )
                    );
                    return [new Response(
                        200,
                        ["Content-Type" => "application/pdf"],
                        $fileData
                    )];
                }
            ]),

            "get_monthly_report_file" => Dispatcher::funcToPipeOf([
                $authenticator->auth(),
                function ($request, $user, $authorisations) {
                    $garageID = $request->param("garage");
                    if (!is_numeric($garageID) || str_contains($garageID, ".")) {
                        throw new HTTPError(422,
                            "Must specify which garage to get the monthly report for"
                        );
                    }
                    $garageID = intval($garageID);

                    return [$request, $garageID, $user, $authorisations];
                },
                function ($request, $garageID, $user, $authorisations) use ($authenticator) {
                    try {
                        $authProcess = Dispatcher::funcToFirstSuccessfulOf([
                            // Any garage consultant
                            Dispatcher::funcToPipeOf([
                                $authenticator->requireAuthentication(),
                                $authenticator->requireAuthorisation("general"),
                                $authenticator->requireAuthorisation(
                                    domain\GarageConsultant::USER_TYPE
                                )
                            ]),

                            // The garage that is being requested
                            Dispatcher::funcToPipeOf([
                                $authenticator->requireAuthentication($garageID),
                                $authenticator->requireAuthorisation("general"),
                                $authenticator->requireAuthorisation(
                                    domain\Garage::USER_TYPE
                                )
                            ])
                        ]);
                        $checked = $authProcess($request, $user, $authorisations);
                        return [$checked[0], $garageID, $checked[1]];

                    } catch (\Exception $e) {
                        throw new HTTPError(401,
                            "Not an authorised garage or garage consultant"
                        );
                    }
                },
                function ($request, $garageID, $user) {
                    $filename = $request->endpointParam("filename");
                    if ($filename === null || $filename === "") {
                        throw new HTTPError(422,
                            "Must provide a filename to download"
                        );
                    }

                    $garage = $this->usersDAO->get(
                        domain\Garage::USER_TYPE,
                        $garageID
                    );
                    if ($garage === null) {
                        throw new HTTPError(404,
                            "Cannot get monthly report for garage that does not exist"
                        );
                    }

                    $content = new EmailContent(
                        $garage->name(),
                        $garage->instruments()
                    );

                    $date = date();
                    return [
                        $request,
                        $content->get_email_html_string(),
                        "monthly-report-$date.pdf"
                    ];
                },
                PDFBuilder::typeSelector(
                    function ($request, $html, $filename) {
                        return [$html, $filename];
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
            "GET" => $getAction(
                function ($request) use ($staticFiles) {
                    $fileName = $request->endpointParam("filename");
                    if ($fileName !== null && in_array($fileName, array_keys($staticFiles))) {
                        return "get_static_file";
                    }
                    return "get_monthly_report_file";
                }
            ),
            "OPTIONS" => $getAction("cors_preflight") // Might not need it yet
        ]);
    }
}
