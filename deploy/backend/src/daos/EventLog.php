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

    public const LOGIN_EVENT = "login";
    public const MESSAGE_EVENT = "message";
    public const DATA_CREATED_EVENT = "data-created";
    public const DATA_UPDATED_EVENT = "data-updated";
    public const DATA_DELETED_EVENT = "data-deleted";
    public const CONNECTION_EVENT = "connection";

    public const INFO_LEVEL = "info";
    public const WARN_LEVEL = "warn";
    public const CRITICAL_LEVEL = "critical";

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
