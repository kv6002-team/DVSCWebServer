<?php
namespace kv6002\resources;

use util\Util;
use html\HTML;
use email\EmailDispatcher;

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

    public function __construct($db) {
        $dao = new daos\Users($db);
        $this->loggerDAO = new daos\EventLog($db);

        $actions = [
            "send_garage_emails" => Dispatcher::funcToPipeOf([
                function ($request) use ($dao){
                    try {
                        $body = Util::toJSON($request->body());
                    } catch (JsonException $e) {
                        throw new HTTPError(422,
                            "Requested Garage list JSON is invalid"
                        );
                    }

                    $garageIDs = [];
                    if (!isset($body["garages"])) {
                        throw new HTTPError(422,
                            "Garage ID list was not given"
                        );
                    }
                    foreach ($body["garages"] as $garageID) {
                        if (!is_numeric($garageID)) {
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
                            return \email\generate_dispatcher_object(
                                $garage->emailAddress(),
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
                            "Report Emails Sent",
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
