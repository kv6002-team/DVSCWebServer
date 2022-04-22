<?php
/**
 * @author Scott Donaldson
 */
namespace Email;

function generate_dispatcher_object(
  $recipient_email,
  $recipient_name,
  $garage_instruments // Array(KEYS: instrument_name,instrument_serial_number,instrument_expiry_date
){
  return Array(
    "recipient_email" => $recipient_email,
    "recipient_name" => $recipient_name,
    "garage_instruments" => $garage_instruments
  );
}
?>
