<?php
/**
 * @author Scott Donaldson
 */
namespace Email;

include_once 'EmailConfig.php';

/**
 * Generates the HTML Email structure for a specific garage
 * returns html string to be used in an email content section
 */
function generate_email_content($garage_name, $instruments_array = Array()){
  $output = get_email_header($garage_name);
  $output .= generate_instrument_table($instruments_array);
  $output .+ get_email_footer();
}
/**
 * Expects: Array(
 *                  Array(
 *                    "instrument_name" => "Rolling Road",
 *                    "instrument_serial_number" => "123XYZ",
 *                    "instrument_expiry_date" => Immutable DateTime Object
 *                  )
 *                )
 */
function generate_instrument_table($instruments_array = Array()){
  $table_headings = Array('Instrument', "Serial Number", "Expiry Date");

  if(!check_instrument_array_shape($table_headings, $instruments_array)){
    throw new \Exception("Instrument Detail Array does not match number of headings in table");
  }
  $output = "<table>" . row(generate_headings($table_headings));

  foreach($instruments_array as $instrument_details){
    $output .= generate_instrument_row($instrument_details);
  }

  $output .= "</table";
  return $output;
}

/**
 * Takes an index array of and creates a html table headings string
 */
function generate_headings($table_headings = Array()){
  $output = "";
  foreach($table_headings as $heading){
    $output .= "<th>$heading</th>";
  }
  return $output;
}

/**
 * Takes an InstrumentObject and converts it into a table 
 * row, including colour for expiry date
 */
function generate_instrument_row($instrument_details = Array()){
  if(array_key_exists('instrument_expiry_date', $instrument_details)){
    $instrument_details['instrument_expiry_date'] = expiry_date_formatter($instrument_details['instrument_expiry_date']);
  }
  $output = "";
  foreach($instrument_details as $instrument_detail){
    $output .= "<td>$instrument_detail</td>";
  }
  return row($output);
}

/**
 * surrounds a string in html row tags
 */
function row($content){
  return "<tr>$content</tr>";
}

/**
 * Ensures that the instrument array provided has the same 
 * amount of entries as there are headings in the table
 */
function check_instrument_array_shape($headings_array, $instruments_array){
  $headings_length = count($headings_array);
  foreach($instruments_array as $instrument_details){
    if(count($instrument_details) > $headings_length) return false;
  }
  return true;
}

/**
 * Gets current date and adds a month to it
 * Takes a DateTime object, and checks if it is less than current date + 1 month
 * If it is, creates span object with red text
 * if not, creates a span object with default text colour
 * 
 */
function expiry_date_formatter($expiry_date){
  $one_month_future = date_add(new \DateTime(), date_interval_create_from_date_string("1 month"));
  $expiry_date_string = date_format($expiry_date,"d-m-Y");
  if($expiry_date < $one_month_future){
    return "<span style='color: red'>$expiry_date_string</span>";
  }else{
    return "<span>$expiry_date_string</span>";
  }
}

/**
 * Gets the email header content as a string
 */
function get_email_header($garage_name){
  $company_name = COMPANY_NAME_LONG;
  $subject = DEFAULT_SUBJECT;

  $output = "<h1>$company_name<h1>";
  $output .="<h2>$garage_name $subject</h2>";

  return $output;
}

/**
 * Gets the email footer content as a string
 */
function get_email_footer(){
  $company_name = COMPANY_NAME_SHORT;
  $name = EMAIL_SENDER_NAME;

  $output = "<span>- $name, $company_name</span>";

  return $output;
}
?>