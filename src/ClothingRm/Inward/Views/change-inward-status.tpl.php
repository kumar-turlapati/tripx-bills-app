<?php

  // dump($form_data, $form_errors);
  // dump($form_errors);
  // dump($form_data);
  // dump($taxes, $taxes_raw);
  // exit;

  $purchase_date = isset($form_data['purchaseDate']) ? date("d-m-Y", strtotime($form_data['purchaseDate'])) : '';
  $credit_days = isset($form_data['creditDays']) ? $form_data['creditDays'] : '';
  $po_no = isset($form_data['poNo']) ? $form_data['poNo'] : '';
  $remarks = isset($form_data['remarks']) ? $form_data['remarks'] : '';
  $supply_type = isset($form_data['supplyType']) ? $form_data['supplyType'] : '';
  $supplier_name = isset($form_data['supplierName']) ? $form_data['supplierName'] : '';
  $store_name = isset($client_locations[$form_data['locationID']]) ? $client_locations[$form_data['locationID']] : '';
  $payment_method = isset($payment_methods[$form_data['paymentMethod']]) ? $payment_methods[$form_data['paymentMethod']] : '';
  $inward_status = isset($form_data['status']) ? $form_data['status'] : 0; 
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $utilities->print_flash_message() ?>
        <?php if($api_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $api_error ?> 
          </div>
        <?php endif; ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/inward-entry/list" class="btn btn-default">
              <i class="fa fa-book"></i> Purchase Register
            </a>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font12" id="owItemsTable">
            <thead>
              <tr>
                <th width="10%" class="text-center valign-middle">PO No.</th>
                <th width="5%"  class="text-center valign-middle">PO Date</th>             
                <th width="15%" class="text-center valign-middle">Supplier Name</th>
                <th width="10%" class="text-center valign-middle">Store Name</th>
                <th width="5%"  class="text-center valign-middle">Payment Method</th>
                <th width="7%"  class="text-center valign-middle">Credit Period</th>
                <th width="10%" class="text-center valign-middle">Supplier Location</th>
              </tr>
            </thead>
            <tbody>
                <tr>
                  <td class="valign-middle"><?php echo $po_no ?></td>
                  <td style="vertical-align:middle;"><?php echo $purchase_date ?></td>
                  <td style="vertical-align:middle;"><?php echo $supplier_name ?></td>
                  <td style="vertical-align:middle;" align="center"><?php echo $store_name ?></td>
                  <td style="vertical-align:middle;" align="center"><?php echo $payment_method ?></td>
                  <td style="vertical-align:middle;"><?php echo $credit_days ?></td>
                  <td style="vertical-align:middle;text-align:center;"><?php echo $states_a[$form_data['supplierStateID']] ?></td> 
                </tr>
            </tbody>
          </table>
        </div>     
        <form class="form-validate form-horizontal" method="POST" id="inwardEntryForm" autocomplete="off">      
          <div class="table-responsive">
            <table class="table table-striped table-hover item-detail-table font11" id="purchaseTable">
              <thead>
                <tr>
                  <th style="width:240px;" class="text-center purItem">Item name</th>
                  <th style="width:80px;"  class="text-center purItem">HSN / SAC Code</th>                  
                  <th style="width:50px;"  class="text-center">Received<br />qty.</th>
                  <th style="width:50px"   class="text-center">Free<br />qty.</th>
                  <th style="width:50px"   class="text-center">Billed<br />qty.</th>
                  <th style="width:60px"   class="text-center">Packed/<br />Unit</th>                  
                  <th style="width:55px"   class="text-center">MRP<br />( in Rs. )</th>
                  <th style="width:55px"   class="text-center">Rate / Unit<br />( in Rs. )</th>
                  <th style="width:55px"   class="text-center">Gross Amt.<br />( in Rs. )</th>
                  <th style="width:55px"   class="text-center">Discount<br />( in Rs. )</th>                  
                  <th style="width:70px"   class="text-center">Taxable Amt.<br />( in Rs. )</th>
                  <th style="width:70px"   class="text-center">G.S.T<br />( in % )</th>
                </tr>
              </thead>
              <tbody>
              <?php
                $items_total =  $total_tax_amount = $items_tot_after_discount = $total_disc_amount = 0;
                $taxable_values = $taxable_gst_value = [];

                for($i=1;$i<=$total_item_rows;$i++):

                  if( isset($form_data['itemName'][$i-1]) && $form_data['itemName'][$i-1] !== '' ) {
                    $item_name = $form_data['itemName'][$i-1];
                  } else {
                    $item_name = '';
                  }
                  if( isset($form_data['inwardQty'][$i-1]) && $form_data['inwardQty'][$i-1] !== '' ) {
                    $inward_qty = $form_data['inwardQty'][$i-1];
                  } else {
                    $inward_qty = '';
                  }
                  if( isset($form_data['freeQty'][$i-1]) && $form_data['freeQty'][$i-1] !== '' ) {
                    $free_qty = $form_data['freeQty'][$i-1];
                  } else {
                    $free_qty = '';
                  }
                  if( isset($form_data['billedQty'][$i-1]) && $form_data['billedQty'][$i-1] !== '' ) {
                    $billed_qty = $form_data['billedQty'][$i-1];
                  } else {
                    $billed_qty = '';
                  }                  
                  if( isset($form_data['mrp'][$i-1]) && $form_data['mrp'][$i-1] !== '' ) {
                    $mrp = $form_data['mrp'][$i-1];
                  } else {
                    $mrp = '';
                  }
                  if( isset($form_data['itemRate'][$i-1]) && $form_data['itemRate'][$i-1] !== '' ) {
                    $item_rate = $form_data['itemRate'][$i-1];
                  } else {
                    $item_rate = '';
                  }
                  if( isset($form_data['taxPercent'][$i-1]) && $form_data['taxPercent'][$i-1] !== '' ) {
                    $tax_percent = $form_data['taxPercent'][$i-1];
                  } else {
                    $tax_percent = 0;
                  }
                  if( isset($form_data['itemDiscount'][$i-1]) && $form_data['itemDiscount'][$i-1] !== '' ) {
                    $item_discount = $form_data['itemDiscount'][$i-1];
                  } else {
                    $item_discount = '';
                  }
                  if( isset($form_data['hsnCodes'][$i-1]) && $form_data['hsnCodes'][$i-1] !== '' ) {
                    $hsn_code = $form_data['hsnCodes'][$i-1];
                  } else {
                    $hsn_code = '';
                  }
                  if( isset($form_data['packedQty'][$i-1]) && $form_data['packedQty'][$i-1] !== '' ) {
                    $packed_qty = $form_data['packedQty'][$i-1];
                  } else {
                    $packed_qty = '';
                  }                  

                  if(is_numeric($packed_qty) && $packed_qty>0) {
                    $gross_amount = $billed_qty*$item_rate*$packed_qty;
                  } else {
                    $gross_amount = $billed_qty*$item_rate;
                  }
                  
                  $item_amount = $gross_amount - $item_discount;
                  $tax_amount = $item_amount*$tax_percent/100;

                  $items_total += $item_amount;
                  $total_tax_amount += $tax_amount;
                  $total_disc_amount += $item_discount;

                  if(isset($taxable_values[$tax_percent])) {
                    $taxable = $taxable_values[$tax_percent] + $item_amount;
                    $gst_value = $taxable_gst_value[$tax_percent] + $tax_amount;

                    $taxable_values[$tax_percent] = $taxable;
                    $taxable_gst_value[$tax_percent] = $gst_value;
                  } else {
                    $taxable_values[$tax_percent] = $item_amount;
                    $taxable_gst_value[$tax_percent] = $tax_amount;
                  }
              ?>
                <tr class="purchaseItemRow">
                  <td style="width:300px;"><?php echo $item_name ?></td>
                  <td style="width:80px;"><?php echo $hsn_code ?></td>                  
                  <td style="width:50px;" align="right"><?php echo number_format($inward_qty,2,'.','') ?></td>
                  <td style="width:50px;" align="right"><?php echo number_format($free_qty,2,'.','') ?></td>
                  <td style="width:55px;" align="right"><?php echo number_format($billed_qty,2,'.','') ?></td>
                  <td style="width:60px;" align="right"><?php echo number_format($packed_qty,2,'.','') ?></td>                  
                  <td style="width:55px;" align="right"><?php echo number_format($mrp,2,'.','') ?></td>
                  <td style="width:55px;" align="right"><?php echo number_format($item_rate,2,'.','') ?></td>
                  <td style="width:80px;" align="right"><?php echo number_format(round($gross_amount,2),2,'.','') ?></td>
                  <td style="width:80px;" align="right"><?php echo number_format($item_discount,2,'.','') ?></td>
                  <td style="width:70px;" align="right"><?php echo number_format(round($item_amount,2),2,'.','') ?></td>
                  <td style="width:80px;" align="right"><?php echo number_format($tax_percent, 2, '.', '') ?></td>
                </tr>
              <?php 
                endfor;
                $items_tot_after_discount = round($items_total - $total_disc_amount, 2);
                $grand_total = round($items_tot_after_discount + $total_tax_amount, 2);
                $round_off = round($grand_total) - $grand_total;
                $net_pay = round($grand_total);
              ?>
                <tr>
                  <td colspan="11" align="right" style="vertical-align:middle;font-weight:bold;font-size:14px;">Total Taxable Value</td>
                  <td id="inwItemsTotal" align="right" style="vertical-align:middle;font-weight:bold;font-size:14px;"><?php echo number_format(round($items_total, 2), 2, '.','') ?></td>
                </tr>
                <tr>
                  <td colspan="11" align="right" style="vertical-align:middle;font-weight:bold;font-size:14px;">(+) G.S.T</td>
                  <td align="right" id="inwItemTaxAmount" class="taxAmounts" style="vertical-align:middle;font-weight:bold;font-size:14px;"><?php echo number_format(round($total_tax_amount, 2), 2, '.','') ?></td>
                </tr>
                <tr>
                  <td style="vertical-align:middle;font-weight:bold;font-size:14px;" colspan="11" align="right">(+ or -) Round off</td>
                  <td style="vertical-align:middle;text-align:right;font-size:14px;" id="roundOff"><?php echo number_format($round_off,2,'.','') ?></td>
                </tr>
                <tr>
                  <td style="vertical-align:middle;font-weight:bold;font-size:14px;" colspan="11" align="right">Total Amount</td>
                  <td style="vertical-align:middle;text-align:right;font-size:18px;" id="inwNetPay"><?php echo number_format(round($net_pay,2),2,'.','') ?></td>
                </tr>
                <tr>
                  <td style="vertical-align:middle;font-weight:bold;" align="center">Notes / Comments</td>
                  <td style="vertical-align:middle;text-align:right;" colspan="11"><?php echo $remarks ?></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="panel" style="margin-bottom:10px;">
            <div class="panel-body" style="border: 1px dotted;">
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">PO Status</label>
                  <div class="select-wrap">                        
                    <select 
                      class="form-control"
                      id="arStatus"
                      name="arStatus"
                    >
                      <?php
                        foreach($inward_status_a as $key=>$value):
                          if((int)$key === (int)$inward_status) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                        <option value="<?php echo $key ?>" <?php echo $selected ?>>
                          <?php echo $value ?>
                        </option>
                      <?php endforeach; ?>                            
                    </select>
                  </div>
                  <?php if(isset($errors['arStatus'])): ?>
                    <span class="error"><?php echo $errors['arStatus'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-8 col-lg-8">
                  <label class="control-label">Approval / Rejected Comments (not more than 300 characters)</label>
                  <textarea name="arRemarks" id="arRemarks" class="form-control noEnterKey" maxlength="300"></textarea>
                  <?php if(isset($errors['arRemarks'])): ?>
                    <span class="error"><?php echo $errors['arRemarks'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-danger" id="inwCancel">
              <i class="fa fa-times"></i> Cancel
            </button>
            <button class="btn btn-primary" id="inwSave">
              <i class="fa fa-save"></i> Save
            </button>
          </div>
          <input type="hidden" name="pc" id="pc" value="<?php echo $po_code ?>" />
          <input type="hidden" name="pn" id="pn" value="<?php echo $po_number ?>" />          
        </form>
      </div>
    </section>
  </div>
</div>

<?php /*

                <tr>
                  <td colspan="12" style="text-align:center;font-weight:bold;font-size:16px;">GST Summary</td>
                </tr>
                <tr style="padding:0px;margin:0px;">
                  <td colspan="12" style="padding:0px;margin:0px;">
                    <table class="table table-striped table-hover font12 valign-middle">
                      <thead>
                        <th style="text-align:center;">GST Rate (in %)</th>
                        <th style="text-align:right;">Taxable Amount (in Rs.)</th>
                        <th style="text-align:right;">IGST (in Rs.)</th>
                        <th style="text-align:right;">CGST (in Rs.)</th>
                        <th style="text-align:right;">SGST (in Rs.)</th>
                      </thead>
                      <tbody>
                      <?php
                        $tot_taxable_value = $tot_igst_amount = $tot_cgst_amount = $tot_sgst_amount = 0;                        
                        foreach($taxes as $tax_code => $tax_percent):
                          if( isset($taxable_values[$tax_percent]) ) {
                            $taxable_value = $taxable_values[$tax_percent];
                            $tot_taxable_value += $taxable_value;
                          } else {
                            $taxable_value = 0;
                          }
                          if(isset($taxable_gst_value[$tax_percent])) {
                            if($supply_type === 'inter') {
                              $cgst_amount = $sgst_amount = 0;
                              $igst_amount = $taxable_gst_value[$tax_percent];
                              $tot_igst_amount += $igst_amount;
                            } else {
                              $cgst_amount = $sgst_amount = round($taxable_gst_value[$tax_percent]/2,2);
                              $igst_amount = 0;
                              $tot_cgst_amount += $cgst_amount;
                              $tot_sgst_amount += $sgst_amount;                              
                            }
                          } else {
                            $cgst_amount = $sgst_amount = $igst_amount = 0;
                          }                          
                      ?>
                        <tr>
                            <input type="hidden" value="<?php echo $tax_percent ?>" class="inwTaxPercents" id="<?php echo $tax_code ?>" />
                            <input type="hidden" value="" id="taxAmount_<?php echo $tax_code ?>" class="taxAmounts" />
                            <td class="font11" style="text-align:right;font-weight:bold;"><?php echo number_format($tax_percent, 2).' %' ?></td>
                            <td class="font11" style="text-align:right;font-weight:bold;" id="taxable_<?php echo $tax_code ?>_amount"><?php echo number_format($taxable_value,2) ?></td>
                            <td class="font11" style="text-align:right;font-weight:bold;" id="taxable_<?php echo $tax_code ?>_igst_value"><?php echo number_format($igst_amount,2)  ?></td>
                            <td class="font11" style="text-align:right;font-weight:bold;" id="taxable_<?php echo $tax_code ?>_cgst_value"><?php echo number_format($cgst_amount,2) ?></td>
                            <td class="font11" style="text-align:right;font-weight:bold;" id="taxable_<?php echo $tax_code ?>_sgst_value"><?php echo number_format($sgst_amount,2) ?></td>
                        </tr>
                      <?php endforeach; ?>
                      </tbody>
                    </table>
                  </td>
                </tr>*/ ?>