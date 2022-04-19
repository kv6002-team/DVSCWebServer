<?php
namespace kv6002\daos;

use kv6002\daos\exceptions\UnsupportedUserTypeError;

use kv6002\domain;
use kv6002\daos;

/**
 * Allows adding events to the event log.
 * 
 * @author William Taylor (19009576)
 */
class EventLog {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Add an event to the database.
     * 
     * @param string $type The type of event.
     * @param string $level The importance level of the event.
     * @param string $message The detail for this event.
     * @param DateTime $timestamp The time this event happened.
     */
    public function add($type, $level, $message, $timestamp) {
        $this->db->execute(
            "INSERT INTO EventLog (type, level, message, timestamp)"
            ." VALUES ("
            ."   :type,"
            ."   :level,"
            ."   :message,"
            ."   :timestamp,"
            ." )",
            [
                "type" => $type,
                "level" => $level,
                "message" => $message,
                "timestamp" => standard\DateTime::format($timestamp)
            ]
        );
        $this->db->execute("COMMIT");
    }
}
