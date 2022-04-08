<?php
namespace kv6002\resources;

use util\Util;
use html\HTML;

use dispatcher\Dispatcher;
use router\resource\BasicResource;
use router\exceptions\HTTPError;
use kv6002\standard\builders\JSONBuilder;
use kv6002\standard\builders\NoContentBuilder;

use kv6002\daos;
use kv6002\views;

/**
 * 
 * 
 * @author Liam
 */
class Emails extends BasicResource {
    public function __construct($db) {
        $dao = new daos\Garages($db);

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

                    $allGarages = $dao->getGarages();
                    $requestedGarages = Util::filterValues(
                        $allGarages,
                        function ($garage) use ($garageIDs) {
                            return in_array($garage->id(), $garageIDs);
                        },
                        false
                    );
                    $formattedEmails = $this->formatEmails($requestedGarages);
                        //Send Emails
                    return [
                        $request, $formattedEmails
                    ];
                },
                JSONBuilder::typeSelector(
                    function ($request, $formattedEmails) {
                        return $formattedEmails;
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

    public function formatEmails($requestedGarages) {
        return Util::mapValues(
            $requestedGarages, 
            function ($garage){
                return [
                    "to" => [
                        ["address" => $garage->emailAddress()]
                    ],
                    "msg" => [
                        "subject" => "MOT - Instrument official check reminder",
                        "html" => html::div(null)->toString(),
                        "attachments" => []
                    ]
                ];
            }, 
            false
        );
    }
}