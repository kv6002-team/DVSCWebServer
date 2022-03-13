<?php
namespace kv6002\domain;

use database\Bindable;

/**
 * An instrument (aka. device, tool, etc.) that a Garage uses to do vehicle
 * testing.
 * 
 * Instruments must be checked for functioning and correct callibration
 * occasionally, as per government requirements. These checks have variable
 * expiration dates set by the professional body after each check.
 * 
 * @author William Taylor (19009576)
 */
final class Instrument {
    use Bindable;

    private $id;

    private $name;
    private $serialNumber;
    private $checkExpiryDate;
    private $checkStatus;

    // Valid status values
    public const SATISFACTORY = 'satisfactory';
    public const UNSATISFACTORY = 'unsatisfactory';

    /**
     * Create a new instrument.
     * 
     * @param string $name The Instrument's descriptive name. This should be
     *   explanatory enough that the garage owner can uniquely identify each of
     *   their devices by name.
     * @param string $serialNumber The Instrument's serial number.
     * @param DateTime $checkExpiryDate The date the checkStatus becomes invalid
     *   (and so ignored).
     * @param string checkStatus The status of the instrument's last check.
     *   Either 'satisfactory' or 'unsatisfactory'.
     */
    public function __construct(
            $name,
            $serialNumber,
            $checkExpiryDate,
            $checkStatus
    ) {
        $this->name = $name;
        $this->serialNumber = $serialNumber;
        $this->checkExpiryDate = $checkExpiryDate;

        if (!self::isValidStatus($checkStatus)) {
            throw new InvalidArgumentException(
                "Invalid status given, must be either 'satisfactory' or "+
                "'unsatisfactory'"
            );
        }
        $this->checkStatus = $checkStatus;
    }

    /**
     * Get the ID of the Instrument.
     * 
     * @return int The ID of the Instrument.
     */
    public function id() {
        return $this->id;
    }

    /**
     * Get the descriptive name of this Instrument.
     * 
     * @return string The descriptive name of this Instrument.
     */
    public function name() {
        return $this->name;
    }

    /**
     * Get the serial number of this Instrument.
     * 
     * @return string The serial number of this Instrument.
     */
    public function serialNumber() {
        return $this->serialNumber;
    }

    /**
     * Get the date that the check status becomes invalid (and so after which
     *   the check status should be ignored and an 'unchecked' status be
     *   assumed).
     * 
     * @return DateTime The date that the check status becomes invalid.
     */
    public function checkExpiryDate() {
        return $this->checkExpiryDate;
    }

    /**
     * Get whether the User must change their password before being allowed to
     * make any further API requests.
     * 
     * @return bool Whether the User is allowed to make API requests other than
     *   a password change request.
     */
    public function isCheckSatisfactory() {
        return $this->checkStatus === self::SATISFACTORY;
    }
    
    /* Utils
    -------------------------------------------------- */

    /**
     * Check if the given string is a valid status.
     * 
     * @param string $status The status string to test.
     * @return bool True if $status is a valid status, false otherwise.
     */
    private static function isValidStatus($status) {
        return !in_array(
            $checkStatus,
            [
                self::SATISFACTORY,
                self::UNSATISFACTORY
            ],
            true
        );
    }
}
