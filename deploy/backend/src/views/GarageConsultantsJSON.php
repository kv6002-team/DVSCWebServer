<?php
namespace kv6002\views;

/**
 * The data view for GarageConsultant objects.
 * 
 * @author William Taylor (19009576)
 */
class GarageConsultantsJSON {
    /**
     * Return the schema for a single Garage Consultant in this view as a
     * JSON-encodable array.
     * 
     * @return array The JSON-encodable schema for a single Paper.
     */
    public function garageConsultantSchema() {
        return [
            "id" => "(integer) The garage consultant's ID",
            "emailAddress" => "(string) The garage consultant's email address"
        ];
    }

    /**
     * Returns the JSON view of the given Garage Consultant as a JSON-encodable
     * array.
     * 
     * @param GarageConsultant The Garage Consultant to return the JSON view
     *   for.
     * @return array The JSON-encodable view of the given Garage Consultant.
     */
    public function garageConsultant($garageConsultant) {
        return  [
            "id" => $garageConsultant->id(),
            "emailAddress" => $garageConsultant->emailAddress()
        ];
    }

    /**
     * Return the JSON view of the given array of Garage Consultants as a JSON-
     * encodable array.
     * 
     * @param array<GarageConsultant> The garage consultants to return the JSON
     *   view for.
     * @return array The JSON-encodable view of the given Garage Consultants.
     */
    public function garageConsultants($garageConsultants) {
        return array_map([$this, "garageConsultant"], $garageConsultants);
    }
}
