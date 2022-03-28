 <?php
namespace kv6002\resources;

use dispatcher\Dispatcher;
use router\resource\BasicResource;
use router\exceptions\HTTPError;
use kv6002\standard\builders\JSONBuilder;

use kv6002\daos;
use kv6002\views;

/**
 * 
 * provide a list of garages
 * 
 * @author Callum
 */
class Garages extends BasicResource{
    public function __construct($db){
        $dao = new daos\Garages($db);

        $this->view = new views\GaragesJSON();
        
        
        $actions = [
            "get_all_simple" => Dispatcher::funcToPipeOf([
                function($request) use ($dao){
                    return  [
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
                    return  [$request, $request->endpointParam("id")];
                },
                function($request, $id) use ($dao){
                    $garage = $dao->getGarage($id);
                    if($garage === null){
                        throw new HTTPError(404,
                            "Requested Garage does not exist"
                        );
                    }
                    return  [$request, $garage];
                },
                JSONBuilder::typeSelector(
                    function($request, $garage){
                        return $this->view->garage($garage);
                    }
                )
            ]),

            "create" => Dispatcher::funcToPipeOf([
                function ($request) {
                    $vts = $request->privateParam("vts");
                    if($vts === null){
                        throw new HTTPError(422,
                            "Must provide vts parameter"
                        );
                    }

                    $name = $request->privateParam("name");
                    if($name === null){
                        throw new HTTPError(422,
                            "Must provide name parameter"
                        );
                    }

                    $ownerName = $request->privateParam("ownerName");
                    if($ownerName === null){
                        throw new HTTPError(422,
                            "Must provide ownerName parameter"
                        );
                    }

                    $emailAddress = $request->privateParam("emailAddress");
                    if($emailAddress === null){
                        throw new HTTPError(422,
                            "Must provide emailAddress parameter"
                        );
                    }

                    $telephoneNumber = $request->privateParam("telephoneNumber");
                    if($telephoneNumber === null){
                        throw new HTTPError(422,
                            "Must provide telephoneNumber parameter"
                        );
                    }

                    $paidUntil = $request->privateParam("paidUntil");
                    if($paidUntil === null){
                        throw new HTTPError(422,
                            "Must provide paidUntil parameter"
                        );
                    }

                    $password = $request->privateParam("password");
                    if($paidUntil === null){
                        throw new HTTPError(422,
                            "Must provide password parameter"
                        );
                    }

                    return  [
                                $request, 
                                $vts, 
                                $name, 
                                $ownerName, 
                                $emailAddress, 
                                $telephoneNumber, 
                                $paidUntil, 
                                $password
                            ];
                },
                function ($request, $vts, $name, $ownerName, $emailAddress, $telephoneNumber, $paidUntil, $password) use ($dao) {
                    $dao->createGarage(
                        $vts,
                        $name, 
                        $ownerName, 
                        $emailAddress,
                        $telephoneNumber, 
                        $paidUntil,
                        password_hash($password, PASSWORD_DEFAULT),
                        false
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
            )
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