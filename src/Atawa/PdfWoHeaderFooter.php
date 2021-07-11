<?php

namespace Atawa;

use Atawa\Utilities;

include_once __DIR__.'../../../libraries/fpdf181/fpdf.php';

class PdfWoHeaderFooter extends FPDF {

  private static $pdf = null;

  public static function getInstance($skip_footer = false, $loc_address=[], $token='', $client_code='') {
    if(self::$pdf == null) {
      self::$pdf = new PdfWoHeaderFooter;
    }
    return self::$pdf;
  }

  // Page footer
  /*
  public function Footer() {
    // Position at 1.5 cm from bottom
    $this->SetY(-10);
    // Arial italic 8
    $this->SetFont('Arial','IB',8);

    // footer text
    $footer_text = 'Page '.$this->PageNo().'/{nb}';
    $promo_text = 'Powered by QwikBills.com - Cloud based Billing & Inventory Solution.';

    // Page number
    $this->Cell(0,4,$footer_text,'T',2,'C');
    $this->SetFont('Arial','B',6);    
    $this->Cell(0,-4,$promo_text,0,1,'R');
  }*/ 
}