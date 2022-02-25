<?php
namespace database;

/**
 * The data source for a constant value for every primary object ID.
 * 
 * When used as a constructor argument specification in one of the Database
 * functions, this will allow you to pass a constant value to the class
 * constructor for every record made.
 * 
 * This is commonly used to construct a single object (using Database::fetch())
 * that requires arrays of dynamic values from previous SQL fetches. It can also
 * be used to add constant values to the constructor call.
 * 
 * @see Database
 * @author William Taylor (19009576)
 */
class ConstValue implements Value {
    private $value;

    /**
     * Create a constant value.
     * 
     * @param mixed $value The value to pass to the class constructor.
     */
    public function __construct($value) {
        $this->value = $value;
    }

    /* Implement Value
    -------------------------------------------------- */

    /**
     * Get the actual constructor parameter. $id is ignored for ConstValues.
     */
    public function getFor($id) {
        return $this->value;
    }
}
