<?php
/**
 * @author Scott Donaldson
 */
namespace email;

function generate_email_object(
  $recipient_email, 
  $recipient_name,
  $email_content,
  $email_subject = null,
  $include_attachment = true
  ){
    return Array(
      "recipient_email" => $recipient_email, 
      "recipient_name" => $recipient_name,
      "email_content" => $email_content,
      "email_subject" => $email_subject,
      "include_attachment" => $include_attachment
    );
}
?>