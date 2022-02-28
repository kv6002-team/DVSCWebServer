<?php
namespace database;

/**
 * The data source for a value from the fetched record.
 * 
 * @see Database
 * @author William Taylor (19009576)
 */
class Field {
    private $name;

    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * Get the name of the field whose value in each record should be passed to
     * the class constructor.
     */
    public function name() {
        return $this->name;
    }
}
