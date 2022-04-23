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

  public function __construct($dispatcher_objects){
    $this->emails_list = [];
    foreach($dispatcher_objects as $garage_info){
      $email_content = generate_email_content($garage_info['recipient_name'],$garage_info['garage_instruments']);
      $email_object = generate_email_object(
        $garage_info['recipient_email'], 
        $garage_info['recipient_name'],
        $email_content
      );
      array_push($this->emails_list, $email_object);
    }
  }

  public function send_test_data(){
    test_email();
  }

  public function send_emails(){
    send_emails($this->emails_list);
  }
}
?>