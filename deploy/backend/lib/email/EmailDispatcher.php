<?php
/**
 * @author Scott Donaldson
 */
namespace email;
include_once 'EmailContent.php';
include_once 'EmailObject.php';
include_once 'Email.php';

class EmailDispatcher {
  protected $emails_list;

  public function __construct($emails_list){
    $this->emails_list = $emails_list;
  }

  public function add_email($email){
    array_push($this->emails_list, $email);
  }

  public function send_emails(){
    foreach($this->emails_list as $email){
      $email->send();
    }
  }

  public function send_reset_email($recipient_email, $recipient_name, $reset_link){
    $subject = COMPANY_NAME_SHORT . " | Password Reset";

    $content = "<h1>Password Reset</h1>";
    $content .= "<span>If you did not request this password reset, please ignore it.</span>";
    $content .= "<a href='$reset_link'></a>";
  
    $email = new Email($recipient_email, $recipient_name, $content, $subject, false);
    $email->send();
  }

  public function send_contactus_email($recipient_email = null, $contact_form){

    $contact_form = $contact_form->get_details();

    if(is_null($recipient_email)) $recipient_email = CONTACT_US_CONFIG['fallback_recipient'];

    $email_subject = CONTACT_US_CONFIG['subject'] . " " . $contact_form['email_subject'];

    $email_content  = $contact_form['message_content'];
    $email_content .= "\n" . "Phone Number : " . $contact_form['phone_number'];
    $email_content .= "\n" . "Email Address : " . $contact_form['email_address'];
    
    $email = new Email(
      $recipient_email,
      EMAIL_SENDER_NAME,
      $email_content,
      $email_subject,
      false
    );

    $email->send();
  }

  public function send_unique_email(){

  }

}
?>