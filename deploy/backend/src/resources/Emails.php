<?php
namespace kv6002\resources;

use util\Util;
use html\HTML;
use email\EmailDispatcher;
use email\Email;
use email\EmailContent;

use dispatcher\Dispatcher;
use router\resource\BasicResource;
use router\exceptions\HTTPError;
use kv6002\standard\builders\JSONBuilder;
use kv6002\standard\builders\NoContentBuilder;
use kv6002\standard\DateTime;

use kv6002\domain;
use kv6002\daos;
use kv6002\views;

require_once __DIR__ . "/../../lib/email/DispatcherObject.php";

/**
 * 
 * 
 * @author Liam
 */
class Emails extends BasicResource {
    private $loggerDAO;

    public function __construct($db, $authenticator) {
        $dao = new daos\Users($db);
        $this->loggerDAO = new daos\EventLog($db);

        $actions = [
            "send_garage_emails" => Dispatcher::funcToPipeOf([
                $authenticator->auth(),
                $authenticator->requireAuthentication(),
                $authenticator->requireAuthorisation("general"),
                $authenticator->requireAuthorisation(
                    domain\GarageConsultant::USER_TYPE
                ),

                function ($request) use ($dao) {
                    // Parse JSON input
                    $garageJSON = $request->privateParam("garages");
                    if ($garageJSON === null) {
                        throw new HTTPError(422,
                            "Garage list not sent"
                        );
                    }

                    try {
                        $garageIDList = Util::toJSON($garageJSON);
                    } catch (JsonException $e) {
                        throw new HTTPError(422,
                            "Garage ID list is invalid JSON"
                        );
                    }

                    // Validate JSON input
                    if (!is_array($garageIDList)) {
                        throw new HTTPError(422,
                            "Garage ID list was not given as a JSON list"
                        );
                    }

                    $garageIDs = [];
                    foreach ($garageIDList as $garageID) {
                        if (
                            !is_numeric($garageID) ||
                            str_contains($garageID, ".")
                        ) {
                            throw new HTTPError(422,
                                "Garage ID list contained an invalid ID"
                            );
                        }
                        $garageIDs[] = $garageID;
                    }

                    $allGarages = $dao->getAll(domain\Garage::USER_TYPE);
                    $requestedGarages = Util::filterValues(
                        $allGarages,
                        function ($garage) use ($garageIDs) {
                            return (
                                in_array($garage->id(), $garageIDs) &&
                                count($garage->instruments()) > 0
                            );
                        },
                        false
                    );
                    $dispatcherObjects = Util::mapValues(
                        $requestedGarages,
                        function ($garage) {
                            return new Email(
                                $garage->emailAddress(),
                                $garage->name(),
                                (new EmailContent(
                                    $garage->name(),
                                    Util::mapValues(
                                        $garage->instruments(),
                                        function ($instrument) {
                                            return [
                                                "instrument_name" => $instrument->name(),
                                                "instrument_serial_number" => $instrument->serialNumber(),
                                                "instrument_expiry_date" => $instrument->officialCheckExpiryDate()
                                            ];
                                        },
                                        false
                                    )
                                ))->get_email_html_string(),
                                null,
                                true
                            );
                        },
                        false
                    );
                    
                    $emailDispatcher = new EmailDispatcher($dispatcherObjects);
                    $emailDispatcher->send_emails();

                    try {
                        $this->loggerDAO->add(
                            daos\EventLog::MESSAGE_EVENT,
                            daos\EventLog::INFO_LEVEL,
                            "Report emails sent",
                            new \DateTimeImmutable("now")
                        );
                    } catch (DatabaseError $e) { /*Do nothing*/ }

                    return [$request];
                },
                JSONBuilder::typeSelector(
                    function ($request) {
                        return [];
                    }
                )
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
            "POST" => $getAction("send_garage_emails")
        ]);
    }
}
