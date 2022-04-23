<?php
namespace email;

class Email {
  protected $recipient_email;
  protected $recipient_name;
  protected $subject;
  protected $content;
  protected $include_attachment;

  public function __construct($recipient_email, $recipient_name, $content, $subject = null, $include_attachment = false){
    if(!is_null($subject)) $this->subject = $subject; 
    else $this->subject = DEFAULT_SUBJECT;
    $this->recipient_email = $recipient_email;
    $this->recipient_name = $recipient_name;
    $this->content = $content;
    $this->include_attachment = $include_attachment;
  }

  public function send(){
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
?>