<?php
  use Atawa\Utilities;

  // dump($form_data);

  $invoice_date = date("d-m-Y", strtotime($form_data['invoiceDate']));
  $invoice_no = $form_data['billNo'];
  $invoice_amount = $form_data['netPay'];
  $tot_products = isset($form_data['itemDetails']['itemName']) ? count($form_data['itemDetails']['itemName']) : 0;
  if(isset($form_data['name']) && $form_data['name'] !== '') {
    $customer_name = $form_data['name'];
  } elseif( isset($form_data['customerName']) && $form_data['customerName'] !== '') {
    $customer_name = $form_data['customerName'];
  } elseif( isset($form_data['tmpCustName']) && $form_data['tmpCustName'] !== '') {
    $customer_name = $form_data['tmpCustName'];    
  } else {
    $customer_name = '';
  }

  $lot_nos = $form_data['itemDetails']['lotNo'];
  $item_rates = $form_data['itemDetails']['itemRate'];
  $item_qtys = $form_data['itemDetails']['itemSoldQty'];
  $item_rate_lot_nos = array_combine($lot_nos, $item_rates);
  $total_item_qty = array_sum($item_qtys);

  array_walk($lot_nos, function(&$x) {$x = '"'.$x.'"';});
  array_walk($item_rates, function(&$x) {$x = '"'.$x.'"';});
  array_walk($item_qtys, function(&$x) {$x = '"'.$x.'"';});

  // dump($item_qtys, $total_item_qty);
?>

<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $flash_obj->print_flash_message(); ?>
        <div class="global-links actionButtons clearfix"> 
          <div class="pull-right text-right">
            <a href="/sales/list" class="btn btn-default"><i class="fa fa-book"></i> Sales Register</a>&nbsp;&nbsp;
          </div>
        </div>
        <form id="gpEntryForm" method="POST">
          <div class="table-responsive">
            <table class="table table-hover font12" style="border-top:none;border-left:none;border-right:none;margin-bottom: 0px;">
              <thead>
                <tr>
                  <td style="vertical-align:middle;font-size:18px;font-weight:bold;border-right:none;border-left:none;border-top:none;text-align:right;width:10%;" id="scanText">Scan Barcode</td>
                  <td style="vertical-align:middle;border-right:none;border-left:none;border-top:none;width:10%;">
                    <input
                      type="text"
                      id="gpBarcode"
                      style="font-size:16px;font-weight:bold;border:1px dashed #225992;padding-left:5px;font-weight:bold;width:150px;"
                      maxlength="15"
                    />
                  </td>
                  <td style="border-top:none; border-left:none; border-right:none; border-bottom:none; text-align: right; width: 30%; color: #225992; font-weight: bold; font-size: 14px; vertical-align: middle;">Last item scanned:</td>
                  <td style="border-top:none; border-left:none; border-right:none; border-bottom: 2px dotted; text-align: left; width: 35%; color: #4ab033; font-weight: bold; font-size: 16px; vertical-align: middle;" id="lastScannedSaleItem">&nbsp;</td>
                </tr>
              </thead>
            </table>
          </div>
          <div class="table-responsive">
            <table class="table table-striped table-hover font12" id="gpItemsTable" style="display:none;">
              <thead>
                <tr>
                  <th width="5%"  class="text-center">Sno.</th>                  
                  <th width="30%" class="text-center">Item name</th>
                  <th width="20%" class="text-center">Lot no.</th>
                  <th width="10%" class="text-center">Qty.</th>
                  <th width="15%"  class="text-center">Rate<br />( Rs. )</th>
                </tr>
              </thead>
              <tbody id="tBodyowItems"></tbody>
            </table>
            <table class="table table-striped table-hover font12" id="gpInvoiceInfo" style="display:none;">
              <thead>
                <tr>
                  <th class="text-center">Invoice No.</th>
                  <th class="text-center">Invoice Date</th>
                  <th class="text-center">No.of Items</th>
                  <th class="text-center">Total Qty.</th>
                  <th class="text-center">Amount</th>
                  <th class="text-center">Customer name</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td style="font-size: 18px;font-weight: bold;text-align: center;"><?php echo $invoice_no ?></td>
                  <td style="font-size: 18px;font-weight: bold;text-align: center;"><?php echo $invoice_date ?></td>
                  <td style="font-size: 18px;font-weight: bold;text-align: center;"><?php echo $tot_products ?></td>
                  <td style="font-size: 18px;font-weight: bold;text-align: center;"><?php echo number_format($total_item_qty,2,'.','') ?></td>
                  <td style="font-size: 18px;font-weight: bold;text-align: center;"><?php echo number_format($invoice_amount,2,'.','') ?></td>
                  <td style="font-size: 18px;font-weight: bold;text-align: center;"><?php echo $customer_name ?></td>
                </tr>
              </tbody>
            </table>            
            <input type="hidden" name="locationCode" id="locationCode" value="<?php echo $default_location ?>" />
          </div>
          <div class="text-center" id="gpActionButtons" style="display: none;">
            <button class="btn btn-primary cancelOp" id="genGp" name="op" value="genGp">
              <i class="fa fa-save"></i> Generate Gate Pass
            </button>
            <button class="btn btn-danger cancelButton" id="genGpCancel">
              <i class="fa fa-times"></i> Cancel
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>
<script type="text/javascript">
  var gpLotNos = [<?php echo implode(',', $lot_nos)?>];
  var gpRates = [<?php echo implode(',', $item_rates)?>];
  var gpQtys = [<?php echo implode(',', $item_qtys)?>];
</script>