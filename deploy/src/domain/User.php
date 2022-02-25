<?php
namespace kv6002\domain;

use database\Bindable;

/**
 * A user.
 * 
 * @author William Taylor (19009576)
 */
final class User {
    use Bindable;

    private $id;

    private $username;
    private $password;

    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Get the ID of the user.
     * 
     * @return int The ID of the user.
     */
    public function id() {
        return $this->id;
    }

    /**
     * Get the username of the user.
     * 
     * @return string The username of the user.
     */
    public function username() {
        return $this->username;
    }

    /**
     * Get the hashed password of the user.
     * 
     * @return string The hashed password of the user.
     */
    public function password() {
        return $this->password;
    }
}