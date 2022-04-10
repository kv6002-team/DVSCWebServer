<?php
namespace kv6002\domain;

use database\Bindable;

/**
 * A user (account).
 * 
 * @author William Taylor (19009576)
 */
abstract class User {
    use Bindable;

    private $id;

    private $password;
    private $passwordResetRequired;

    /**
     * Create a new user.
     * 
     * @param string $password The User's hashed password.
     * @param bool $passwordResetRequired Whether the User must reset their
     *   password before being allowed to make any further API requests.
     */
    public function __construct($password, $passwordResetRequired) {
        $this->password = $password;
        $this->passwordResetRequired = $passwordResetRequired;
    }

    /**
     * Get the ID of the User.
     * 
     * @return int The ID of the User.
     */
    public function id() {
        return intval($this->id);
    }

    /**
     * Get the username of the User.
     * 
     * @return string The username of the User.
     */
    public abstract function username();

    /**
     * Get the hashed password of the User.
     * 
     * @return string The hashed password of the User.
     */
    public function password() {
        return $this->password;
    }

    /**
     * Get whether the User must change their password before being allowed to
     * make any further API requests.
     * 
     * @return bool Whether the User is allowed to make API requests other than
     *   a password change request.
     */
    public function passwordResetRequired() {
        return $this->passwordResetRequired;
    }
}
