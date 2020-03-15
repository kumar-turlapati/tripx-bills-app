<?php
  use Atawa\Utilities;
  use Atawa\Flash;
  use Atawa\PDFBarcode;

  $flash = new Flash;
  if(!isset($_SESSION['printBarCodes'])) {
    $flash->set_flash_message('No barcodes are available for Printing.',1);
    Utilities::redirect('/barcodes/list');
  }
  $printable_array = [];
  $generator = new \Picqer\Barcode\BarcodeGeneratorPNG;
  $print_info = $_SESSION['printBarCodes'];
  foreach($print_info as $barcode => $print_qty_details) {
    $print_qty_details['barcode'] = $barcode;
    $print_qty = $print_qty_details[0];
    $printable_array = array_merge($printable_array, array_fill(0, $print_qty, $print_qty_details));
  }
  if(count($printable_array)<=0) {
    die("Unable to print....");
  }

  // dump($printable_array);
  // exit;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Print Barcodes</title>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style type="text/css">
      @page { margin: 0; }
      body {
        margin: 0px;
        font-family: arial,sans-serif;
        line-height: 9px;
      }
      * {
        -webkit-box-sizing: border-box;
           -moz-box-sizing: border-box;
                box-sizing: border-box;
      }
      *:before,
      *:after {
        -webkit-box-sizing: border-box;
           -moz-box-sizing: border-box;
                box-sizing: border-box;
      }
      .labelSheet {
        max-width:794px;
        margin:auto;
        padding:45.354336px 18.8976px;
        display: table;
        width: 100%;
      }
      .labelSheet >div {
        width: 143.5px;
        height: 79.3px;
        float: left;
        margin-left: 3.7px;
        margin-right: 3.7px;
        border-radius: 3px;
        padding: 3px 8px 5px 8px;
        font-size: 9px;
      }
      .labelSheet >div img {
        max-width: 100%;
      }
      .productName {
        text-align: center;
        padding-bottom: 0px;
      }
      .barCode {
        text-align: center;
        font-size: 11px;
      }
      .rate {
        text-align: center;
        font-size: 9px;
      }
      .mfgDate {
        text-align: center;
      }
      @media all {
        .page-break { display: none; }
      }
      @media print {
        .page-break { display: block; page-break-before: always; }
      }
    </style>
  </head>
  <body>
    <div class="labelSheet">
    <?php 
      $tot_sticker_count = 0;
      foreach($printable_array as $print_qty_details):
        $barcode = $print_qty_details['barcode'];
        $print_qty = $print_qty_details[0];
        $print_item_name = strtoupper(substr($print_qty_details[1],0,20));
        if($rate_type === 'wholesale') {
          $print_item_mrp = number_format($print_qty_details[8],2,'.','');
        } elseif($rate_type === 'online') {
          $print_item_mrp = number_format($print_qty_details[9],2,'.','');
        } else {
          $print_item_mrp = number_format($print_qty_details[2],2,'.','');
        }        
        $cno = $print_qty_details[5];
        $mfg_name = substr(strtoupper($print_qty_details[6]), 0, 12);
        $packed_qty = $print_qty_details[4];
        $uom_name = strtoupper(substr($print_qty_details[7],0,5));
        $barcode_image = 'data:image/png;base64,'.base64_encode($generator->getBarcode($barcode, $generator::TYPE_EAN_13));
    ?>
      <div>
        <div class="productName"><?php echo $print_item_name ?></div>
        <div class="rate">BRAND : <?php echo $mfg_name ?></div>
        <img src="<?php echo $barcode_image ?>" width="190" height="30" alt="NoImage" />
        <div class="barCode"><?php echo $barcode ?></div>
        <div class="mfgDate"><?php echo 'CASE: '.$cno.' - '.$packed_qty.' '.$uom_name ?></div>
      </div>
      <?php
        $tot_sticker_count++;
        if($tot_sticker_count === 65) {
          $tot_sticker_count = 0;
          echo '</div>'.'<div class="labelSheet">';
        }
        endforeach;
        echo '</div>';
      ?>
  </body>
</html>

<?php exit; ?>