<?php

namespace Atawa;

use Atawa\Utilities;

include_once __DIR__.'../../../libraries/fpdf181/fpdf.php';

class PDF extends FPDF {

  private static $pdf = null;
  private static $client_details = null;

  public static function getInstance() {
    if(self::$pdf == null) {
      self::$pdf = new PDF;
    }
    return self::$pdf;
  }

  public function get_client_details() {
    return Utilities::get_client_details();
  }

  public function Header() {
    global $height;

    $client_details = $this->get_client_details();
    $business_name = $client_details['businessName'];

    $address = $client_details['addr1'].', '.$client_details['addr2'];

    // Arial bold 15
    $this->SetFont('Arial','B',15);
    $this->Cell(0, $height, $business_name, 0, 1, 'C');
    $this->SetFont('Arial','B',8);
    $this->Ln(5);
    
    $this->Cell(0,$height,$address,'',1,'C');
    $this->Ln(4);
    $this->Cell(0,$height,'Phone(s):'.$client_details['phones'],'',1,'C');

    if(isset($client_details['gstNo']) && $client_details['gstNo'] !== '') {
      $this->Ln(4); 
      $this->Cell(0,$height,'GSTIN:'.$client_details['gstNo'],'',1,'C');      
    }
    $this->Ln(3);
    $this->Cell(0,0,'','B',1);
    $this->Ln(4);
  }

  // Page footer
  public function Footer() {
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','IB',8);

    // footer text
    $footer_text = 'Page '.$this->PageNo().'/{nb}';
    $promo_text = 'Powered by QwikBills.com, Version 1.6, Build 2.5.6';

    // Page number
    $this->Cell(0,4,$footer_text,'T',2,'C');
    $this->SetFont('Arial','B',6);    
    $this->Cell(0,-4,$promo_text,0,1,'R');    
  }  

}