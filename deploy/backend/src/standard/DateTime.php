<?php
namespace kv6002\standard;

class DateTime {
    public static function parse($string) {
        return new \DateTimeImmutable($string);
    }

    public const FORMAT = "Y-m-d H:i:s";
    public static function format($datetime) {
        return $datetime->format(DateTime::FORMAT);
    }
}
