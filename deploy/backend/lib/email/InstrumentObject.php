<?php
/**
 * @author Scott Donaldson
 */
namespace Email;

function generate_instrument_object(
  $instrument_name,
  $instrument_serial_number,
  $instrument_expiry_date
){
  return Array(
    "instrument_name" => $instrument_name,
    "instrument_serial_number" => $instrument_serial_number,
    "instrument_expiry_date" => $instrument_expiry_date
  );
}
?>