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

    /**
     * 
     */
    public function add(
            $garageID,
            $name,
            $serialNumber,
            $officialCheckExpiryDate,
            $ourCheckStatus,
            $ourCheckDate
    ) {
        // Add
        $this->db->execute(
            "INSERT INTO Instrument ("
            ."   garageID,"
            ."   name,"
            ."   serialNumber,"
            ."   officialCheckExpiryDate,"
            ."   ourCheckStatus,"
            ."   ourCheckDate"
            ." ) VALUES ("
            ."   :garageID,"
            ."   :name,"
            ."   :serialNumber,"
            ."   :officialCheckExpiryDate,"
            ."   :ourCheckStatus,"
            ."   :ourCheckDate"
            ." )",
            [
                "garageID" => $garageID,
                "name" => $name,
                "serialNumber" => $serialNumber,
                "officialCheckExpiryDate" => standard\DateTime::format($officialCheckExpiryDate),
                "ourCheckStatus" => $ourCheckStatus,
                "ourCheckDate" => standard\DateTime::format($ourCheckDate)
            ]
        );

        // Commit
        $this->db->execute("COMMIT");

        // Return the created object
        return $this->db->fetch(
            "SELECT id,"
            ."   name,"
            ."   serialNumber,"
            ."   officialCheckExpiryDate,"
            ."   ourCheckStatus,"
            ."   ourCheckDate"
            ." FROM Instrument"
            ." WHERE id = ("
            ."   SELECT max(id)"
            ."   FROM Instrument"
            ." )",
            null,
            domain\Instrument::class,
            null,
            [
                new Field("name"),
                new Field("serialNumber"),

                new Field("officialCheckExpiryDate",
                    [standard\DateTime::class, "parse"]),

                new Field("ourCheckStatus",
                    [domain\Instrument::class, "parseOurCheckStatus"]),

                new Field("ourCheckDate",
                    [standard\DateTime::class, "parse"])
            ]
        );
    }

    /**
     * 
     */
    public function update(
            $id,
            $name,
            $officialCheckExpiryDate,
            $ourCheckStatus,
            $ourCheckDate
    ) {
        // Update
        $this->db->execute(
            "UPDATE Instrument SET"
            ."   name = :name,"
            ."   officialCheckExpiryDate = :officialCheckExpiryDate,"
            ."   ourCheckStatus = :ourCheckStatus,"
            ."   ourCheckDate = :ourCheckDate"
            ." WHERE id = :id",
            [
                "id" => $id,
                "name" => $name,
                "officialCheckExpiryDate" => standard\DateTime::format($officialCheckExpiryDate),
                "ourCheckStatus" => $ourCheckStatus,
                "ourCheckDate" => standard\DateTime::format($ourCheckDate)
            ]
        );

        // Commit
        $this->db->execute("COMMIT");
    }

    /**
     * 
     */
    public function remove($id){
        $this->db->execute(
            "DELETE FROM Instrument WHERE id = :id",
            ["id" => $id]
        );
        $this->db->execute("COMMIT");
    }
} 