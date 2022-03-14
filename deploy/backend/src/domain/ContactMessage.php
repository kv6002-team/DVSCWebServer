<?php
namespace kv6002\domain;

use database\Bindable;

/**
 * A contact message.
 * 
 * Contact messages are messages sent by Garages or anonymous website visitors
 * to a/the garage consultant for various reasons, eg. asking questions,
 * requesting privileged data changes, etc.
 * 
 * @author William Taylor (19009576)
 */
final class ContactMessage {
    use Bindable;

    private $id;

    private $content;
    private $sentDate;

    /**
     * Create a new contact message.
     * 
     * @param string $content The content of the message.
     * @param DateTime $sentDate The date the message was sent.
     */
    public function __construct(
            $content,
            $sentDate,
    ) {
        $this->content = $content;
        $this->sentDate = $sentDate;
    }

    /**
     * Get the ID of the Contact Message.
     * 
     * @return int The ID of the Contact Message.
     */
    public function id() {
        return $this->id;
    }

    /**
     * Get the content of this Contact Message.
     * 
     * @return string The content of this Contact Message.
     */
    public function content() {
        return $this->content;
    }

    /**
     * Get the date this Contact Message was sent.
     * 
     * @return DateTime The date this Contact Message was sent.
     */
    public function sentDate() {
        return $this->sentDate;
    }
}
