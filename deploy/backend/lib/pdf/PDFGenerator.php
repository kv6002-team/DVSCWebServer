<?php
namespace pdf;

require "fpdf.php";

class PDFGenerator extends \FPDF {
  public function generate_header($title){
    $this->Cell(30,10,$title,1,0,'C');
  }
  public function generate_table($headers, $array){
    foreach($headers as $col){
      $this->Cell(40, 6, $col, 1);
      $this->Ln(10);
    }
    $this->Ln();
    foreach($array as $data){
      $this->Cell(40, 6, $data, 1);
    }
    $this->Ln();
  }
}
