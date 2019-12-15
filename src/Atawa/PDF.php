<?php

namespace Atawa;

use Atawa\Utilities;

include_once __DIR__.'../../../libraries/fpdf181/fpdf.php';

class PDF extends FPDF {

  private static $pdf = null;
  private static $client_details = null;
  private static $skip_footer = null;
  private static $loc_address = null;

  public static function getInstance($skip_footer = false, $loc_address=[]) {
    if(self::$pdf == null) {
      self::$pdf = new PDF;
    }
    self::$skip_footer = $skip_footer;
    self::$loc_address = $loc_address;
    return self::$pdf;
  }

  public function get_client_details() {
    return Utilities::get_client_details();
  }

  public function Header() {
    global $height;

    if(is_array(self::$loc_address) && count(self::$loc_address)>0) {
      $business_name = self::$loc_address['store_name'];
      $address = self::$loc_address['address1'].', '.self::$loc_address['address2'];

      $address1 = self::$loc_address['address1'];
      $address2 = self::$loc_address['address2'];
      $address3 = self::$loc_address['address3'];

      $phones = self::$loc_address['phones'];
      $gst_no = self::$loc_address['gst_no'];
    } else {
      $client_details = $this->get_client_details();
      $business_name = $client_details['businessName'];
      $address = $client_details['addr1'].', '.$client_details['addr2'];

      $address1 = $client_details['addr1'];
      $address2 = $client_details['addr2'];
      $address3 = '';

      $phones = $client_details['phones'];
      $gst_no = $client_details['gstNo'];
    }

    // Arial bold 15
    $this->SetFont('Arial','B',15);
    $this->Cell(0, $height, $business_name, 0, 1, 'C');
    $this->SetFont('Arial','B',8);
    $this->Ln(5);
    $this->Cell(0,$height,$address1,'',1,'C');
    $this->Ln(3);
    $this->Cell(0,$height,$address2,'',1,'C');
    // if($address3 !== '') {
    //   // $this->Ln(4);
    //   $this->Cell(1,$height,$address3,'',1,'C');
    // }
    $this->Ln(4);
    $this->Cell(0,$height,'Phone(s):'.$phones,'',1,'C');
    if($gst_no !== '') {
      $this->Ln(4); 
      $this->Cell(0,$height,'GSTIN:'.$gst_no,'',1,'C');      
    }
    $this->Ln(3);
    $this->Cell(0,0,'','B',1);
    $this->Ln(4);
  }

  // Page footer
  public function Footer() {
    if(self::$skip_footer === false) {
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
    }
  }  

}
