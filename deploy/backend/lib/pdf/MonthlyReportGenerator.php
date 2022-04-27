<?php
namespace pdf;

class MonthlyReportGenerator {
  public static function generate_report($garage_name, $instruments_array){
    $pdf_obj = new PDFGenerator('P', 'mm', 'A4');

    $pdf_obj->setFont('Arial', 14);
    $pdf_obj->AddPage();

    $instrument_headers = ["Instrument Name", "Serial Number", "Expiry Date"];
    $pdf_obj->generate_header($garage_name . " : " . date("MY"));
    $pdf_obj->generate_table($instrument_headers,$instruments_array);
    return $pdf_obj->Output('S');
  }
}
