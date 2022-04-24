<?php
namespace kv6002\validators;

use router\exceptions\HTTPError;
use kv6002\standard\DateTime;

class Garage {

    public function validateGarageID($id) {
        if(!is_numeric($id) || str_contains($id, ".")){
            throw new HTTPError(422,
                "id is not a valid garage ID"
            );
        }
        return $id;
    }

    public function validateVTS($vts) {
        // This also rules out possibility of a colon being in it.
        !preg_match(
            "/^[A-Za-z0-9]+$/",
            $vts
        )) {
            throw new HTTPError(422,
                "vts is not a valid VTS"
            );
        }
        return $vts;
    }

    public function validateGarageName($name) {
        !preg_match(
            "/^[A-Za-z0-9]+$/",
            $name
        )) {
            throw new HTTPError(422,
                "name is not a valid garage name"
            );
        }
        return $name;
    }

    public function validateOwnerName($ownerName) {
        !preg_match(
            "/^[A-Za-z0-9]+$/",
            $ownerName
        )) {
            throw new HTTPError(422,
                "ownerName is not a valid owner name"
            );
        }
        return $ownerName;  
    }

    public function validateEmailAddress($emailAddress) {
        if (!preg_match(
            "/^"                   // From start of string
            ."(?=.{1,128}@)"       // Before @ must be 1-128 chars
            ."[A-Za-z0-9_-]+"      // First '.'-delimited segment
            ."(\.[A-Za-z0-9_-]+)*" // Other '.'-delimited segments
            ."@"                   // @ symbol
            ."(?=.{1,128})"        // After @ must be 1-128 chars
            ."[A-Za-z0-9]"         // First char of domain name
            ."[A-Za-z0-9-]*"       // Bottom level domain name
            ."(\.[A-Za-z0-9-]+)*"  // Intermediate domain names
            ."(\.[A-Za-z]{2,})"    // Top level domain name (TLD)
            ."$/"                  // To end of string
            ."u",                  // FLAGS: Use Unicode matching
            $emailAddress
        )) {
            throw new HTTPError(422,
                "emailAddress is not a valid email address"
            );
        }
    }

    public function validateTelephoneNumber($telephoneNumber) {
        if(!is_numeric($telephoneNumber){
            throw new HTTPError(422,
                "telephoneNumber is not a valid telephone number"
            );
        }
        return $telephoneNumber;     
    }

    public function validatePaidUntil($paidUntil) {
        try {
            $paidUntil = DateTime::parse(
                $paidUntil
            );
        } catch (Exception $e) {
            throw new HTTPError(422,
                "Must provide paidUntil in a correct format (eg."
                ." YYYY-MM-DD HH:MM:SS)"
            );
        }
        return $paidUntil;
    }
}