<?php
namespace kv6002\daos;

use kv6002\domain;

/**
 * Allows retrieving garage consultants.
 * 
 * @author William Taylor (19009576)
 */
class GarageConsultants {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Return a Garage Consultant object for the consultant in the database with
     * the given ID.
     * 
     * @param int $id The ID of the consultant to fetch.
     * @return GarageConsultant A GarageConsultant object for that consultant.
     */
    public function getConsultant($id) {
        // Fetch authors
        $authors = $this->db->fetchAll(
            "SELECT DISTINCT GarageConsultant.id as id,"
            ."   emailAddress,"
            ."   password,"
            ."   passwordResetRequired"
            ." FROM GarageConsultant"
            ." JOIN User ON GarageConsultant.id = User.id"
            ." WHERE id = :id"
            ." ORDER BY id",
            ["id" => $id],
            domain\GarageConsultant::class,
            null,
            [
                new Field("emailAddress"),
                new Field("password"),
                new Field("passwordResetRequired")
            ]
        );
        $authorsByPaper = Database::groupByFKey($authors, "paper_id");
    }
}
