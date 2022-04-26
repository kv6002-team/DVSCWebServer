<?php
namespace email;

class ContactMessage {
  private $email_address;
  private $phone_number;
  private $email_subject;
  private $message_content;

  public function __construct(
    $email_address,
    $phone_number,
    $email_subject,
    $message_content
  ) {
    $this->email_address = $email_address;
    $this->phone_number = $phone_number;
    $this->email_subject = $email_subject;
    $this->message_content = $message_content;
  }

  public function as_array() {
    return Array(
      "email_address" => $this->email_address,
      "phone_number" => $this->phone_number,
      "email_subject" => $this->email_subject,
      "message_content" => $this->message_content
    );
  }
}
