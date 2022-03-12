<?php
namespace database;

/**
 * The data source for a specified value for each primary object ID.
 * 
 * When used as a constructor argument specification in one of the Database
 * functions, this will pass different values to the class constructor for each
 * record being made.
 * 
 * This is commonly used to construct multiple objects that require arrays of
 * dynamic values from previous SQL fetches, such as when fetching many objects
 * of a given type, each requiring an array of objects of a different type. This
 * is commonly needed when implementing an ORM layer for JOINed data.
 * 
 * @see Database
 * @author William Taylor (19009576)
 */
class RecordValue implements Value {
    private $values;
    private $default;

    /**
     * Construct a record-dependant value.
     * 
     * @param array<mixed,mixed> $values A mapping of [primary object ID =>
     *   value] where the value mapped from each primary object ID is used as
     *   the class constructor parameter.
     */
    public function __construct($values, $default = null) {
        $this->values = $values;
        $this->default = $default;
    }

    /* Implement Value
    -------------------------------------------------- */

    /**
     * Get the actual constructor parameter for the given primary object ID.
     * 
     * @param mixed $id The primary object ID for the object to construct.
     */
    public function getFor($id) {
        if (!array_key_exists($id, $this->values)) {
            return $this->default;
        }
        return $this->values[$id];
    }
}
