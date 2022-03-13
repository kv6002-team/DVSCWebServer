<?php
namespace kf6012\domain;

use database\Bindable;
use kf6012\domain\User;

/**
 * A garage.
 * 
 * @author William Taylor (19009576)
 */
final class Garage extends User {
    private $vts;
    private $name;
    private $ownerName;
    private $emailAddress;
    private $telephoneNumber;
    private $paidUntil;

    /**
     * Create a new garage.
     * 
     * @param string $vts The vehicle testing station (VTS) number for the
     *   Garage.
     * @param string $name The name of the Garage.
     * @param string $ownerName The name of the owner of the Garage.
     * @param string $emailAddress The email for the Garage. This is the
     *   address used to send reminder emails to, among other things.
     * @param string $telephoneNumber The telephone number for the Garage.
     * @param DateTime $paidUntil The date up to which the Garage has paid. If
     *   this is before now, then payment is overdue.
     * 
     * @param string $password The Garage's hashed password.
     * @param bool $passwordResetRequired Whether the Garage must reset their
     *   password before being allowed to make any further API requests.
     */
    public function __construct(
            $vts,
            $name,
            $ownerName,
            $emailAddress,
            $telephoneNumber,
            $paidUntil,

            $password,
            $passwordResetRequired
    ) {
        parent::__construct($password, $passwordResetRequired);

        $this->vts = $vts;
        $this->name = $name;
        $this->ownerName = $ownerName;
        $this->emailAddress = $emailAddress;
        $this->telephoneNumber = $telephoneNumber;
        $this->paidUntil = $paidUntil;
    }

    /**
     * Get the vehicle testing station (VTS) number for this Garage.
     * 
     * @return string The vehicle testing station (VTS) number for this Garage.
     */
    public function vts() {
        return $this->vts;
    }

    /**
     * Get the name of this Garage.
     * 
     * @return string The name of this Garage.
     */
    public function name() {
        return $this->name;
    }

    /**
     * Get the name of the owner of this Garage.
     * 
     * @return string The name of the owner of this Garage.
     */
    public function ownerName() {
        return $this->ownerName;
    }

    /**
     * The email for this Garage.
     * 
     * This is the address used to send reminder emails to, among other things.
     * 
     * @return string The email for this Garage.
     */
    public function emailAddress() {
        return $this->emailAddress;
    }

    /**
     * Get the telephone number for this Garage.
     * 
     * @return string The telephone number for this Garage.
     */
    public function telephoneNumber() {
        return $this->telephoneNumber;
    }

    /**
     * Get the date up to which the Garage has paid.
     * 
     * If this is before now, then payment is overdue.
     * 
     * @return DateTime The date up to which the Garage has paid.
     */
    public function paidUntil() {
        return $this->paidUntil;
    }
    
    /* Implement User
    -------------------- */
    
    public function username() {
        return $this->vts;
    }
}
