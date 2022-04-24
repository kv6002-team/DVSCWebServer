<?php
/**
 * @author Scott Donaldson
 */
namespace email;

require_once __DIR__ . "/sendgrid-api/sendgrid-php.php";
use SendGrid\Mail\Mail;

class EmailConnection {
  public function send_email($email_array) {
    $email_manager = new \SendGrid(SENDGRID_API_KEY);
    $email = $this->generate_email_object($email_array);

    try {
      $email_manager->send($email);
    } catch (\Exception $ex) {
      throw new \Exception($ex->getMessage());
    }
  }

  private function generate_email_object($email_array) {
    $email = new Mail();
    $email->setFrom(EMAIL_SENDER, EMAIL_SENDER_NAME);
    $email->setSubject($email_array['subject']);
    $email->addTo($email_array['recipient_email'], $email_array['recipient_name']);
    $email->addContent("text/html", $email_array['content']);

    if ($email_array['include_attachment']) $this->add_attachment($email);

    return $email;
  }

  private function add_attachment($email) {
    $attachment = $this->get_attachment("application/word", "Checklist.docx");
    $email->addAttachment(
      $attachment['encoded_file'],
      $attachment['type'],
      $attachment['file_name'],
      "attachment"
    );
  }

  private function get_attachment($type, $name) {
    $encoded = base64_encode(file_get_contents(__DIR__ . "/attachments/$name"));
    return Array(
      "encoded_file" => $encoded,
      "type" => $type,
      "file_name" => $name
    );
  }
}
