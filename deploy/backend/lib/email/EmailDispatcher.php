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
    
  }

}
?>