<?php
  use Atawa\Utilities;

  $current_date = isset($form_data['transferDate']) && $form_data['transferDate']!=='' ? date("d-m-Y", strtotime($form_data['transferDate'])) : date("d-m-Y");
  $from_location_name = $location_ids[$form_data['fromLocationID']];
  $to_location_name = $location_ids[$form_data['toLocationID']];

  $bill_amount = $form_data['billAmount'];
  $round_off = $form_data['roundOff'];
  $netpay = $form_data['netpay'];
  $total_qty = $form_data['totalQty'];

  // dump($form_data['itemDetails']);
?>

<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $flash_obj->print_flash_message(); ?>
        <div class="global-links actionButtons clearfix"> 
          <div class="pull-right text-right">
            <a href="/stock-transfer/register" class="btn btn-default"><i class="fa fa-book"></i> Stock Transfer Register</a>
            <a href="/stock-transfer/out" class="btn btn-default"><i class="fa fa-file-text-o"></i> New Stock Transfer</a> 
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
                  <th width="20%" class="text-center">Item Name</th>
                  <th width="9%" class="text-center">Barcode</th>
                  <th width="10%" class="text-center">Lot No.</th>
                  <th width="8%" class="text-center">Case / Box No.</th>
                  <th width="8%" class="text-center">GST<br />( in % )</th>
                  <th width="8%" class="text-center">Transferred<br />Qty.</th>
                  <th width="8%"  class="text-center">MRP<br />( in Rs. )</th>
                  <th width="8%" class="text-center">Amount<br />( in Rs. )</th>
                  <th width="17%" class="text-center">Status at<br />Destination</th>                  
                </tr>
              </thead>
              <tbody>
                <?php
                $tot_bill_amount = $tot_qty = 0;
                if(count($form_data['itemDetails']) > 0):
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
                    if(isset($form_data['itemDetails']['cno'][$ex_index])) {
                      $cno = $form_data['itemDetails']['cno'][$ex_index];
                    } else {
                      $cno = '';
                    }
                    if(isset($form_data['itemDetails']['barcode'][$ex_index])) {
                      $barcode = $form_data['itemDetails']['barcode'][$ex_index];
                    } else {
                      $barcode = '';
                    }                    
                    if(isset($form_data['itemDetails']['scannedDate'][$ex_index])) {
                      $scanned_date = $form_data['itemDetails']['scannedDate'][$ex_index];
                    } else {
                      $scanned_date = '';
                    }   
                    if($item_qty && $item_rate>0) {
                      $bill_amount = $item_qty * $item_rate;
                      $tot_bill_amount += $bill_amount;
                    } else {
                      $bill_amount = $tot_bill_amount = 0;
                    }
                    $tot_qty += $item_qty;

                    $in_status = (int)$form_data['itemDetails']['status'][$ex_index];
                    if($in_status === 1) {
                      if($scanned_date !== '0000-00-00 00:00:00' && $scanned_date !== '') {
                        $status_text = '<span style="color:green;font-size:12px;font-weight:bold;">In time: '.date("d-m-y, h:ia", strtotime($scanned_date)).'</span>';
                      } else {
                        $status_text = '';
                      }
                    } else {
                      $status_text = '<span style="color:red;font-size:11px;font-weight:bold;">Not Received.</span>';
                    }
                ?>

                <?php if( ((int)$form_data['itemDetails']['status'][$ex_index] === 1) || Utilities::is_admin()): ?>
                  <tr class="font11">
                    <td align="right" class="valign-middle"><?php echo $i ?></td>
                    <td style="vertical-align:middle;"><?php echo $item_name ?></td>
                    <td style="vertical-align:middle;font-weight:bold;"><?php echo $barcode ?></td>
                    <td style="vertical-align:middle;"><?php echo $lot_no ?></td>
                    <td style="vertical-align:middle;text-align:right;"><?php echo $cno ?></td>
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
                    <td style="text-align:right;"><?php echo $status_text ?></td>
                  </tr>
                <?php else: ?>
                  <tr>
                    <td colspan="10" style="vertical-align:middle;font-weight:bold;font-size:15px;color:#FC4445;text-align:center;">Stock Transfer is in progress. Not yet received by `<?php echo $from_location_name ?>`</td>
                  </tr>
                <?php endif; ?>
                <?php
                  endfor;
                else: ?>
                  <tr>
                    <td colspan="9" style="font-weight:bold;font-size:16px;color:red;text-align:center;">Stock Transfer is in Progress...</td>
                  </tr>
                <?php endif; ?>
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Gross Amount</td>
                    <td id="grossAmount" class="" style="font-size:18px;text-align:right;font-weight:bold;color:#225992"><?php echo number_format($tot_bill_amount, 2, '.', '') ?></td>
                  </tr>
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">(+/-) Round off</td>
                    <td id="roundOff" style="vertical-align:middle;font-weight:bold;font-size:18px;text-align:right;color:#225992" class="roundOff"><?php echo number_format($round_off, 2, '.', '')  ?></td>
                  </tr>
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Net Pay</td>
                    <td id="netPayBottom" class="netPay" style="vertical-align:middle;font-weight:bold;font-size:18px;text-align:right;color:#225992"><?php echo number_format($netpay, 2, '.', '')  ?></td>
                  </tr>
                  <tr>
                    <td colspan="6" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Total Qty.</td>
                    <td id="totalQty" class="netPay" style="vertical-align:middle;font-weight:bold;font-size:18px;text-align:right;color:#225992"><?php echo number_format($total_qty, 2, '.', '')  ?></td>
                    <td colspan="3">&nbsp;</td>                    
                  </tr>                   
              </tbody>
            </table>
          </div>
        </form>  
      </div>
    </section>
  </div>
</div>