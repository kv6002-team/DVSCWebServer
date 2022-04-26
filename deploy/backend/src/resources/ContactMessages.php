<?php
namespace kv6002\resources;

use email\EmailDispatcher;
use email\ContactMessage;

use dispatcher\Dispatcher;
use router\resource\BasicResource;
use router\exceptions\HTTPError;
use kv6002\standard\builders\NoContentBuilder;

use kv6002\domain;
use kv6002\daos;

/**
 * Allow authenticated and unauthenticated users to change/reset their password.
 * 
 * @author William Taylor (19009576)
 */
class ContactMessages extends BasicResource {
    private $authenticator;

    public function __construct($db, $authenticator) {
        $this->authenticator = $authenticator;
        $this->dao = new daos\Users($db);
        $this->loggerDAO = new daos\EventLog($db);

        // Which actions can we take?
        $actions = [
            "send_contact_message" => Dispatcher::funcToPipeOf([
                $this->authenticator->auth(),
                function ($request, $user, $authorisations) {
                    try {
                        $hasAuthentication =
                            $this->authenticator->requireAuthentication();
                        $ret = $hasAuthentication(
                            $request,
                            $user,
                            $authorisations
                        );
                    } catch (HTTPError $e) {
                        // If it doesn't have authentication, don't require
                        // authorisation.
                        return [$request, $user, $authorisations];
                    }

                    // If it does have authentication, then require
                    // authorisation, otherwise error.
                    $hasAuthorisation = Dispatcher::funcToPipeOf([
                        $this->authenticator->requireAuthorisation("general"),
                        $this->authenticator->requireAuthorisation(
                            domain\Garage::USER_TYPE
                        )
                    ]);
                    return $hasAuthorisation(...$ret);
                },
                function ($request, $garage, $authorisations) {
                    // Optional (defaults to authenticated garage's email addr)
                    $email = $request->privateParam("emailAddress");
                    if ($email === null || $email === "") {
                        if ($garage !== null) {
                            $email = $garage->emailAddress();
                        } else {
                            throw new HTTPError(422, "Email address not given");
                        }
                    }

                    // Optional (defaults to authenticated garage's phone number)
                    $phone = $request->privateParam("telephoneNumber");
                    if ($phone === null || $phone === "") {
                        if ($garage !== null) {
                            $phone = $garage->telephoneNumber();
                        } else {
                            throw new HTTPError(422, "Telephone number not given");
                        }
                    }

                    // Required
                    $subject = $request->privateParam("subject");
                    if ($phone === null || $phone === "") {
                        throw new HTTPError(422, "Subject not given");
                    }

                    // Required
                    $message = $request->privateParam("message");
                    if ($message === null || $message === "") {
                        throw new HTTPError(422, "Message not given");
                    }

                    // Send
                    EmailDispatcher::send_contactus_email(
                        "mailawrolyatrelay@gmail.com",
                        new ContactMessage($email, $phone, $subject, $message)
                    );

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
        parent::__construct([
            "POST" => $getAction("send_contact_message"),
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
}
