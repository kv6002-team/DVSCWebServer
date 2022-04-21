<?php
namespace Email;
define('SENDGRID_API_KEY', "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");
define('EMAIL_SENDER', "no-reply@dvsc.services");
define('EMAIL_SENDER_NAME', "Kevin Donaldson");
define('DEFAULT_SUBJECT', "Monthly Garage Report : " . date('MY'));
define('TEST_DATA', Array(
  "recipient_email" => "scott-donaldson@outlook.com",
  "recipient_name" => "Generic Garage Name", 
  "email_content"=>"<strong>This is a test, Hello World</strong>",
  "email_subject" => null,
  "include_attachment" => true
))
?>