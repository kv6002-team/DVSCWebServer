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

    //TODO : Garage Creation

}