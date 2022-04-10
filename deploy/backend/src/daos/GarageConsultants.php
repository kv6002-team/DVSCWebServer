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
    public function getGarageConsultant($id) {
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
    public function getGarageConsultantByUsername($username) {
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
    public function getGarageConsultants() {
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
     * @param string $emailAddress The email address for the new consultant.
     * @param string $password The hashed password for the new consultant.
     * @param bool $passwordResetRequired Whether the new User must reset
     *   their password before being allowed to make any further API requests.
     */
    public function createGarageConsultant(
            $emailAddress,
            $password,
            $passwordResetRequired
    ) {
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
        $this->db->execute("COMMIT");
    }

    /**
     * Update a Garage Consultant in the database.
     * 
     * @param int $id The ID of the consultant to update.
     * @param string $emailAddress The new email address for the consultant.
     * @param string $password The new hashed password for the consultant.
     * @param bool $passwordResetRequired Whether the User must reset their
     *   password before being allowed to make any further API requests.
     */
    public function updateGarageConsultant(
            $id,
            $emailAddress,
            $password,
            $passwordResetRequired
    ) {
        $this->db->execute(
            "UPDATE User SET"
            ."   password = :password,"
            ."   passwordResetRequired = :passwordResetRequired"
            ." WHERE id = :id",
            [
                "id" => $id,
                "password" => $password,
                "passwordResetRequired" => $passwordResetRequired
            ]
        );

        $this->db->execute(
            "UPDATE GarageConsultant SET"
            ."   emailAddress = :emailAddress"
            ." WHERE id = :id",
            [
                "id" => $id,
                "emailAddress" => $emailAddress
            ]
        );

        $this->db->execute("COMMIT");
    }

    /**
     * Delete a consultant from the database.
     * 
     * @param int $id The ID of the consultant to remove.
     */
    public function removeGarageConsultant($id) {
        $this->db->execute(
            "DELETE FROM GarageConsultant WHERE id = :id",
            ["id" => $id]
        );
        $this->db->execute(
            "DELETE FROM User WHERE id = :id",
            ["id" => $id]
        );
        $this->db->execute("COMMIT");
    }
}
