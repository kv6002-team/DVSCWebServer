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
            $instrumentData["officialCheckExpiryDate"] = DateTime::parse(
                $instrumentData["officialCheckExpiryDate"]
            );
        } catch (Exception $e) {
            throw new HTTPError(422,
                "Must provide officialCheckExpiryDate in a correct format"
                ."(eg. YYYY-MM-DD HH:MM:SS)"
            );
        }

        if ($instrumentData["officialCheckExpiryDate"] < new DateTime('today midnight')) {
            throw new HTTPError(422,
                "Must provide a date from tomorrow for officialCheckExpiryDate"
            );
        }

        try {
            $instrumentData["ourCheckDate"] = DateTime::parse(
                $instrumentData["ourCheckDate"]
            );
        } catch (Exception $e) {
            throw new HTTPError(422,
                "Must provide ourCheckDate in a correct format"
                ."(eg. YYYY-MM-DD HH:MM:SS)"
            );
        }

        if ($instrumentData["ourCheckDate"] < new DateTime('yesterday midnight')) {
            throw new HTTPError(422,
                "Must provide a date from today for ourCheckDate"
            );
        }
    }

    return [
        $name,
        $officialCheckExpiryDate,
        $ourCheckStatus,
        $ourCheckDate    
    ]
}