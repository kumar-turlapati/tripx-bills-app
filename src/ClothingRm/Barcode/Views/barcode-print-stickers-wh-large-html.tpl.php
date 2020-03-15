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

  // dump($printable_array);
  // exit;

  if(count($printable_array)<=0) {
    die("Unable to print....");
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Barcode Print</title>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style type="text/css">
      @page { margin: 26.4566929px 0; }
      body {
        margin: 0px;
        font-family: arial,sans-serif;
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
        max-width:794px;
        margin:auto;
        padding: 0 11.343px;
        display: table;
        width: 100%;
      }
      .labelSheet >div {
        width: 377.95275591px;
        background-color: #fff;
        height: 166.2992126px;
        float: left;
        margin-left: 3.775px;
        margin-right: 3.775px;
        margin-bottom: 5.6692913386px;
        margin-top: 5.6692913386px;
        border-radius: 5px;
        padding: 10px;
        font-size: 14px;
      }
      .labelSheet >div img {
        max-width: 100%;
      }
      .productName {
        text-align: right;
        padding-bottom: 3px;
        padding-right: 10px;
      }
      .barCode {
        font-size: 12px;
        font-weight: bold;
        padding-left:50px;
      }
      .mfgDate {
        text-align: left;
        padding-top: 10px;
      }
      .overflowText {
        overflow: hidden;
        height: 100%;
      }
      .bimg {
        padding-top: 5px;
      }
      @media print {
        .lableSheet {
          page-break-before: always;
        }
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
        $print_item_name = strtoupper($print_qty_details[1]);
        if($rate_type === 'wholesale') {
          $print_item_mrp = number_format($print_qty_details[8],2,'.','');
        } elseif($rate_type === 'online') {
          $print_item_mrp = number_format($print_qty_details[9],2,'.','');
        } else {
          $print_item_mrp = number_format($print_qty_details[2],2,'.','');
        }        
        $mfg_date = date("m/y", strtotime($print_qty_details[3]));
        $packed_qty = $print_qty_details[4];
        $uom_name = strtoupper(substr($print_qty_details[7],0,5));
        $cno = $print_qty_details[5];
        $bno = $print_qty_details[10];
        $lot_no = $print_qty_details[11];
        $mfg_name = $print_qty_details[12];
        $barcode_image = 'data:image/png;base64,'.base64_encode($generator->getBarcode($barcode, $generator::TYPE_EAN_13));
    ?>      
      <div>
        <div class="overflowText">
          <div class="productName"><?php echo $print_item_name ?></div>
          <?php /*<div>MRP: RS.<?php echo $print_item_mrp ?></div>*/?>
          <img src="<?php echo $barcode_image ?>" width="190" height="30" alt="NoImage" class="bimg" />
          <div class="barCode"><?php echo $barcode ?></div>
          <div class="mfgDate"><?php echo 'Case/Batch No.: '.$cno.' - '.$packed_qty.' '.$uom_name ?></div>
          <?php if($bno !== '' ) : ?> 
            <div class="mfgDate" style="text-align: left;"><?php echo 'Bale No: '.$bno ?></div>
          <?php endif; ?>
          <?php /*<div class="mfgDate" style="text-align: left;font-size: 12px;"><?php echo $lot_no ?>&nbsp;<span style="padding-left:60px;"><?php echo $mfg_name ?></span></div>*/?>
          <div class="mfgDate" style="text-align: center;font-size: 12px;font-weight: bold;"><?php echo $mfg_name ?></div>
        </div>
      </div>             
      <?php
        $tot_sticker_count++;
        if($tot_sticker_count === 12) {
          $tot_sticker_count = 0;
          echo '</div>'.'<div class="labelSheet">';
        }
        endforeach;
        echo '</div>';
      ?>
  </body>
</html>

<?php exit; ?>