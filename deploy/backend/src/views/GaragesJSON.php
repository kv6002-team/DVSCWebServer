<?php
namespace kv6002\views;

use kv6002\standard;

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
            "paidUntil" => "(string) The date up to which the Garage has paid"
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
            "paidUntil" => standard\DateTime::format($garage->paidUntil()),

            "instruments" => array_map(function ($instrument) {
                return [
                    "id" => $instrument->id(),
                    "name" => $instrument->name(),
                    "serialNumber" => $instrument->serialNumber(),
                    "officialCheckExpiryDate" => standard\DateTime::format(
                        $instrument->officialCheckExpiryDate()
                    ),
                    "ourCheckStatus" => $instrument->ourCheckStatus(),
                    "ourCheckDate" => standard\DateTime::format(
                        $instrument->ourCheckDate()
                    )
                ];
            }, $garage->instruments())
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