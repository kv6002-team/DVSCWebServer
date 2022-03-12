<?php
namespace database;

/**
 * The data source for a given value for each primary object ID.
 * 
 * @see Database
 * @author William Taylor (19009576)
 */
interface Value {
    /**
     * Get the value to pass to the class constructor for the given primary
     * object ID.
     */
    public function getFor($id);
}
