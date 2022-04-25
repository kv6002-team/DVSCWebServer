<?php
namespace kv6002\daos;

use kv6002\daos\exceptions\UnsupportedUserTypeError;

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
     * Return the relevant DAO for the given user type.
     * 
     * @throws UnsupportedUserTypeError If the given user type is unrecognised.
     */
    private function getDAOFor($type) {
        switch ($type) {
            case domain\GarageConsultant::USER_TYPE:
                return new daos\GarageConsultants($this->db);

            case domain\Garage::USER_TYPE:
                return new daos\Garages($this->db);
    
            default:
                $typeStr = strval($type); // Just in case it's not a string
                throw new UnsupportedUserTypeError(
                    "Unrecognised user type '$typeStr'"
                );
        }
    }

    /**
     * Get the names of the supported types of user.
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
     * Return a User object for the user in the database with the given ID.
     * 
     * @param string $type The type of user to get.
     * @param int $id The user ID of the user to fetch.
     * 
     * @return User A user object for that user.
     * 
     * @throws UnsupportedUserTypeError If the given user type is unrecognised.
     */
    public function get($type, $id) {
        return $this->getDAOFor($type)->get($id);
    }

    /**
     * Return a User object for the user in the database with the given
     * username.
     * 
     * @param string $type The type of user to get.
     * @param string $username The username of the user to fetch.
     * 
     * @return User A user object for that user, or null if the user type is not
     *   supported.
     * 
     * @throws UnsupportedUserTypeError If the given user type is unrecognised.
     */
    public function getByUsername($type, $username) {
        return $this->getDAOFor($type)->getByUsername($username);
    }

    /**
     * Return an array of User objects containing all users in the database.
     * 
     * @param string $type The type of users to get.
     * 
     * @return array<User> An array of user objects, or null if the user type is
     *   not supported.
     * 
     * @throws UnsupportedUserTypeError If the given user type is unrecognised.
     */
    public function getAll($type) {
        return $this->getDAOFor($type)->getAll();
    }

    /**
     * Add a user to the database.
     * 
     * @param string $type The type of user to add.
     * @param string $password The hashed password for the User.
     * @param bool $passwordResetRequired Whether the User must reset their
     *   password before being allowed to make any further API requests.
     * @param array<mixed> ...$args The parameters to pass to the appropriate
     *   DAO's add method. These will vary depending on the type of user being
     *   created. See the DAO for the new user's type for the parameters
     *   required.
     * 
     * @return User The added user.
     * 
     * @throws UnsupportedUserTypeError If the given user type is unrecognised.
     * @throws DatabaseError If any kind of database error occurs.
     */
    public function add($type, $password, $passwordResetRequired, ...$args) {
        $dao = $this->getDAOFor($type);

        // Add user part
        $this->db->execute(
            "INSERT INTO User (password, passwordResetRequired)"
            ." VALUES ("
            ."   :password,"
            ."   CAST( :passwordResetRequired AS UNSIGNED )"
            ." )",
            [
                "password" => $password,
                "passwordResetRequired" => $passwordResetRequired
            ]
        );
        $id = $this->db->fetch("SELECT max(id) as maxID FROM User")->maxID;

        // Add domain part
        $dao->add($id, ...$args);

        // Commit and return the created object
        $this->db->execute("COMMIT");
        return $dao->get($id);
    }

    /**
     * Update a user in the database.
     * 
     * Note: update() doesn't support updating non-domain user information, eg.
     *       password. For this, use the relevant specific method in this DAO.
     * 
     * @param string $type The type of user to update.
     * @param int $id The ID of the user to update.
     * @param array<mixed> ...$args The parameters to pass to the appropriate
     *   DAO's update method. These will vary depending on the type of user
     *   being updated. See the DAO for the user's type for the parameters
     *   required.
     * 
     * @throws UnsupportedUserTypeError If the given user type is unrecognised.
     * @throws DatabaseError If any kind of database error occurs.
     */
    public function update($type, $id, ...$args) {
        $this->getDAOFor($type)->update($id, ...$args);
        $this->db->execute("COMMIT");
    }

    /**
     * Remove a user from the database.
     * 
     * @param string $type The type of user to remove.
     * @param int $id The ID of the user to remove.
     * 
     * @throws UnsupportedUserTypeError If the given user type is unrecognised.
     * @throws DatabaseError If any kind of database error occurs.
     */
    public function remove($type, $id) {
        // Remove domain part
        $this->getDAOFor($type)->remove($id);

        // Remove user part
        $this->db->execute(
            "DELETE FROM User WHERE id = :id",
            ["id" => $id]
        );

        // Commit
        $this->db->execute("COMMIT");
    }

    /**
     * Change the password of the given user to the given hashed password.
     * 
     * @param User $user The user to change the password for.
     * @param string $newPassword The hashed password to set for that user.
     * 
     * @throws DatabaseError If any kind of database error occurs.
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
