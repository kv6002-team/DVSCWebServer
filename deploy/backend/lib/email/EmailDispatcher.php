<?php
/**
 * @author Scott Donaldson
 */

namespace email;

require_once "EmailConfig.php";

class EmailDispatcher {
  protected $emails_list;

  public function __construct($emails_list) {
    $this->emails_list = $emails_list;
  }

  public function add_email($email) {
    array_push($this->emails_list, $email);
  }

  public function send_emails() {
    foreach ($this->emails_list as $email) {
      $email->send();
    }
  }

  public static function send_reset_email($recipient_email, $recipient_name, $reset_link) {
    $subject = COMPANY_NAME_SHORT . " | Password Reset";

    $content = (
      "<h1>Password Reset</h1>"
      ."<p>If you did not request this password reset, please ignore it.</p>"
      ."Click here to change your password for DVSC: <a href='$reset_link'>$reset_link</a>"
    );

    $email = new Email(
      $recipient_email,
      $recipient_name,
      $content,
      $subject,
      false
    );
    $email->send();
  }

  public static function send_contactus_email($recipient_email = null, $contact_message) {
    $contact_message = $contact_message->as_array();

    if (is_null($recipient_email)) $recipient_email = CONTACT_US_CONFIG['fallback_recipient'];

    $email_subject = CONTACT_US_CONFIG['subject'] . " - " . $contact_message['email_subject'];

    // Nobody likes emails of portal messages that don't keep newlines
    $paragraphs = explode("\n\n", // double-newline to only split paragraphs
      htmlspecialchars($contact_message['message_content'])
    );
    $message = implode("\n", array_map(
      function ($paragraph) { return "<p>$paragraph</p>"; },
      $paragraphs
    ));
    $email_content = implode("\n", [
      "<p><strong>Message</strong>:</p>"
      ."<div style=\"padding-left: 10px\">"
      .$message
      ."</div>"
      ."<p><strong>Phone Number</strong>: " . $contact_message['phone_number'] . "</p>"
      ."<p><strong>Email Address</strong>: " . $contact_message['email_address'] . "</p>"
    ]);

    $email = new Email(
      $recipient_email,
      EMAIL_SENDER_NAME,
      $email_content,
      $email_subject,
      false
    );

    $email->send();
  }

  public function send_unique_email($email){
    $email->send();
  }
}
