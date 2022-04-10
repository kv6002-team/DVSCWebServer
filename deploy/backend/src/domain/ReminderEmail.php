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
final class ReminderEmail {
    use Bindable;

    private $id;

    private $content;
    private $sendDate;

    /**
     * Create a new reminder email.
     * 
     * Reminder emails are sent to Garages to remind them to book and carry out
     * checks for Instruments. They are automatically scheduled for sending
     * after they are manually approved, and are automatically sent at the
     * scheduled time.
     * 
     * @param string $content The content of the email.
     * @param DateTime $sendDate The date the email is to be sent (or was sent),
     *   or null if the email has not yet been approved.
     */
    public function __construct(
            $content,
            $sendDate,
    ) {
        $this->content = $content;
        $this->sendDate = $sendDate;
    }

    /**
     * Get the ID of the Reminder Email.
     * 
     * @return int The ID of the Reminder Email.
     */
    public function id() {
        return intval($this->id);
    }

    /**
     * Get the content of this Reminder Email.
     * 
     * @return string The content of this Reminder Email.
     */
    public function content() {
        return $this->content;
    }

    /**
     * Get the send date of this Reminder Email.
     * 
     * @return DateTime The send date of this Reminder Email.
     */
    public function sendDate() {
        return $this->sendDate;
    }

    /**
     * Return true if the Reminder Email has been approved, or false otherwise.
     * 
     * @return bool Whether the Reminder Email has been approved.
     */
    public function isApproved() {
        return $this->sendDate !== null;
    }
}
