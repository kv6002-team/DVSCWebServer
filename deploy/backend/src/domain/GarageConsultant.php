<?php
namespace kv6002\domain;

use database\Bindable;
use kv6002\domain\User;

/**
 * A garage.
 * 
 * @author William Taylor (19009576)
 */
final class GarageConsultant extends User {
    const USER_TYPE = "garage-consultant";

    private $emailAddress;

    /**
     * Create a new garage consultant.
     * 
     * @param string $emailAddress The email address for this consultant.
     * 
     * @param string $password The Garage Consultant's hashed password.
     * @param bool $passwordResetRequired Whether the Garage Consultant must
     *   reset their password before being allowed to make any further API
     *   requests.
     */
    public function __construct(
            $emailAddress,

            $password,
            $passwordResetRequired
    ) {
        parent::__construct($password, $passwordResetRequired);
        $this->type = self::USER_TYPE;

        $this->emailAddress = $emailAddress;
    }

    /* Implement User
    -------------------- */

    /**
     * Get the email address for this Garage Consultant.
     * 
     * @return string The email address for this Garage Consultant
     */
    public function emailAddress() {
        return $this->emailAddress;
    }

    /**
     * The username for this Garage Consultant, which is the email address.
     * 
     * @return string The email address for this Garage Consultant.
     */
    public function username() {
        return $this->emailAddress;
    }
}
