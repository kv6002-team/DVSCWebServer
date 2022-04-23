<?php
/**
 * @author Scott Donaldson
 */
namespace email;

use SendGrid\Mail\Mail;

require_once 'sendgrid-api/sendgrid-php.php';
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
 * Sends an email to a set recipient that contains contact us submission form content
 * $content expects Array(
 *                        "email_address" => "test@example.com",
 *                        "phone_number" => "1234567890",
 *                        "email_subject" => "Hello, I need help with XYZ"
 *                        "message_content" => "I need some assistance with a big problem im having, send your best looking man please"
 *                        )
 */
function send_contact_form_submission_email($recipient_email, $content){
  $email_subject = CONTACT_US_CONFIG['subject'] . " " . $content['email_subject'];
  $phone_number = $content['phone_number'];
  $email_address = $content['email_address'];
  $email_content = $content['message_content'];

  $email_content .= "\n" . "Phone Number: $phone_number";
  $email_content .= "\n" . "Email Address: $email_address";

  $email_object = generate_email_object($recipient_email, EMAIL_SENDER_NAME, $email_content, $email_subject, false);
  send_email($email_object);
}

function send_password_reset_email($recipient_email, $recipient_name, $password_reset_link){
  $subject = COMPANY_NAME_SHORT . " | Password Reset";

  $content = "<h1>Password Reset</h1>";
  $content .= "<span>If you did not request this password reset, please ignore it.</span>";
  $content .= "<a href='$password_reset_link'></a>";

  $email_object = generate_email_object($recipient_email, $recipient_name, $content, $subject, false);
  send_email($email_object);
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
    $attachment = get_attachment();
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
function get_attachment(){
  $encoded_attatchment = base64_encode(file_get_contents(__DIR__ . '/attachments/Checklist.docx'));
  return Array(
    "encoded_file" => $encoded_attatchment,
    "type" => "application/word",
    "file_name" => "Checklist.docx"
  );
}

function test_email(){
  send_emails(Array(TEST_DATA));
}
?>