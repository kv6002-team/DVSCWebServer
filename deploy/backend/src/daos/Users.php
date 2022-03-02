<?php
namespace kv6002\daos;

use kv6002\domain;

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
     * @param int $id The user ID of the user to fetch.
     * @return User A user object for that user.
     */
    public function getUser($id) {
        return $this->db->fetch(
            "SELECT id, email as username, password"
            ." FROM user"
            ." WHERE id = :id",
            ["id" => $id],
            domain\User::class
        );
    }

    /**
     * Return a User object for the user in the database with the given
     * username.
     * 
     * @param string $username The username of the user to fetch.
     * @return User A user object for that user.
     */
    public function getUserByUsername($username) {
        return $this->db->fetch(
            "SELECT id, email as username, password"
            ." FROM user"
            ." WHERE username = :username",
            ["username" => $username],
            domain\User::class
        );
    }
}
