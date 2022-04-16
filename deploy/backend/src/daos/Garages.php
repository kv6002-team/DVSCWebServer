<?php
namespace kv6002\daos;

use database\Field;
use database\RecordValue;
use database\Database;

use kv6002\standard;
use kv6002\domain;

/**
 * Allows retrieving garages.
 * 
 * @author Callum
 */
class Garages{
    private $db;

    public function __construct($db){
        $this->db = $db;
    }

    /**
     * Return a Garage object for the Garage within the database with a given
     * ID.
     * 
     * @param int $id The ID of the Garage to fetch.
     * @return Garage A Garage object for that garage, including the list of
     *   Instruments that Garage manages.
     */
    public function get($id) {
        // Some of these could throw InvalidArgumentException (parse
        // ourCheckStatus) or Exception (parse DateTime). These will be caught
        // by the global error handler and a 500 error emitted.

        $instruments = $this->db->fetchAll(
            "SELECT Instrument.id,"
            ."   garageID,"
            ."   name,"
            ."   serialNumber,"
            ."   officialCheckExpiryDate,"
            ."   ourCheckStatus,"
            ."   ourCheckDate"
            ." FROM Instrument"
            ." WHERE garageID = :garageID",
            ["garageID" => $id],
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
        $instrumentsByGarage = Database::groupByFKey($instruments, "garageID");

        return $this->db->fetch(
            "SELECT Garage.id,"
            ."   vts,"
            ."   name,"
            ."   ownerName,"
            ."   emailAddress,"
            ."   telephoneNumber,"
            ."   paidUntil,"
            ."   password,"
            ."   passwordResetRequired"
            ." FROM Garage"
            ." JOIN User ON Garage.id = User.id"
            ." WHERE Garage.id = :id",
            ["id" => $id],
            domain\Garage::class,
            null,
            [
                new Field("vts"),
                new Field("name"),
                new Field("ownerName"),
                new Field("emailAddress"),
                new Field("telephoneNumber"),
                new Field("paidUntil", [standard\DateTime::class, "parse"]),
                new RecordValue($instrumentsByGarage, []),
                new Field("password"),
                new Field("passwordResetRequired")
            ]
        );
    } 

    /**
     * Return a Garage object for the garage in the database with the given
     * username (VTS number).
     * 
     * @param string $username The username (VTS number) of the garage to fetch.
     * @return Garage A Garage object for that garage.
     */
    public function getByUsername($username) {
        // Some of these could throw InvalidArgumentException (parse
        // ourCheckStatus) or Exception (parse DateTime). These will be caught
        // by the global error handler and a 500 error emitted.

        $instruments = $this->db->fetchAll(
            "SELECT DISTINCT Instrument.id,"
            ."   garageID,"
            ."   Instrument.name,"
            ."   serialNumber,"
            ."   officialCheckExpiryDate,"
            ."   ourCheckStatus,"
            ."   ourCheckDate"
            ." FROM Instrument"
            ." JOIN Garage"
            ." WHERE vts = :garageVTS",
            ["garageVTS" => $username],
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
        $instrumentsByGarage = Database::groupByFKey($instruments, "garageID");

        return $this->db->fetch(
            "SELECT Garage.id,"
            ."   vts,"
            ."   name,"
            ."   ownerName,"
            ."   emailAddress,"
            ."   telephoneNumber,"
            ."   paidUntil,"
            ."   password,"
            ."   passwordResetRequired"
            ." FROM Garage"
            ." JOIN User ON Garage.id = User.id"
            ." WHERE vts = :garageVTS",
            ["garageVTS" => $username],
            domain\Garage::class,
            null,
            [
                new Field("vts"),
                new Field("name"),
                new Field("ownerName"),
                new Field("emailAddress"),
                new Field("telephoneNumber"),
                new Field("paidUntil", [standard\DateTime::class, "parse"]),
                new RecordValue($instrumentsByGarage, []),
                new Field("password"),
                new Field("passwordResetRequired")
            ]
        );
    }

    /**
     * Return all Garages in the database.
     * 
     * @return array<Garage> A list of Garage object containing every garage in
     *   the database.
     */
    public function getAll() {
        // Some of these could throw InvalidArgumentException (parse
        // ourCheckStatus) or Exception (parse DateTime). These will be caught
        // by the global error handler and a 500 error emitted.

        $instruments = $this->db->fetchAll(
            "SELECT Instrument.id,"
            ."   garageID,"
            ."   name,"
            ."   serialNumber,"
            ."   officialCheckExpiryDate,"
            ."   ourCheckStatus,"
            ."   ourCheckDate"
            ." FROM Instrument",
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
        $instrumentsByGarage = Database::groupByFKey($instruments, "garageID");

        return $this->db->fetchAll(
            "SELECT Garage.id,"
            ."   vts,"
            ."   name,"
            ."   ownerName,"
            ."   emailAddress,"
            ."   telephoneNumber,"
            ."   paidUntil,"
            ."   password,"
            ."   passwordResetRequired"
            ." FROM Garage"
            ." JOIN User ON Garage.id = User.id",
            null,
            domain\Garage::class,
            null,
            [
                new Field("vts"),
                new Field("name"),
                new Field("ownerName"),
                new Field("emailAddress"),
                new Field("telephoneNumber"),
                new Field("paidUntil", [standard\DateTime::class, "parse"]),
                new RecordValue($instrumentsByGarage, []),
                new Field("password"),
                new Field("passwordResetRequired")
            ]
        );
    }

    /**
     * Add a Garage to the database.
     * 
     * @param int $id The ID of for the Garage User.
     * @param string $vts The VTS number for the Garage.
     * @param string $name The name for the Garage.
     * @param string $ownerName The name of the owner for the Garage.
     * @param string $emailAddress The email address for the Garage.
     * @param string $telephoneNumber The telephone number for the Garage.
     * @param DateTimeImmutable $paidUntil The date up to which the Garage has
     *   paid fees.
     * 
     * @throws DatabaseError If any kind of database error occurs.
     */
    public function add(
            $id,
            $vts,
            $name,
            $ownerName,
            $emailAddress,
            $telephoneNumber,
            $paidUntil
    ) {
        $this->db->execute(
            "INSERT INTO Garage ("
            ."   id,"
            ."   vts,"
            ."   name,"
            ."   ownerName,"
            ."   emailAddress,"
            ."   telephoneNumber,"
            ."   paidUntil"
            ." ) VALUES ("
            ."   :id,"
            ."   :vts,"
            ."   :name,"
            ."   :ownerName,"
            ."   :emailAddress,"
            ."   :telephoneNumber,"
            ."   :paidUntil"
            ." )",
            [
                "id" => $id,
                "vts" => $vts,
                "name" => $name,
                "ownerName" => $ownerName,
                "emailAddress" => $emailAddress,
                "telephoneNumber" => $telephoneNumber,
                "paidUntil" => standard\DateTime::format($paidUntil)
            ]
        );
    }
}
