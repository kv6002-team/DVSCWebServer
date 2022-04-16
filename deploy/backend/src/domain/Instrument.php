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
    private $officialCheckExpiryDate;
    private $ourCheckStatus;
    private $ourCheckDate;

    // Valid ourCheckStatus values
    public const CHECKED_SATISFACTORY = 'checked_satisfactory';
    public const CHECKED_UNSATISFACTORY = 'checked_unsatisfactory';
    public const UNCHECKED = 'unchecked';

    public static function parseOurCheckStatus($ourCheckStatus) {
        switch ($ourCheckStatus) {
            case Instrument::CHECKED_SATISFACTORY:
            case Instrument::CHECKED_UNSATISFACTORY:
            case Instrument::UNCHECKED:
                return $ourCheckStatus;

            default:
                throw new \InvalidArgumentException(
                    "Invalid check status: '$ourCheckStatus'"
                );
        }
    }

    /**
     * Create a new instrument.
     * 
     * @param string $name The Instrument's descriptive name. This should be
     *   explanatory enough that the garage owner can uniquely identify each of
     *   their devices by name.
     * @param string $serialNumber The Instrument's serial number.
     * @param DateTime $officialCheckExpiryDate The date by which the Instrument
     *   must be officially checked again.
     * @param string $ourCheckStatus The status of the Instrument's last
     *   unofficial check. One of 'checked_satisfactory',
     *   'checked_unsatisfactory', or 'unchecked'.
     * @param DateTime $ourCheckDate The date the Instrument was last
     *   unofficially checked.
     */
    public function __construct(
            $name,
            $serialNumber,
            $officialCheckExpiryDate,
            $ourCheckStatus,
            $ourCheckDate
    ) {
        $this->name = $name;
        $this->serialNumber = $serialNumber;
        $this->officialCheckExpiryDate = $officialCheckExpiryDate;
        $this->ourCheckStatus = self::parseOurCheckStatus($ourCheckStatus);
        $this->ourCheckDate = $ourCheckDate;
    }

    /**
     * Get the ID of the Instrument.
     * 
     * @return int The ID of the Instrument.
     */
    public function id() {
        return intval($this->id);
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
     * Get the date that the next official check must be done by.
     * 
     * @return DateTime The date that the next official check must be done by.
     */
    public function officialCheckExpiryDate() {
        return $this->officialCheckExpiryDate;
    }

    /**
     * Get the status of the unofficial check.
     * 
     * Will be one of:
     * - 'checked_satisfactory' - The unofficial check has been carried out, and
     *   the instrument was deemed in satisfactory condition.
     * - 'checked_unsatisfactory' - The unofficial check has been carried out,
     *   and the instrument was not deemed in satisfactory condition.
     * - 'unchecked' - The unnoficial check has not yet been carried out.
     * 
     * @return string The status of the unofficial check.
     */
    public function ourCheckStatus() {
        return $this->ourCheckStatus;
    }
    
    /**
     * Get the date that the last unofficial check was carried out.
     * 
     * @return DateTime The date that the last unofficial check was carried out.
     */
    public function ourCheckDate() {
        return $this->ourCheckDate;
    }
}
