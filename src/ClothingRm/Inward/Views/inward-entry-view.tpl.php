<?php

  // dump($form_data, $form_errors);
  // dump($form_errors);
  // dump($form_data);
  // exit;

  if(isset($form_data['purchaseDate'])) {
    $purchase_date = date("d-m-Y", strtotime($form_data['purchaseDate']));
  } else {
    $purchase_date = date("d-m-Y");
  }
  if(isset($form_data['creditDays'])) {
    $creditDays = $form_data['creditDays'];
  } else {
    $creditDays = 0;
  }
  if(isset($form_data['paymentMethod'])) {
    $paymentMethod = $form_data['paymentMethod'];
  } else {
    $paymentMethod = 0;
  }
  if(isset($form_data['supplierID'])) {
    $supplierCode = $form_data['supplierID'];
  } elseif(isset($form_data['supplierCode'])) {
    $supplierCode = $form_data['supplierCode'];
  } else {
    $supplierCode = '';
  }
  if( isset($form_data['discountPercent']) ) {
    $discount_percent = $form_data['discountPercent'];
  } else {
    $discount_percent = 0;
  }
  if(isset($form_data['otherTaxes'])) {
    $other_taxes = $form_data['otherTaxes'];
  } else {
    $other_taxes = 0;
  }
  if(isset($form_data['adjustment'])) {
    $adjustment = $form_data['adjustment'];
  } else {
    $adjustment = 0;
  }
  if(isset($form_data['shippingCharges'])) {
    $shipping_charges = $form_data['shippingCharges'];
  } else {
    $shipping_charges = 0;
  }
  if(isset($form_data['roundOff'])) {
    $round_off = $form_data['roundOff'];
  } else {
    $round_off = 0;
  }
  if(isset($form_data['poNo'])) {
    $po_no = $form_data['poNo'];
  } else {
    $po_no = '';
  }
  if(isset($form_data['indentNo'])) {
    $indent_no = $form_data['indentNo'];
  } else {
    $indent_no = '';
  }
  if(isset($form_data['remarks'])) {
    $remarks = $form_data['remarks'];
  } else {
    $remarks = '';
  }
  if((int)$form_data['supplierStateID'] === (int)$client_business_state) {
    $supply_type = 'inter';
  } else {
    $supply_type = 'intra';
  }
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $utilities->print_flash_message() ?>
        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php endif; ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/inward-entry/list" class="btn btn-default">
              <i class="fa fa-book"></i> Purchase Register
            </a>
          </div>
        </div>

        <form class="form-validate form-horizontal" method="POST">
          <div class="panel">
            <div class="panel-body">
              <h2 class="hdg-reports borderBottom">Transaction Details</h2>
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Date of purchase (dd-mm-yyyy)</label>
                  <div class="form-group">
                    <div class="col-lg-12">
                      <div class="input-append date" data-date="<?php echo $purchase_date ?>" data-date-format="dd-mm-yyyy">
                        <input class="span2" value="<?php echo $purchase_date ?>" size="16" type="text" name="purchaseDate" id="purchaseDate" disabled />
                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                      </div>                  
                    </div>
                  </div>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Supplier name</label>
                  <div class="select-wrap">
                    <select class="form-control" name="supplierID" id="supplierID" disabled>
                      <?php 
                        foreach($suppliers as $key=>$value): 
                            if($supplierCode === $key) {
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
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Indent number</label>
                  <input 
                    type="text" 
                    class="form-control noEnterKey" 
                    name="indentNo" 
                    id="indentNo" 
                    value="<?php echo $indent_no ?>"
                    disabled
                  >
                </div>           
              </div>
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Purchaser order (PO) No.</label>
                  <input 
                    type="text" 
                    class="form-control noEnterKey" 
                    name="poNo" 
                    id="poNo" 
                    value="<?php echo $po_no ?>"
                    disabled
                  >            
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Payment method</label>
                  <div class="select-wrap">
                    <select class="form-control" name="paymentMethod" id="paymentMethod" disabled>
                      <?php 
                        foreach($payment_methods as $key=>$value): 
                          if((int)$paymentMethod === (int)$key) {
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
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                    <label class="control-label">Credit period (in days)</label>
                    <div class="select-wrap">
                      <select class="form-control" name="creditDays" id="creditDays" disabled>
                        <?php 
                          foreach($credit_days_a as $key=>$value):
                            if((int)$creditDays === $key) {
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
                  </div>
              </div>
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Supplier location</label>
                  <div class="select-wrap">
                    <select class="form-control" name="supplierState" id="supplierState" disabled>
                      <?php 
                        foreach($states_a as $key=>$value): 
                          if((int)$form_data['supplierStateID'] === (int)$key) {
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
                    <?php if(isset($form_errors['supplierState'])): ?>
                      <span class="error"><?php echo $form_errors['supplierState'] ?></span>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Supplier GST No.</label>
                  <input 
                    type="text" 
                    class="form-control noEnterKey" 
                    name="supplierGSTNo"
                    id="supplierGSTNo" 
                    value="<?php echo $form_data['supplierGSTNo'] ?>"
                    disabled
                  >
                  <?php if(isset($form_errors['supplierGSTNo'])): ?>
                      <span class="error"><?php echo $form_errors['supplierGSTNo'] ?></span>
                  <?php endif; ?>              
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                    <label class="control-label">Supply type</label>
                    <div class="select-wrap">
                      <select class="form-control" name="supplyType" id="supplyType" disabled>
                        <?php 
                          foreach($supply_type_a as $key=>$value):
                            if($supply_type === $key) {
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
                      <?php if(isset($form_errors['supplyType'])): ?>
                        <span class="error"><?php echo $form_errors['supplyType'] ?></span>
                      <?php endif; ?>
                    </div>
                </div>
              </div>
            </div>
          </div>
          <h2 class="hdg-reports">Item Details</h2>
          <?php if(isset($form_errors['itemDetailsError'])): ?>
            <span class="error"><?php echo $form_errors['itemDetailsError'] ?></span>
          <?php endif; ?>          
          <div class="table-responsive">
            <table class="table table-striped table-hover item-detail-table font12" id="purchaseTable">
              <thead>
                <tr>
                  <th style="width:220px;" class="text-center purItem">Item name</th>
                  <th style="width:60px;" class="text-center purItem">HSN / SAC Code</th>                  
                  <th style="width:50px;" class="text-center">Received<br />qty.</th>
                  <th style="width:50px" class="text-center">Free<br />qty.</th>
                  <th style="width:50px" class="text-center">Billed<br />qty.</th>
                  <th style="width:50px" class="text-center">Packed<br />qty.</th>                                   
                  <th style="width:50px" class="text-center">Lot no.</th>
                  <th style="width:55px" class="text-center">MRP<br />( in Rs. )</th>
                  <th style="width:55px" class="text-center">Rate / Unit<br />( in Rs. )</th>
                  <th style="width:55px" class="text-center">Gross Amt.<br />( in Rs. )</th>
                  <th style="width:55px" class="text-center">Discount<br />( in Rs. )</th>                  
                  <th style="width:70px" class="text-center">Taxable Amt.<br />( in Rs. )</th>
                  <th style="width:50px" class="text-center">G.S.T<br />( in % )</th>
                </tr>
              </thead>
              <tbody>
              <?php
                $items_total =  $total_tax_amount = $items_tot_after_discount = 0;
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
                  if( isset($form_data['lotNo'][$i-1]) &&  $form_data['lotNo'][$i-1] !== '' ) {
                    $lot_no = $form_data['lotNo'][$i-1];
                  } else {
                    $lot_no = '';
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

                  $billed_qty = $inward_qty-$free_qty;

                  if(is_numeric($packed_qty) && $packed_qty>0) {
                    $gross_amount = $billed_qty*$item_rate*$packed_qty;
                  } else {
                    $gross_amount = $billed_qty*$item_rate;
                  }
                                    
                  $item_amount = $gross_amount-$item_discount;
                  $tax_amount = round($item_amount*$tax_percent/100,2);

                  $items_total += $item_amount;
                  $total_tax_amount += $tax_amount;

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
                  <td style="width:180px;"><?php echo $item_name ?></td>
                  <td style="width:80px;text-align:right;"><?php echo $hsn_code ?></td>                  
                  <td style="width:50px;text-align:right;"><?php echo number_format($inward_qty,2) ?></td>
                  <td style="width:50px;text-align:right;"><?php echo number_format($free_qty,2) ?></td>
                  <td style="width:55px;text-align:right;"><?php echo number_format($billed_qty,2) ?></td>
                  <td style="width:55px;text-align:right;"><?php echo number_format($packed_qty,2) ?></td>                  
                  <td style="width:50px;text-align:right;"><?php echo $lot_no ?></td>
                  <td style="width:55px;text-align:right;"><?php echo number_format($mrp,2) ?></td>
                  <td style="width:55px;text-align:right;"><?php echo number_format($item_rate,2) ?></td>
                  <td style="width:80px;text-align:right;"><?php echo number_format(round($gross_amount,2),2) ?></td>
                  <td style="width:80px;text-align:right;"><?php echo number_format(round($item_discount,2),2) ?></td>
                  <td style="width:70px;text-align:right;"><?php echo number_format(round($item_amount,2),2) ?></td>
                  <td style="width:80px;text-align:right;"><?php echo number_format(round((float)$tax_percent,2),2) ?></td>
                </tr>
              <?php 
                endfor;

                // dump($taxable_values, $taxable_gst_value);

/*                $items_tot_after_discount = $items_total-($items_total*$discount_percent)/100;
                $grand_total = $items_tot_after_discount+$total_tax_amount+
                               $other_taxes+$shipping_charges;

                $net_pay = $grand_total+$adjustment+$round_off;*/
                $grand_total = round($items_total + $total_tax_amount, 2);
                
                $round_off = round($grand_total) - $grand_total;
                $net_pay = round($grand_total);                
              ?>
                <tr>
                  <td colspan="12" align="right" style="vertical-align:middle;font-weight:bold;font-size:14px;">Total Taxable Value</td>
                  <td id="inwItemsTotal" align="right" style="vertical-align:middle;font-weight:bold;font-size:14px;"><?php echo number_format(round($items_total, 2),2) ?></td>
                </tr>
                <tr>
                  <td colspan="12" align="right" style="vertical-align:middle;font-weight:bold;font-size:14px;">(+) G.S.T</td>
                  <td align="right" id="inwItemTaxAmount" class="taxAmounts" style="vertical-align:middle;font-weight:bold;font-size:14px;"><?php echo number_format(round($total_tax_amount,2),2) ?></td>
                </tr>
                <tr>
                  <td style="vertical-align:middle;font-weight:bold;font-size:14px;" colspan="12" align="right">(+ or -) Round off</td>
                  <td style="vertical-align:middle;text-align:right;font-size:14px;" id="roundOff"><?php echo round($round_off,2) ?></td>
                </tr>
                <tr>
                  <td style="vertical-align:middle;font-weight:bold;font-size:14px;" colspan="12" align="right">Total Amount</td>
                  <td style="vertical-align:middle;text-align:right;font-size:18px;" id="inwNetPay"><?php echo number_format(round($net_pay,2),2) ?></td>
                </tr>
                <tr>
                  <td style="vertical-align:middle;font-weight:bold;" align="center">Notes / Comments</td>
                  <td style="vertical-align:middle;text-align:right;" colspan="12">
                    <textarea
                      class="form-control noEnterKey"
                      rows="3"
                      cols="100"
                      id="inwRemarks"
                      name="remarks"
                    ><?php echo $remarks ?></textarea>
                  </td>
                </tr>
                <tr>
                  <td colspan="12" style="text-align:center;font-weight:bold;font-size:16px;">GST Summary</td>
                </tr>
                <tr style="padding:0px;margin:0px;">
                  <td colspan="13" style="padding:0px;margin:0px;">
                    <table class="table table-striped table-hover font11 valign-middle">
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
                              $cgst_amount = $sgst_amount = round($taxable_gst_value[$tax_percent]/2,2);
                              $igst_amount = 0;
                              $tot_cgst_amount += $cgst_amount;
                              $tot_sgst_amount += $sgst_amount;
                            } else {
                              $cgst_amount = $sgst_amount = 0;
                              $igst_amount = $taxable_gst_value[$tax_percent];
                              $tot_igst_amount += $igst_amount;
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
                        <tr>
                            <td class="font11" style="text-align:right;font-weight:bold;font-size:12px;">TOTALS</td>
                            <td class="font11" style="text-align:right;font-weight:bold;font-size:12px;" id="taxable_<?php echo $tax_code ?>_amount"><?php echo number_format($tot_taxable_value,2) ?></td>
                            <td class="font11" style="text-align:right;font-weight:bold;font-size:12px;" id="taxable_<?php echo $tax_code ?>_igst_value"><?php echo number_format($tot_igst_amount,2)  ?></td>
                            <td class="font11" style="text-align:right;font-weight:bold;font-size:12px;" id="taxable_<?php echo $tax_code ?>_cgst_value"><?php echo number_format($tot_cgst_amount,2) ?></td>
                            <td class="font11" style="text-align:right;font-weight:bold;font-size:12px;" id="taxable_<?php echo $tax_code ?>_sgst_value"><?php echo number_format($tot_sgst_amount,2) ?></td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>

              </tbody>
            </table>
          </div>
        </form>

      </div>
    </section>
  </div>
</div>