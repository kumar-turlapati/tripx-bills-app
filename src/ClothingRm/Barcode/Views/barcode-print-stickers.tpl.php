<?php
  
  use Atawa\Utilities;
  use Atawa\Flash;
  use Atawa\PDFBarcode;

  $flash = new Flash;
  if(!isset($_SESSION['printBarCodes'])) {
    $flash->set_flash_message('No barcodes are available for Printing.',1);
    Utilities::redirect('/barcodes/list');
  }  

  $generator = new \Picqer\Barcode\BarcodeGeneratorPNG;
  $print_info = $_SESSION['printBarCodes'];
  $total_print_qty = array_sum(array_column($print_info, 0));
  $no_of_pages = ceil($total_print_qty/65);

  $column_widths = [40];

  # start PDF printing.
  $pdf = PDFBarcode::getInstance();
  $pdf->AliasNbPages();
  $pdf->SetAutoPageBreak(false);
  $pdf->AddPage('P','A4');
  $pdf->SetFont('Arial', '', 12);
  $pdf->setLeftMargin(8);
  // dump($print_info, $total_print_qty);
  // exit;

  $row_cntr = 0;
  foreach($print_info as $barcode => $print_qty_details) {
    $print_qty = $print_qty_details[0];
    $print_item_name = strtoupper(substr($print_qty_details[1],0,12));
    $print_item_mrp = number_format($print_qty_details[2],2,'.','');
    $mfg_date = date("d/m/y", strtotime($print_qty_details[3]));

/*    $print_item_mrp = '20000.00';
    $print_item_name = strtoupper('abcdeabcdeabcdeabcde');*/

    $barcode_cntr = 0;
    $barcode_image = 'data:image/png;base64,' . base64_encode($generator->getBarcode($barcode, $generator::TYPE_EAN_13));
    $bc_image_info = getimagesize($barcode_image);
    $barcode_a = str_split($barcode);
    $barcode_string = implode(' ', $barcode_a);
    $pdf->Ln(3);
    for($sticker=0; $sticker<$print_qty; $sticker++) {
      $pdf->SetFont('Arial', '', 6);
      $pdf->Cell($column_widths[0],0,$pdf->Image($barcode_image, $pdf->GetX(10), $pdf->GetY(10), 30, 10, 'png'),'',0,'L');
      $barcode_cntr++;
      if($barcode_cntr >= 5) {
        $row_cntr++;
        $pdf->Ln(12.5);
        $pdf->SetFont('Arial', '', 8);
        for($i=1;$i<=5;$i++) {
          $pdf->Cell($column_widths[0],0,$barcode_string,'',0,'L');
        }
        $pdf->SetFont('Arial', '', 6);        
        $pdf->Ln(2.4);
        for($i=1;$i<=5;$i++) {
          $pdf->Cell($column_widths[0],0,$print_item_name,'',0,'L');
        }
        $pdf->Ln(2.4);
        for($i=1;$i<=5;$i++) {
          $pdf->Cell($column_widths[0],0,'Rate: Rs.'.$print_item_mrp,'',0,'C');
        }
        $pdf->Ln(5.5);   
        $barcode_cntr = 0;
      }
      if($row_cntr >= 13) {
        $row_cntr=0;
        $pdf->AddPage('P','A4');
        $pdf->setLeftMargin(8);            
      }
    }
  }

  $pdf->Output();

  exit; 
?>

<?php /*

<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
      .pagebreak { page-break-before: always; } /* page-break-after works, as well
    </style>
  </head>
  <body>
    <div id="printDiv">
      <div style="font-family: monospace; width:190px;">       
        <table style="width: 100%;" cellpadding=0 cellspacing=0>
          <tbody>
            <?php

              foreach($print_info as $barcode => $print_qty_details) { 
                $print_qty = $print_qty_details[0];
                $print_item_name = substr($print_qty_details[1],0,20);
                $print_item_mrp = $print_qty_details[2];
                $mfg_date = date("d/m/y", strtotime($print_qty_details[3]));
                for($i=1;$i<=$print_qty; $i+=5) {
            ?>
                  <tr style="font-weight:bold;">
                    <?php for($j=1;$j<=5;$j++): ?>
                      <td style="text-align:left;width:20%;padding:15px;text-align:center;border-right:3px dashed;">
                        <p style="font-size:9px;text-align:right;clear:both;margin:0px;"><?php echo $print_item_name ?></p>                     
                        <span style="font-size:9px;">MRP: Rs.<?php echo number_format($print_item_mrp, 2, '.', '') ?></span>
                        <?php echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($barcode, $generator::TYPE_EAN_13)) . '" width="100" height="20">'; ?>
                        <p style="font-size:10px;clear:both;margin:0px;"><?php echo $barcode ?></p>
                        <p style="font-size:8px;text-align:center;clear:both;margin:0px;">Mfg:<?php echo $mfg_date ?> - 1pc.</p>
                      </td>
                    <?php endfor; ?>
                  </tr>
                  <?php
                    $print_cntr = $print_cntr + 5;
                    if((int)$print_cntr >= 65) {
                      $print_cntr = 0;
                  ?>
                    <tr style="page-break-before: always;"></tr>
                  <?php } else { ?>
                    <tr><td colspan="3">&nbsp;</td></tr>
                  <?php } ?>
                
                <?php } ?>

            <?php } ?>
        </table>
      <div>
    </div>
  </body>
</html>
?>*/ ?>