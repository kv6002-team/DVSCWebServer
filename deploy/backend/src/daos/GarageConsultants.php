<?php
namespace kv6002\daos;

use database\Field;

use kv6002\domain;

/**
 * Allows retrieving garage consultants.
 * 
 * @author William Taylor (19009576)
 */
class GarageConsultants {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Return a Garage Consultant object for the consultant in the database with
     * the given ID.
     * 
     * @param int $id The ID of the consultant to fetch.
     * @return GarageConsultant A GarageConsultant object for that consultant.
     */
    public function get($id) {
        return $this->db->fetch(
            "SELECT DISTINCT GarageConsultant.id,"
            ."   emailAddress,"
            ."   password,"
            ."   passwordResetRequired"
            ." FROM GarageConsultant"
            ." JOIN User ON GarageConsultant.id = User.id"
            ." WHERE GarageConsultant.id = :id",
            ["id" => $id],
            domain\GarageConsultant::class,
            null,
            [
                new Field("emailAddress"),
                new Field("password"),
                new Field("passwordResetRequired")
            ]
        );
    }

    /**
     * Return a Garage Consultant object for the consultant in the database with
     * the given username (email address).
     * 
     * @param string $username The username (email address) of the consultant to
     *   fetch.
     * @return GarageConsultant A GarageConsultant object for that consultant.
     */
    public function getByUsername($username) {
        return $this->db->fetch(
            "SELECT DISTINCT GarageConsultant.id,"
            ."   emailAddress,"
            ."   password,"
            ."   passwordResetRequired"
            ." FROM GarageConsultant"
            ." JOIN User ON GarageConsultant.id = User.id"
            ." WHERE emailAddress = :username",
            ["username" => $username],
            domain\GarageConsultant::class,
            null,
            [
                new Field("emailAddress"),
                new Field("password"),
                new Field("passwordResetRequired")
            ]
        );
    }

    /**
     * Return all Garage Consultants in the database.
     * 
     * @param string $username The username (email address) of the consultant to
     *   fetch.
     * @return GarageConsultant A GarageConsultant object for that consultant.
     */
    public function getAll() {
        return $this->db->fetchAll(
            "SELECT DISTINCT GarageConsultant.id,"
            ."   emailAddress,"
            ."   password,"
            ."   passwordResetRequired"
            ." FROM GarageConsultant"
            ." JOIN User ON GarageConsultant.id = User.id",
            null,
            domain\GarageConsultant::class,
            null,
            [
                new Field("emailAddress"),
                new Field("password"),
                new Field("passwordResetRequired")
            ]
        );
    }

    /**
     * Add a Garage Consultant to the database.
     * 
     * @param string $emailAddress The email address for the consultant.
     * @throws DatabaseError If any kind of database error occurs.
     */
    public function add($id, $emailAddress) {
        $this->db->execute(
            "INSERT INTO GarageConsultant (id, emailAddress)"
            ." VALUES ("
            ."   :id,"
            ."   :emailAddress"
            ." )",
            [
                "id" => $id,
                "emailAddress" => $emailAddress
            ]
        );
    }

    /**
     * Update a Garage Consultant in the database.
     * 
     * @param int $id The ID of the consultant to update.
     * @param string $emailAddress The new email address for the consultant.
     * 
     * @throws DatabaseError If any kind of database error occurs.
     */
    public function update($id, $emailAddress) {
        $this->db->execute(
            "UPDATE GarageConsultant SET"
            ."   emailAddress = :emailAddress"
            ." WHERE id = :id",
            [
                "id" => $id,
                "emailAddress" => $emailAddress
            ]
        );
    }

    /**
     * Remove a Garage Consultant from the database.
     * 
     * @param int $id The ID of the consultant to remove.
     * 
     * @throws DatabaseError If any kind of database error occurs.
     */
    public function remove($id) {
        $this->db->execute(
            "DELETE FROM GarageConsultant WHERE id = :id",
            ["id" => $id]
        );
    }
}
