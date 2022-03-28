<?php
namespace kv6002\daos;

use database\Field;

use kv6002\standard;
use kv6002\domain;

/**
 * Allows retrieving garages
 * 
 * @author Callum
 */
class Garages{
    private $db;

    public function __construct($db){
        $this->db = $db;
    }

    /**
     * Return a Garage object for the Garage within the database 
     * with a given ID 
     * 
     * @param int $id The ID of the Garage to fetch
     * @return Garage A Garage object for that garage
     */
    public function getGarage($id){
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
                new Field("password"),
                new Field("passwordResetRequired")
            ]
        );
    } 

    /**
     * Return all Garages in the database.
     * 
     * @return Garage A Garage object for that Garage
     */
    public function getGarages(){
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
                new Field("password"),
                new Field("passwordResetRequired")
            ]
        );
    }

    
    /**
     * Add Garage to ther database
     * 
     * @param string $vts The VTS number for the Garage
     * @param string $name The name of the Garage
     * @param string $ownerName The name of the owner of the Garage
     * @param string $emailAddress The email address of the Garage
     * @param string $telephoneNumber The telephone number of the Garage
     * @param string $paidUntil The date of which the garage has paid until
     * @param string $password The hashed password for the new consultant.
     * @param bool $passwordResetRequired Whether the new User must reset
     * 
     */
    public function createGarage(
            $vts,
            $name,
            $ownerName,
            $emailAddress,
            $telephoneNumber,
            $paidUntil,
            $password,
            $passwordResetRequired
    ){
        $this->db->execute(
            "INSERT INTO User (password, passwordResetRequired)"
            ." VALUES ("
            ."   :password,"
            ."   :passwordResetRequired"
            ." )",
            [
                "password" => $password,
                "passwordResetRequired" => $passwordResetRequired    
            ]
        );
        $id = $this->db->fetch("SELECT max(id) as maxID FROM User")->maxID;

        $this->db->execute(
            "INSERT INTO Garage (id, vts, name, ownerName, emailAddress, telephoneNum, paidUntil)"
            ." VALUES ("
            ."   :id,"
            ."   :vts,"
            ."   :name,"
            ."   :ownerName,"
            ."   :emailAddress,"
            ."   :telephoneNum,"
            ."   :paidUntil"
            ." )",
            [
                "id" => $id,
                "vts" => $vts,
                "name" => $name,
                "ownerName" => $ownerName
                "emailAddress" => $emailAddress
                "telephoneNum" => $telephoneNum
                "paidUntil" => $paidUntil
            ]
        );
        $this->$db->execute("COMMIT");
    }

}