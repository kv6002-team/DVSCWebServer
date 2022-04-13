<?php
namespace kv6002\daos;

use router\exceptions\HTTPError;

use kv6002\domain;
use kv6002\daos;

/**
 * Allows retrieving users.
 * 
 * @author William Taylor (19009576)
 */
class Users {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Return a User object for the user in the database with the given ID.
     * 
     * @param string $type The type of user to get.
     * @param int $id The user ID of the user to fetch.
     * @return User A user object for that user.
     */
    public function getUser($type, $id) {
        switch ($type) {
            case domain\GarageConsultant::USER_TYPE:
                $dao = new daos\GarageConsultants($this->db);
                return $dao->getGarageConsultant($id);

            case domain\Garage::USER_TYPE:
                $dao = new daos\Garages($this->db);
                return $dao->getGarage($id);
    
            default:
                return null;
        }
    }

    /**
     * Return a User object for the user in the database with the given
     * username.
     * 
     * @param string $type The type of user to get.
     * @param string $username The username of the user to fetch.
     * @return User A user object for that user.
     */
    public function getUserByUsername($type, $username) {
        switch ($type) {
            case domain\GarageConsultant::USER_TYPE:
                $dao = new daos\GarageConsultants($this->db);
                return $dao->getGarageConsultantByUsername($username);

            case domain\Garage::USER_TYPE:
                $dao = new daos\Garages($this->db);
                return $dao->getGarageByUsername($username);
    
            default:
                return null;
        }
    }

    /**
     * Return a list of the supported types of user.
     * 
     * @return array<string> A list of the supported types of user, as strings.
     */
    public function getSupportedUserTypes() {
        return [
            domain\GarageConsultant::USER_TYPE,
            domain\Garage::USER_TYPE
        ];
    }

    /**
     * Change the password of the given user to the given hashed password.
     * 
     * @param User $user The user to change the password for.
     * @param string $newPassword The hashed password to set for that user.
     */
    public function changePassword($user, $newPassword) {
        $this->db->execute(
            "UPDATE User SET"
            ."   password = :password,"
            ."   passwordResetRequired = false"
            ." WHERE id = :id",
            [
                "id" => $user->id(),
                "password" => $newPassword
            ]
        );
        $this->db->execute("COMMIT");
    }
}
