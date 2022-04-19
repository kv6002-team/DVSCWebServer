<?php

namespace email;

use SendGrid\Mail\Mail;

class EmailConnection {
  public function send_email($email_array){
    $email_manager = new \SendGrid(SENDGRID_API_KEY);
    $email = $this->generate_email_object($email_array);
    try{
      $email_manager->send($email);
    } catch (\Exception $ex){
      throw new \Exception($ex->getMessage());
    }
  }

  private function generate_email_object($email_array){
    $email = new Mail();
    $email->setFrom(EMAIL_SENDER, EMAIL_SENDER_NAME);
    $email->setSubject($email_array['subject']);
    $email->addTo($email_array['recipient_email'],$email_array['recipient_name']);
    $email->addContent("text/html",$email_array['content']);

    if($email_array['include_attachment']) $this->add_attachment($email);

    return $email;
  }

  private function add_attachment($email){
    $attachment = $this->get_attachment();

    $email->addAttachment(
      $attachment['encoded_file'],
      $attachment['type'],
      $attachment['file_name'],
      "attachment"
    );
  }

  private function get_attachment(){
    $encoded = base64_encode(file_get_contents(__DIR__ . 'attachments/Checklist.docx'));
    return Array(
      "encoded_file" => $encoded,
      "type" => "application/word",
      "file_name" => "Checklist.docx"
    );
  }


}
?>