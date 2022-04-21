<?php
namespace Email;

use SendGrid\Mail\Mail;

require_once './sendgrid-api/sendgrid-php.php';
require_once 'EmailConfig.php';

/**
 * Sends an email using the sendgrid api
 */
function send_email($email_object){
  $email = generate_email_obj($email_object);
  $email_manager = new \SendGrid(SENDGRID_API_KEY);

  try{
    $email_manager->send($email);
  } catch (\Exception $ex){
    throw new \Exception($ex->getMessage());
  }
}


/**
 * Generates the email object for the sendgrid email manager to send
 */
function generate_email_obj($email_object){

  if(is_null($email_object['email_subject'])) $email_object['email_subject'] = DEFAULT_SUBJECT;

  $email = new Mail();
  $email->setFrom(EMAIL_SENDER, EMAIL_SENDER_NAME);
  $email->setSubject($email_object['email_subject']);
  $email->addTo($email_object['recipient_email'], $email_object['recipient_name']);
  $email->addContent("text/html", $email_object['email_content']);

  if($email_object['include_attachment']){
    $attachment = getAttachment();
    $email->addAttachment(
      $attachment['encoded_file'],
      $attachment['type'],
      $attachment['file_name'],
      "attachment"
    );
  }
  return $email;
}

/**
 * Takes an array of Email objects to then pass to sendgrid and to convert into email objects.
 * Expects Array(
 *                 Array(
 *                        recipient_email => "test@example.com",
 *                        recipient_name => "GARAGE NAME", 
 *                        email_content="HTML BASED CONTENT",
 *                        email_subject => "" || null,
 *                        include_attachment => BOOLEAN
 *                )
 *          )
 */
function send_emails($email_objects){
  forEach($email_objects as $email_object){
    send_email($email_object);
  }
}

// Gets a local checklist file as an attatchment and returns the encoded file as an object.
function getAttachment(){
  $encoded_attatchment = base64_encode(file_get_contents('./attachments/Checklist.docx'));
  return Array(
    "encoded_file" => $encoded_attatchment,
    "type" => "application/word",
    "file_name" => "Checklist.docx"
  );
}

function test_emails(){
  send_emails(Array(TEST_DATA));
}
?>