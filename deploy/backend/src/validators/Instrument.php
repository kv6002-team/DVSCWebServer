<?php
namespace kv6002\validators;

use router\exceptions\HTTPError;
use kv6002\standard\DateTime;

class Instrument {

    public function validate(
        $name,
        $officialCheckExpiryDate,
        $ourCheckStatus,
        $ourCheckDate
    ) {
        try {
            $officialCheckExpiryDate = DateTime::parse(
                $officialCheckExpiryDate
            );
        } catch (Exception $e) {
            throw new HTTPError(422,
                "Must provide officialCheckExpiryDate in a correct format"
                ."(eg. YYYY-MM-DD HH:MM:SS)"
            );
        }

        if ($officialCheckExpiryDate < new \DateTimeImmutable('tomorrow')) {
            throw new HTTPError(422,
                "Must provide a date from tomorrow for officialCheckExpiryDate"
            );
        }

        try {
            $ourCheckDate = DateTime::parse(
                $ourCheckDate
            );
        } catch (Exception $e) {
            throw new HTTPError(422,
                "Must provide ourCheckDate in a correct format"
                ."(eg. YYYY-MM-DD HH:MM:SS)"
            );
        }

        if ($ourCheckDate < new \DateTimeImmutable('tomorrow + 1 day')) {
            throw new HTTPError(422,
                "Must provide a date from today for ourCheckDate"
            );
        }
        return [
            "name" => $name,
            "officialCheckExpiryDate" => $officialCheckExpiryDate,
            "ourCheckStatus" => $ourCheckStatus,
            "ourCheckDate" => $ourCheckDate    
        ];

    }
}