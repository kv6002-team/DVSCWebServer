<?php
namespace kv6002\daos;

use database\Field;
use database\RecordValue;
use database\Database;

use kv6002\standard;
use kv6002\domain;

/**
 * Allows retrieving instruments
 * 
 * @author Callum
 * 
 */
class Instruments{
    private $db;

    public function __construct($db){
        $this->db = $db;
    }

    public function add(
            $id,
            $garageID,
            $name,
            $serialNumber,
            $officialCheckExpiryDate,
            $ourCheckStatus,
            $ourCheckDate
    ) {
        $this->db->execute(
            "INSERT INTO Instrument ("
            ."   id,"
            ."   garageID,"
            ."   name,"
            ."   serialNumber,"
            ."   officialCheckExpiryDate,"
            ."   ourCheckStatus,"
            ."   ourCheckDate"
            ." ) VALUES ("
            ."   :id,"
            ."   :garageID,"
            ."   :name,"
            ."   :serialNumber,"
            ."   :officialCheckExpiryDate,"
            ."   :ourCheckStatus,"
            ."   :outCheckDate"
            ." )",
            [
                "id" => $id,
                "garageID" => $garageID,
                "name" => $name,
                "serialNumber" => $serialNumber,
                "officialCheckExpiryDate" => standard\DateTime::format($officialCheckExpiryDate),
                "ourCheckStatus" => $ourCheckStatus,
                "ourCheckDate" => standard\DateTime::format($ourCheckDate)
            ]
        );
    }

    public function remove($id){
        $this->db->execute(
            "DELETE FROM Instrument WHERE id = :id",
            ["id" => $id]
        );
    }
}