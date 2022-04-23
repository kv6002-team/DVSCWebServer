<?php
namespace kv6002\validators;

use router\exceptions\HTTPError;
use kv6002\standard\DateTime;

class Garage {

    public function validate(
            $vts,
            $name,
            $ownerName,
            $emailAddress,
            $telephoneNumber,
            $paidUntil
    ) {
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

        if (str_contains($vts, ":")) {
            throw new HTTPError(422,
                "VTS number must not contain a colon"
            );
        }

        return [
            $vts,
            $name,
            $ownerName,
            $emailAddress,
            $telephoneNumber,
            $paidUntil
        ];
    }
}