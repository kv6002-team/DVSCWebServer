<?php
namespace kv6002\validators;

use router\exceptions\HTTPError;
use kv6002\standard\DateTime;

use kv6002\domain;

class Instrument {

    public function validateInstrumentID($id) {
        if(!is_numeric($id) || str_contains($value, ".")){
            throw new HTTPError(422,
                "id is not a valid instrument ID"
            );
        }
        return $id;
    }

    public function validateInstrumentName($name) {
        !preg_match(
            "/^[A-Za-z0-9]+$/",
            $name
        )) {
            throw new HTTPError(422,
                "name is not a valid Instrument Name"
            );
        }
        return $name;
    }

    public function validateSerialNumber($serialNumber) {
        !preg_match(
            "/^[A-Za-z0-9]+$/",
            $serialNumber
        )) {
            throw new HTTPError(422,
                "serialNumber is not a valid Serial Number"
            );
        }
        return $serialNumber;
    }

    public function validateOfficialCheckExpiryDate($officialCheckExpiryDate) {
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
        return $officialCheckExpiryDate;
    }

    public function validateOurCheckDate($ourCheckDate) {
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
        return $ourCheckDate;
    }

    public function validateOurCheckStatus($ourCheckStatus) {
        try {
            return domain\Instrument::parseOurCheckStatus($ourCheckStatus);
        } catch (\InvalidArgumentException $e) {
            throw new HTTPError(422, 
                "Must provide a valid Check Status"
            );
        }
    }
}