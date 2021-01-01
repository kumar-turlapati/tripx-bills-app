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
      @page {
        margin: 26.4566929px 0;
      }
      body {
        margin: 0px;
        font-family: arial, sans-serif;
        line-height: 9px;
        background-color: #ccc;
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
        max-width: 396.8503937px;
        margin: auto;
        padding: 0 7.5590551181px;
        display: table;
        width: 100%;
      }

      .labelSheet>div {
        width: 188.9763779528px;
        background-color: #fff;
        height: 94.5px;
        float: left;
        margin-left: 0.9448818898px;
        margin-right: 0.9448818898px;
        margin-bottom: 5.669291339px;
        margin-top: 5.669291339px;
        border-radius: 5px;
        padding: 10px;
        font-size: 9px;
      }

      .labelSheet>div img {
        max-width: 100%;
      }

      .productName {
        padding-bottom: 3px;
        font-weight: bold;
      }

      .barCode {
        padding-top: 2px;
        padding-bottom: 2px;
      }
      /*
      .mfgDate {
        padding-top: 6px;
      }
      */
      .overflowText {
        font-size: 10px;
        overflow: hidden;
        height: 100%;
        text-align: center;
      }

      .bimg {
        padding-top: 5px;
      }
      @media all {
        .page-break { display: none; }
      }
      @media print {
        .page-break { display: block; page-break-before: always; }
      }
      .rate {
        text-align: center;
        font-size: 9px;
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
        <div class="overflowText">
          <div class="productName"><?php echo $print_item_name ?></div>
          <div class="rate">BRAND : <?php echo $mfg_name ?></div>
          <img src="<?php echo $barcode_image ?>" width="190" height="30" alt="NoImage" />
          <div class="barCode"><?php echo $barcode ?></div>
          <div class="mfgDate"><?php echo 'CASE: '.$cno.' - '.$packed_qty.' '.$uom_name ?></div>
        </div>
      </div>
      <?php
        $tot_sticker_count++;
        if($tot_sticker_count === 2) {
          $tot_sticker_count = 0;
          echo '</div>'.'<div class="page-break"></div><div class="labelSheet">';
        }
        endforeach;
        echo '</div><div class="page-break"></div>';
      ?>
  </body>
</html>

<?php exit; ?>