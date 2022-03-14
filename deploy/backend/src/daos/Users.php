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
            case "garage-consultant":
                $dao = new daos\GarageConsultants();
                return $dao->getGarageConsultant($id);

            case "garage":
                throw new HTTPError(501,
                    "Getting 'garage'-type Users is not yet implemented"
                );
    
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
            case "garage-consultant":
                $dao = new daos\GarageConsultants();
                return $dao->getGarageConsultantByUsername($username);

            case "garage":
                throw new HTTPError(501,
                    "Getting 'garage'-type Users is not yet implemented"
                );
    
            default:
                return null;
        }
    }
}
