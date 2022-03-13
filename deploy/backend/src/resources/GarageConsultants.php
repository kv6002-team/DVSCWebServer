<?php
namespace kf6012\resources;

use dispatcher\Dispatcher;
use router\resource\BasicResource;
use router\exceptions\HTTPError;
use kf6012\standard\builders\JSONBuilder;

use router\Request;
use router\resource\WithMetadata;
use router\resource\MetadataUtils;

use kf6012\daos;
use kf6012\views;

/**
 * Provide a list of garage consultants.
 * 
 * @author William Taylor (19009576)
 */
class GarageConsultants extends BasicResource implements WithMetadata {
    public function __construct($db) {
        $dao = new daos\GarageConsultants($db);

        $this->view = new views\GarageConsultantsJSON();

        // Which actions can we take?
        $GETActions = [
            "get_all" => Dispatcher::funcToPipeOf([
                function ($request) use ($dao) {
                    return [
                        $request,
                        $dao->getGarageConsultants()
                    ];
                },
                JSONBuilder::typeSelector(
                    function ($request, $consultants) {
                        return $this->view->garageConsultants($consultants);
                    }
                )
            ]),
            
            "get_one" => Dispatcher::funcToPipeOf([
                function ($request) {
                    return [$request, $request->param("id")];
                },
                function ($request, $id) use ($dao) {
                    $consultants = $dao->getGarageConsultant($id);
                    if ($consultants === null) {
                        throw new HTTPError(404,
                            "Requested garage consultant does not exist"
                        );
                    }
                    return [$request, $consultants];
                },
                JSONBuilder::typeSelector(
                    function ($request, $consultants) {
                        return $this->view->garageConsultants($consultants);
                    }
                )
            ])
        ];
        
        // Which action should we take?
        $GETSelectAction = function ($request) {
            if ($request->param("id") !== null) {
                return "get_one";
            }
            return "get_all";
        };

        // Compose (Always add CORS headers)
        $headers = ["Access-Control-Allow-Origin" => "*"];
        parent::__construct([
            "GET" => Dispatcher::funcToPipeOf([
                Dispatcher::funcToKeyOf($GETActions, ),
                BasicResource::addHeaders($headers)
            ])
        ]);
    }
}
