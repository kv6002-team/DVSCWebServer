<?php
namespace email;

class Email {
  protected $recipient_email;
  protected $recipient_name;
  protected $subject;
  protected $content;

  public function __construct($recipient_email, $recipient_name, $content, $subject = null){
    if(!is_null($subject)) $this->subject = $subject; 
    else $this->subject = DEFAULT_SUBJECT;
    $this->recipient_email = $recipient_email;
    $this->recipient_name = $recipient_name;
    $this->content = $content;
  }
}
?>