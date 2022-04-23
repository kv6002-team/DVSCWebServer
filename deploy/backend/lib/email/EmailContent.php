<?php
/**
 * @author Scott Donaldson
 */
namespace email;

include_once 'EmailConfig.php';


class EmailContent {
  protected $html_email_string;

  public function __construct($garage_name, $instruments_array = Array()){
    $this->html_email_string = $this->generate_email_content($garage_name, $instruments_array);
  }

  public function get_email_html_string(){
    return $this->html_email_string;
  }

  private function generate_email_content($garage_name, $instrument_array = Array()){
    $output = $this->get_email_header($garage_name);
    $output .- $this->generate_instrument_table($instrument_array);
    $output .- $this->get_email_footer();
  }

  private function get_email_header($garage_name){
    $company_name = COMPANY_NAME_LONG;
    $subject = DEFAULT_SUBJECT;
  
    $output = "<h1>$company_name<h1>";
    $output .="<h2>$garage_name $subject</h2>";
  
    return $output;
  }
  
  private function get_email_footer(){
    $company_name = COMPANY_NAME_SHORT;
    $name = EMAIL_SENDER_NAME;
  
    $output = "<span>- $name, $company_name</span>";
  
    return $output;
  }

  private function generate_instrument_table($instruments_array){
    $table_headings = Array('Instrument', "Serial Number", "Expiry Date");

    if(!$this->check_array_shape($table_headings, $instruments_array)){
      throw new \Exception("Instrument Detail Array does not match number of headings in table");
    }
    $output = "<table>" . $this->row($this->generate_headings($table_headings));
  
    foreach($instruments_array as $instrument_details){
      $output .= $this->generate_instrument_row($instrument_details);
    }
  
    $output .= "</table";
    return $output;
  }

  private function generate_headings($table_headings = Array()){
    $output = "";
    foreach($table_headings as $heading){
      $output .= "<th>$heading</th>";
    }
    return $output;
  }

  private function check_array_shape($headings_array, $instruments_array){
    $headings_length = count($headings_array);
    foreach($instruments_array as $instrument_details){
      if(count($instrument_details) > $headings_length) return false;
    }
    return true;
  }

  private function generate_instrument_row($instrument_details){
    if(array_key_exists('instrument_expiry_date', $instrument_details)){
      $instrument_details['instrument_expiry_date'] = $this->expiry_formatter($instrument_details['instrument_expiry_date']);
    }
    $output = "";
    foreach($instrument_details as $instrument_detail){
      $output .= "<td>$instrument_detail</td>";
    }
    return $this->row($output);
  }

  private function row($string){
    return "<tr>$string</tr>";
  }

  private function expiry_formatter($expiry_date){
    $one_month_future = date_add(new \DateTime(), date_interval_create_from_date_string("1 month"));
    $expiry_date_string = date_format($expiry_date,"d-m-Y");
    if($expiry_date < $one_month_future){
      return "<span style='color: red'>$expiry_date_string</span>";
    }else{
      return "<span>$expiry_date_string</span>";
    }
  }
}