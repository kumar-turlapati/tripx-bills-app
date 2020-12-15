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
      $barcode_final_array[$item_name] = $filled_array;
    }
  }
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
        line-height: 11px;
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
        font-size: 10px;
      }
      .labelSheet >div img {
        max-width: 100%;
      }
      .productName {
        text-align: center;
        padding-bottom: 3px;
      }
      .barCode {
        text-align: center;
        font-size: 11px;
      }
      .rate {
        text-align: center;
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
    <?php 
      foreach($barcode_final_array as $item_name => $print_info_print): ?>
      <div class="labelSheet">
      <?php
        $i = 0;
        foreach($print_info_print as $print_qty_details):
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
          <div class="productName" style="font-size: 9px; font-weight: bold;"><?php echo $print_item_name ?></div>
          <div class="rate">RATE : <?php echo 'Rs.'.$print_item_mrp ?></div>
          <img src="<?php echo $barcode_image ?>" width="190" height="30" alt="NoImage" />
          <div class="barCode"><?php echo $barcode ?></div>
          <div class="mfgDate">&nbsp;</div>
        </div>
  <?php
      $i++;
      if($i === 65) {
        echo '</div><div class="page-break"></div><div class="labelSheet">';
        $i = 0;
      }
      endforeach;
  ?>
    <?php
      echo '</div><div class="page-break"></div>';
      endforeach; 
    ?>
  </body>
</html>

<?php exit; ?>