<?php
namespace kf6012\domain;

use database\Bindable;

/**
 * A garage.
 * 
 * @author William Taylor (19009576)
 */
final class GarageConsultant {
    use Bindable;

    private $id;

    private $user;

    /**
     * Create a new garage consultant.
     * 
     * @param User $user The User account for this consultant.
     */
    public function __construct($user) {
        $this->id = null;
        $this->user = $user;
    }

    /**
     * Get the ID of the Garage Consultant.
     * 
     * @return int The ID of the Garage Consultant.
     */
    public function id() {
        return $this->id;
    }

    /**
     * Get the User for this Garage Consultant.
     * 
     * @return User The User for this Garage Consultant.
     */
    public function user() {
        return $this->user;
    }
}
