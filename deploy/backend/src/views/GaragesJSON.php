<?php
namespace kv6002\views;

/**
 * @author Callum
 */

 class GaragesJSON{
    /**
    * Return the schema for a simple Garage information in this view as a 
    * JSON-encodable array
    * 
    * @return array The JSON-encodable schema for simple Garage information
    */
    public function simpleGarageSchema(){
        return [
            "id" => "(integer) The Garage's ID",
            "name" => "(string) Name of the Garage" 
        ];
    }

    public function fullGarageSchema(){
        return [
            "id" => "(integer) The Garage's ID",
            "vts" => "(string) the VTS number of the Garage",
            "name" => "(string) Name of the Garage",
            "ownerName" => "(string) The name of the Garage's owner",
            "emailAddress" => "(string) The email address for the Garage",
            "telephoneNumber" => "(string) The telephone number for the garage",
            "paidUntil" => "(DateTime) The date up to which the Garage has paid"
        ];
    }

    /**
     * Return JSON view of given Garage as JSON-encodable array.
     * 
     * @param Garage The Garage to return the JSON value for
     * 
     * @return array The JSON-encodable view of the given Garage
     * 
     */
    public function garage($garage){
        return  [
            "id" => $garage->id(),
            "vts" => $garage->vts(),
            "name" => $garage->name(),
            "ownerName" => $garage->ownerName(),
            "emailAddress" => $garage->emailAddress(),
            "telephoneNumber" => $garage->telephoneNumber(),
            "paidUntil" => $garage->paidUntil()
        ];
    }


    /**
     * Return JSON view of SimpleGarages as a JSON-encodable 
     * array
     * 
     *  @param array<simpleGarage> The SimpleGarages to return the JSON view for
     *  
     *  @return array The JSON-encodable view of the given SimpleGarages 
     */
    public function simpleGarages($garages){
        return array_map(function ($garage) {
            return  [
                "id" => $garage->id(),
                "name" => $garage->name()
            ];
        }, $garages);    
    }
 }