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

  $item_names = array_unique(array_column($print_info, 1));
  $barcodes_by_item = [];
  foreach($item_names as $item_name) {
    foreach($print_info as $barcode => $barcode_details) {
      $barcode_item_name = $barcode_details[1];
      if($item_name === $barcode_item_name) {
        $barcodes_by_item[$item_name][$barcode] = $barcode_details; 
      }
    }
  }

  // loop through barcodes and expand qtys.
  $barcode_final_array = [];
  foreach($barcodes_by_item as $item_name => $barcode_items) {
    $filled_array = [];
    foreach($barcode_items as $barcode => $barcode_item_details) {
      $print_qty = $barcode_item_details[0];
      array_push($barcode_item_details, $barcode);
      $filled_array_length = count($filled_array);
      $filled_array += array_fill($filled_array_length, $print_qty, $barcode_item_details);
      // $barcode_final_array[$item_name] = $filled_array;
      $barcode_final_array = array_merge($barcode_final_array, $filled_array);
    }
  }

  // dump($barcode_final_array);
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
        line-height: 11px;
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
        font-size: 14px;
      }

      .labelSheet>div img {
        max-width: 100%;
      }

      .productName {
        padding-bottom: 3px;
        font-weight: bold;
      }

      .barCode {
        padding-top: 3px;
        padding-bottom: 5px;
      }

      .mfgDate {
        padding-top: 6px;
      }

      .overflowText {
        font-size: 12px;
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
    </style>
  </head>
  <body>
    <div class="labelSheet">
      <?php
        $i = 0;
        foreach($barcode_final_array as $print_qty_details):
          $barcode = $print_qty_details[13];
          $print_qty = $print_qty_details[0];
          $print_item_name = strtoupper(substr($print_qty_details[1],0,40));
          $mfg_date = date("m/y", strtotime($print_qty_details[3]));
          $barcode_image = 'data:image/png;base64,'.base64_encode($generator->getBarcode($barcode, $generator::TYPE_EAN_13));
          if($rate_type === 'wholesale') {
            $print_item_mrp = number_format($print_qty_details[8],2,'.','');
          } elseif($rate_type === 'online') {
            $print_item_mrp = number_format($print_qty_details[9],2,'.','');
          } else {
            $print_item_mrp = number_format($print_qty_details[2],2,'.','');
          }
      ?>
        <div>
          <div class="overflowText">
            <div class="productName" style="font-size: 9px; font-weight: bold;"><?php echo $print_item_name ?></div>
            <div class="rate">RATE : <?php echo 'Rs.'.$print_item_mrp ?></div>
            <img src="<?php echo $barcode_image ?>" width="190" height="30" alt="NoImage" />
            <div class="barCode"><?php echo $barcode ?></div>
          </div>
        </div>
      <?php
        $i++;
        if($i === 2) {
          echo '</div><div class="page-break"></div><div class="labelSheet">';
          $i = 0;
        }
        endforeach;
      ?>
    </div>
    <div class="page-break"></div>
  </body>
</html>

<?php exit; ?>