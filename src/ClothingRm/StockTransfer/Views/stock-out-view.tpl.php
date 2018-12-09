<?php
  $current_date = isset($form_data['transferDate']) && $form_data['transferDate']!=='' ? date("d-m-Y", strtotime($form_data['transferDate'])) : date("d-m-Y");
  $from_location_name = $location_ids[$form_data['fromLocationID']];
  $to_location_name = $location_ids[$form_data['toLocationID']];

  $bill_amount = $form_data['billAmount'];
  $round_off = $form_data['roundOff'];
  $netpay = $form_data['netpay'];
  $total_qty = $form_data['totalQty'];
?>

<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $flash_obj->print_flash_message(); ?>
        <div class="global-links actionButtons clearfix"> 
          <div class="pull-right text-right">
            <a href="/stock-transfer/register" class="btn btn-default"><i class="fa fa-book"></i> Stock Transfer Register</a>
            <a href="/sales/entry" class="btn btn-default"><i class="fa fa-file-text-o"></i> New Sales Entry </a> 
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="stockOutForm">
          <div class="panel" style="margin-bottom:0px;">
            <div class= "panel-body">
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Transferred from</label>
                  <p style="font-size:16px;font-weight:bold;color:#225992;"><?php echo $from_location_name ?></p>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Transferred to</label>
                  <p style="font-size:16px;font-weight:bold;color:#225992;"><?php echo $to_location_name ?></p>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Transfer date (dd-mm-yyyy)</label>
                  <p style="font-size:16px;font-weight:bold;color:#225992;"><?php echo $current_date ?></p>
                </div>                
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-striped table-hover font12">
              <thead>
                <tr>
                  <th width="5%"  class="text-center">Sno.</th>                  
                  <th width="18%" class="text-center">Item name</th>
                  <th width="12%" class="text-center">Lot No</th>
                  <th width="10%" class="text-center">GST<br />( in % )</th>
                  <th width="11%" class="text-center">Transferred<br />qty.</th>
                  <th width="8%"  class="text-center">M.R.P<br />( in Rs. )</th>
                  <th width="10%" class="text-center">Amount<br />( in Rs. )</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $tot_bill_amount = $tot_qty = 0;
                  for($i=1;$i<=count($form_data['itemDetails']['itemName']);$i++):
                    $bill_amount = 0;
                    $ex_index = $i-1;
                    if(isset($form_data['itemDetails']['itemName'][$ex_index])) {
                      $item_name = $form_data['itemDetails']['itemName'][$ex_index];
                    } else {
                      $item_name = '';
                    }
                    if(isset($form_data['itemDetails']['itemSoldQty'][$ex_index])) {
                      $item_qty = $form_data['itemDetails']['itemSoldQty'][$ex_index];
                    } else {
                      $item_qty = 0;
                    }
                    if(isset($form_data['itemDetails']['itemRate'][$ex_index])) {
                      $item_rate = $form_data['itemDetails']['itemRate'][$ex_index];
                    } else {
                      $item_rate = 0;
                    }
                    if(isset($form_data['itemDetails']['itemTaxPercent'][$ex_index])) {
                      $tax_percent = $form_data['itemDetails']['itemTaxPercent'][$ex_index];
                    } else {
                      $tax_percent = 0;
                    }
                    if(isset($form_data['itemDetails']['lotNo'][$ex_index])) {
                      $lot_no = $form_data['itemDetails']['lotNo'][$ex_index];
                    } else {
                      $lot_no = '';
                    }
                    if($item_qty && $item_rate>0) {
                      $bill_amount = $item_qty * $item_rate;
                      $tot_bill_amount += $bill_amount;
                      $tot_qty += $item_qty;
                    }
                ?>
                  <tr>
                    <td align="right" style="vertical-align:middle;"><?php echo $i ?></td>
                    <td style="vertical-align:middle;"><?php echo $item_name ?></td>
                    <td style="vertical-align:middle;"><?php echo $lot_no ?></td>                
                    <td style="vertical-align:middle;text-align:right;"><?php echo number_format((float)$tax_percent, 2, '.', '')?></td>
                    <td style="vertical-align:middle;text-align:right;"><?php echo number_format($item_qty, 2, '.', '') ?></td>
                    <td style="vertical-align:middle;text-align:right;"><?php echo number_format($item_rate, 2, '.', '') ?></td>
                    <td 
                      class="grossAmount" 
                      id="grossAmount_<?php echo $i-1 ?>" 
                      index="<?php echo $i-1 ?>"
                      style="vertical-align:middle;text-align:right;"
                    ><?php echo number_format($bill_amount, 2, '.', '') ?>
                    </td>                    
                  </tr>
                <?php endfor; ?>
                  <tr>
                    <td colspan="6" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Gross Amount</td>
                    <td id="grossAmount" class="" style="font-size:16px;text-align:right;font-weight:bold;"><?php echo number_format($bill_amount, 2, '.', '') ?></td>
                  </tr>
                  <tr>
                    <td colspan="6" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">(+/-) Round off</td>
                    <td id="roundOff" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" class="roundOff"><?php echo number_format($round_off, 2, '.', '')  ?></td>
                  </tr>
                  <tr>
                    <td colspan="6" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Net Pay</td>
                    <td id="netPayBottom" class="netPay" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;"><?php echo number_format($netpay, 2, '.', '')  ?></td>
                  </tr>
                  <tr>
                    <td colspan="4" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Total Qty.</td>
                    <td id="totalQty" class="netPay" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;"><?php echo number_format($total_qty, 2, '.', '')  ?></td>
                    <td colspan="2">&nbsp;</td>                    
                  </tr>                   
              </tbody>
            </table>
          </div>
        </form>  
      </div>
    </section>
  </div>
</div>