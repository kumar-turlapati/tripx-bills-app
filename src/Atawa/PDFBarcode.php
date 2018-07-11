<?php

namespace Atawa;

include_once __DIR__.'../../../libraries/fpdf181/fpdf.php';

class PDFBarcode extends FPDF {

  private static $pdf = null;
  private static $client_details = null;

  public static function getInstance() {
    if(self::$pdf == null) {
      self::$pdf = new PDFBarcode;
    }
    return self::$pdf;
  }
}