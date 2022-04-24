<?php
namespace email;

class Email {
  private $recipient_email;
  private $recipient_name;
  private $subject;
  private $content;
  private $include_attachment;

  public function __construct(
      $recipient_email,
      $recipient_name,
      $content,
      $subject = null,
      $include_attachment = false
  ) {
    $this->recipient_email = $recipient_email;
    $this->recipient_name = $recipient_name;
    $this->content = $content;
    $this->subject = $subject !== null ? $subject : DEFAULT_SUBJECT;
    $this->include_attachment = $include_attachment;
  }

  public function send() {
    $email = Array(
      "recipient_email" => $this->recipient_email,
      "recipient_name" => $this->recipient_name,
      "subject" => $this->subject,
      "content" => $this->content,
      "include_attachment" => $this->include_attachment
    );
    $email_conn = new EmailConnection();
    $email_conn->send_email($email);
  }
}
