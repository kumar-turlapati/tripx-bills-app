<?php

  // dump($form_data);
  // dump($errors);
  // dump($offers_raw);
  // exit;

  // dump($print_format, $bill_to_print);

  if(isset($form_data['locationCode'])) {
    $location_code = $form_data['locationCode'];
  } elseif($default_location !== '') {
    $location_code = $default_location;
  } else {
    $location_code = '';
  }
  if(isset($form_data['invoiceDate']) && $form_data['invoiceDate'] !== '') {
    $current_date = date("d-m-Y", strtotime($form_data['invoiceDate']));
  } elseif(isset($form_data['saleDate']) && $form_data['saleDate'] !== '') {
    $current_date = date("d-m-Y", strtotime($form_data['saleDate']));
  } else {
    $current_date = date("d-m-Y");
  }
  if(isset($form_data['name']) && $form_data['name'] !== '') {
    $customer_name = $form_data['name'];
  } elseif( isset($form_data['customerName']) && $form_data['customerName'] !== '') {
    $customer_name = $form_data['customerName'];
  } elseif( isset($form_data['tmpCustName']) && $form_data['tmpCustName'] !== '') {
    $customer_name = $form_data['tmpCustName'];    
  } else {
    $customer_name = '';
  }

  $payment_method = isset($form_data['paymentMethod']) ? $form_data['paymentMethod'] : $payment_method = 0;
  $discount_method = isset($form_data['discountMethod']) ? $form_data['discountMethod'] : $discount_method = 0;
  $mobile_no = isset($form_data['mobileNo']) ? $form_data['mobileNo'] : '';
  $card_number = isset($form_data['cardNo']) ? $form_data['cardNo'] : ''; 
  $card_auth_code = isset($form_data['authCode']) ? $form_data['authCode'] : '';
  $coupon_code = isset($form_data['couponCode']) ? $form_data['couponCode'] : '';
  $tax_calc_option = isset($form_data['taxCalcOption']) ? $form_data['taxCalcOption'] : 'i';
  $executive_id = isset($form_data['saExecutive']) ? $form_data['saExecutive'] : '';
  $cn_no = isset($form_data['cnNo']) ? $form_data['cnNo'] : '';
  $promo_code = isset($form_data['promoCode']) ? $form_data['promoCode'] : '';
  $referral_code = isset($form_data['refCode']) ? $form_data['refCode'] : '';
  $customer_type = isset($form_data['customerType']) ? $form_data['customerType'] : 'b2c';
  $credit_days = isset($form_data['saCreditDays']) ? $form_data['saCreditDays'] : '';

  if(isset($form_data['splitPaymentCash']) && $form_data['splitPaymentCash']>0) {
    $split_payment_cash =  $form_data['splitPaymentCash'];
  } elseif(isset($form_data['netPayCash']) && $form_data['netPayCash']>0) {
    $split_payment_cash = $form_data['netPayCash'];
  } else {
    $split_payment_cash = '';
  }
  if(isset($form_data['splitPaymentCard']) && $form_data['splitPaymentCard']>0) {
    $split_payment_card =  $form_data['splitPaymentCard'];
  } elseif(isset($form_data['netPayCard']) && $form_data['netPayCard']>0) {
    $split_payment_card = $form_data['netPayCard'];
  } else {
    $split_payment_card = '';
  }
  if(isset($form_data['splitPaymentCn']) && $form_data['splitPaymentCn']>0) {
    $split_payment_cn =  $form_data['splitPaymentCn'];
  } elseif(isset($form_data['netPayCn']) && $form_data['netPayCn']>0) {
    $split_payment_cn = $form_data['netPayCn'];
  } else {
    $split_payment_cn = '';
  }  

  $packing_charges = isset($form_data['packingCharges']) ? $form_data['packingCharges'] : '';
  $shipping_charges = isset($form_data['shippingCharges']) ? $form_data['shippingCharges'] : '';
  $insurance_charges = isset($form_data['insuranceCharges']) ? $form_data['shippingCharges'] : '';
  $other_charges = isset($form_data['otherCharges']) ? $form_data['otherCharges'] : '';
  $transporter_name = isset($form_data['transporterName']) ? $form_data['transporterName'] : '';
  $lr_no = isset($form_data['lrNo']) ? $form_data['lrNo'] : '';
  $lr_date = isset($form_data['lrDate']) ? $form_data['lrDate'] : '';
  $challan_no = isset($form_data['challanNo']) ? $form_data['challanNo'] : '';

  $card_and_auth_style = (int)$payment_method === 0 || (int)$payment_method === 3 ? 'style="display:none;"' : '';
  $split_payment_input_style = (int)$payment_method === 2 ? '' : 'disabled';
  $credit_days_input_style = (int)$payment_method === 3 ? '' : 'style="display:none;"';
  $coupon_code_input_style = (int)$discount_method === 1 ? '' : 'disabled';

  $form_submit_url = '/sales/entry';
?>

<div class="row">
  <div class="col-lg-12"> 
    
    <section class="panelBox">
      <div class="panelBody">

        <?php echo $flash_obj->print_flash_message(); ?>
       
        <div class="global-links actionButtons clearfix"> 
          <div class="pull-right text-right">
            <a href="/sales/list" class="btn btn-default"><i class="fa fa-book"></i> Sales Register</a>&nbsp;&nbsp;
            <a href="/sales/entry-with-barcode" class="btn btn-default"><i class="fa fa-inr"></i> Sales Entry with Barcode</a>            
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="outwardEntryForm" action="<?php echo $form_submit_url ?>">
          <div class="table-responsive">
            <table class="table table-striped table-hover font12">
              <thead>
                <tr>
                  <th width="5%"  class="text-center">Sno.</th>                  
                  <th width="18%" class="text-center">Item name</th>
                  <th width="12%" class="text-center">Lot no.</th>
                  <th width="5%"  class="text-center">Available<br />qty.</th>
                  <th width="11%"  class="text-center">Ordered<br />qty.</th>
                  <th width="8%" class="text-center">M.R.P<br />( in Rs. )</th>
                  <th width="10%" class="text-center">Gross Amt.<br />( in Rs. )</th>
                  <th width="8%" class="text-center">Discount<br />( in Rs. )</th>                  
                  <th width="8%" class="text-center">Taxable Amt.<br />( in Rs. )</th>
                  <th width="10%" class="text-center">GST<br />( in % )</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $tot_item_amount = $tot_taxable_amount = $tot_tax_amount = $tot_discount = 0;
                  $tot_bill_qty = 0;
                  for($i=1; $i<=$no_of_rows; $i++):
                    $ex_index = $i-1;
                    if(isset($form_data['itemDetails'])) {
                      $item_name = isset($form_data['itemDetails']['itemName'][$ex_index]) ? $form_data['itemDetails']['itemName'][$ex_index] : '';
                      $item_qty_available = isset($form_data['itemDetails']['itemAvailQty'][$ex_index]) ? $form_data['itemDetails']['itemAvailQty'][$ex_index] : 0;
                      $item_qty = isset($form_data['itemDetails']['itemSoldQty'][$ex_index]) ? $form_data['itemDetails']['itemSoldQty'][$ex_index] : 0;
                      $item_rate = isset($form_data['itemDetails']['itemRate'][$ex_index]) ? $form_data['itemDetails']['itemRate'][$ex_index] : '';
                      $item_discount = isset($form_data['itemDetails']['itemDiscount'][$ex_index]) ? $form_data['itemDetails']['itemDiscount'][$ex_index] : '';
                      $tax_percent = isset($form_data['itemDetails']['itemTaxPercent'][$ex_index]) ? $form_data['itemDetails']['itemTaxPercent'][$ex_index] : '';
                      $lot_no = isset($form_data['itemDetails']['lotNo'][$ex_index]) ? $form_data['itemDetails']['lotNo'][$ex_index] : '';
                    } else {
                      $item_name = '';
                      $lot_no = '';
                      $item_qty_available = 0;
                      $item_qty = 0;
                      $item_rate = 0;
                      $item_discount = 0;
                      $tax_percent = 0;      
                    }

                    if($item_qty > 0 && $item_rate > 0) {

                      $item_amount = $item_qty * $item_rate;
                      $taxable_amount = $item_amount - $item_discount;
                      $tax_amount = round(($taxable_amount*$tax_percent)/100, 2);

                      $tot_item_amount += $item_amount;
                      $tot_taxable_amount += $taxable_amount;
                      $tot_tax_amount += $tax_amount;
                      $tot_bill_qty += $item_qty;
                      $tot_discount += $item_discount;

                    } else {
                      $item_amount = '';
                      $taxable_amount = '';
                      $tax_amount = '';
                    }
                ?>
                  <tr>
                    <td align="right" style="vertical-align:middle;"><?php echo $i ?></td>
                    <td style="vertical-align:middle;"><?php echo $item_name ?></td>
                    <td style="vertical-align:middle;"><?php echo $lot_no ?></td>                
                    <td style="vertical-align:middle;text-align:right;"><?php echo number_format($item_qty_available,2,'.','') ?></td>
                    <td style="vertical-align:middle;text-align:right;"><?php echo number_format($item_qty,2,'.','') ?></td>
                    <td style="vertical-align:middle;text-align:right;"><?php echo number_format($item_rate,2,'.','') ?></td>
                    <td 
                      class="grossAmount" 
                      id="grossAmount_<?php echo $i-1 ?>" 
                      index="<?php echo $i-1 ?>"
                      style="vertical-align:middle;text-align:right;"
                    ><?php echo number_format($item_amount,2,'.','') ?></td>
                    <td style="vertical-align:middle;text-align:right;"><?php echo $item_discount ?></td>
                    <td
                      class="taxableAmt text-right"
                      id="taxableAmt_<?php echo $i-1 ?>"
                      index="<?php echo $i-1 ?>"
                      style="vertical-align:middle;text-align:right;"                      
                    ><?php echo number_format($taxable_amount,2,'.','') ?></td>
                    <td style="vertical-align:middle;text-align:right;"><?php echo $tax_percent ?></td>
                  </tr>
                  <?php
                    /* Show error tr if there are any errors in the line item */
                    if( isset($errors['itemDetails']['itemName'][$i-1]) ) {
                  ?>
                      <tr>
                        <td style="border:none;">&nbsp;</td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemName'][$i-1]) ? '<span class="error">Invalid</span>': '' ?></td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemAvailQty'][$i-1]) ? '<span class="error">Invalid</span>': '' ?></td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemSoldQty'][$i-1]) ? '<span class="error">Invalid</span>': '' ?></td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemRate'][$i-1]) ? '<span class="error">Invalid</span>': '' ?></td>
                        <td style="border:none;">&nbsp;</td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemDiscount'][$i-1]) ? '<span class="error">Invalid</span>': '' ?></td>
                        <td style="border:none;">&nbsp;</td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemTaxPercent'][$i-1]) ? '<span class="error">Invalid</span>': '' ?></td>
                      </tr>
                  <?php } ?>
                <?php endfor; ?>
                <?php 
                  if($tax_calc_option === 'i') {
                    $tot_tax_amount = 0;
                  }
                  $net_pay_actual = $tot_taxable_amount + $tot_tax_amount;
                  $rounded_off = round($net_pay_actual,0) - $net_pay_actual;
                  $net_pay = round($net_pay_actual,0);
                ?>
                  <tr>
                    <td colspan="4" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Total Bill Qty.</td>
                    <td id="totalItems" name="totalItems" style="vertical-align:middle;font-weight:bold;font-size:18px;text-align:right;"><?php echo $tot_bill_qty > 0 ? $tot_bill_qty : ''  ?></td>
                    <td colspan="4" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Gross Amount</td>
                    <td id="grossAmount" class="" style="font-size:16px;text-align:right;font-weight:bold;"><?php echo $tot_item_amount > 0 ? number_format($tot_item_amount, 2, '.', '') : '' ?></td>
                  </tr>
                  <tr>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;">Discount (in Rs.)</td>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;">Taxable amount (in Rs.)</td>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;">GST (in Rs.)</td>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;">Round off (in Rs.)</td>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;">Net pay (in Rs.)</td>
                  </tr>
                  <tr>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" id="totDiscount"><?php echo $tot_discount > 0 ? number_format($tot_discount, 2, '.', '') : '' ?></td>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" id="taxableAmount" class="taxableAmount"><?php echo $tot_taxable_amount > 0 ? number_format($tot_taxable_amount, 2, '.', '') : '' ?></td>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" id="gstAmount" class="gstAmount"><?php echo $tot_tax_amount >0 ? number_format($tot_tax_amount, 2, '.', '') : '<span style="color:green;font-weight:bold;">Inclusive</span>' ?></td>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" id="roundOff" class="roundOff"><?php echo $rounded_off !== '' ? number_format($rounded_off, 2, '.', '') : '' ?></td>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" id="netPayBottom" class="netPay"><?php echo $net_pay > 0 ? number_format($net_pay, 2, '.', '') : '' ?></td>
                  </tr>
                  <tr>
                    <td colspan="10" align="right" style="vertical-align:middle;">
                      <span style="padding-right:5px;font-weight:bold;font-size:14px;">Credit Note No.</span>
                      <span style="padding-right:40px;">
                        <input
                          type="text"
                          size="10"
                          id="cnNo"
                          name="cnNo"
                          maxlength="8"
                          style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                          <?php echo $split_payment_input_style ?>
                          value="<?php echo $cn_no ?>"
                          disabled
                        />
                      </span>            
                      <span style="padding-right:5px;font-weight:bold;font-size:14px;">Credit Note Value</span>
                      <span style="padding-right:40px;">
                        <input
                          type="text"
                          size="15"
                          id="splitPaymentCn"
                          name="splitPaymentCn"
                          style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                          <?php echo $split_payment_input_style ?>
                          value="<?php echo $split_payment_cn ?>"
                          disabled
                        />
                      </span>
                      <span style="padding-right:5px;font-weight:bold;font-size:14px;">Cash Value</span>
                      <span style="padding-right:40px;">
                        <input
                          type="text"
                          size="15"
                          id="splitPaymentCash"
                          name="splitPaymentCash"
                          style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                          <?php echo $split_payment_input_style ?>
                          value="<?php echo $split_payment_cash ?>"
                          disabled
                        />
                      </span>
                      <span style="padding-right:5px;font-weight:bold;font-size:14px;">Card Value</span>
                      <span style="padding-right:40px;">
                        <input 
                          type="text"
                          size="15"
                          id="splitPaymentCard"
                          name="splitPaymentCard"
                          style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                          <?php echo $split_payment_input_style ?>
                          value="<?php echo $split_payment_card ?>"
                          disabled
                        />
                      </span>
                      <span style="padding-right:5px;font-weight:bold;font-size:14px;display:none;">Net Pay</span>
                      <span>
                        <input
                          type="hidden"
                          size="20"
                          id="netPayTop"
                          name="netPayTop"
                          class="netPay"
                          style="font-weight:bold;font-size:14px;padding-left:5px;background-color:#f1f442;border:1px dashed;"
                          disabled
                        />
                      </span>
                    </td>
                  </tr>
              </tbody>
            </table>
            <input type="hidden" name="promoKey" id="promoKey" value="<?php echo $promo_key ?>" />
          </div>
          <div class="panel" style="margin-bottom:10px;<?php echo $customer_type === 'b2b' ? '' : 'display:none;' ?>" id="siOtherInfoWindow">
            <div class="panel-body" style="border: 1px dotted;">
              <div class="form-group">
                <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
                  <label class="control-label">Packing charges (in Rs.)</label>
                  <input
                    type="text"
                    size="10"
                    id="packingCharges"
                    name="packingCharges"
                    maxlength="10"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    value="<?php echo $packing_charges ?>"
                    class="form-control"
                    disabled
                  />
                  <?php if(isset($errors['packingCharges'])): ?>
                    <span class="error"><?php echo $errors['packingCharges'] ?></span>
                  <?php endif; ?>                  
                </div>                
                <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
                  <label class="control-label">Shipping charges (in Rs.)</label>
                  <input
                    type="text"
                    size="10"
                    id="shippingCharges"
                    name="shippingCharges"
                    maxlength="10"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    value="<?php echo $shipping_charges ?>"
                    class="form-control"
                    disabled
                  />
                  <?php if(isset($errors['shippingCharges'])): ?>
                    <span class="error"><?php echo $errors['shippingCharges'] ?></span>
                  <?php endif; ?>                  
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
                  <label class="control-label">Insurance charges (in Rs.)</label>
                  <input
                    type="text"
                    size="15"
                    id="insuranceCharges"
                    name="insuranceCharges"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    value="<?php echo $insurance_charges ?>"
                    class="form-control"
                    disabled                
                  />
                  <?php if(isset($errors['insuranceCharges'])): ?>
                    <span class="error"><?php echo $errors['insuranceCharges'] ?></span>
                  <?php endif; ?>                   
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
                  <label class="control-label">Other charges (in Rs.)</label>
                  <input
                    type="text"
                    size="15"
                    id="otherCharges"
                    name="otherCharges"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    value="<?php echo $other_charges ?>"
                    class="form-control"
                    disabled             
                  />
                  <?php if(isset($errors['otherCharges'])): ?>
                    <span class="error"><?php echo $errors['otherCharges'] ?></span>
                  <?php endif; ?>                   
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
                  <label class="control-label">Transporter name</label>
                  <input 
                    type="text"
                    size="15"
                    id="transporterName"
                    name="transporterName"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    value="<?php echo $transporter_name ?>"
                    class="form-control"
                    disabled
                  />
                  <?php if(isset($errors['transporterName'])): ?>
                    <span class="error"><?php echo $errors['transporterName'] ?></span>
                  <?php endif; ?>                  
                </div>                
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label">L.R. No(s)</label>
                  <input
                    type="text"
                    size="10"
                    id="lrNos"
                    name="lrNos"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    value="<?php echo $lr_no ?>"
                    class="form-control"
                    disabled
                  />
                  <?php if(isset($errors['lrNo'])): ?>
                    <span class="error"><?php echo $errors['lrNo'] ?></span>
                  <?php endif; ?>                  
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label">L.R. Date</label>
                  <input
                    type="text"
                    size="15"
                    id="lrDate"
                    name="lrDate"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    value="<?php echo $insurance_charges ?>"
                    class="form-control"
                    disabled        
                  />
                  <?php if(isset($errors['lrDate'])): ?>
                    <span class="error"><?php echo $errors['lrDate'] ?></span>
                  <?php endif; ?>                   
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label">Challan no.</label>
                  <input
                    type="text"
                    size="15"
                    id="challanNo"
                    name="challanNo"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    value="<?php echo $challan_no ?>"
                    class="form-control"           
                    disabled        
                  />
                  <?php if(isset($errors['challanNo'])): ?>
                    <span class="error"><?php echo $errors['challanNo'] ?></span>
                  <?php endif; ?>                   
                </div>
              </div>
            </div>
          </div>          
          <div class="panel" style="margin-bottom:10px;">
            <div class="panel-body" style="border: 2px dotted;">
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Payment method</label>
                  <div class="select-wrap">
                    <select class="form-control" name="paymentMethod" id="saPaymentMethod" style="border:2px solid; color: #FFA902;" disabled>
                      <?php 
                        foreach($payment_methods as $key=>$value):
                          if((int)$payment_method === (int)$key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                        <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <?php if(isset($errors['paymentMethod'])): ?>
                    <span class="error"><?php echo $errors['paymentMethod'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4" id="containerCrDays" <?php echo $credit_days_input_style ?>>
                  <label class="control-label">Credit days</label>
                  <div class="select-wrap">
                    <select class="form-control" name="saCreditDays" id="saCreditDays" disabled>
                      <?php 
                        foreach($credit_days_a as $key=>$value):
                          if((int)$credit_days === (int)$key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                        <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <?php if(isset($errors['saCreditDays'])): ?>
                    <span class="error"><?php echo $errors['saCreditDays'] ?></span>
                  <?php endif; ?>
                </div>                
                <div class="col-sm-12 col-md-4 col-lg-4" id="containerCardNo" <?php echo $card_and_auth_style ?>>
                  <label class="control-label">Card No.</label>
                  <input type="text" class="form-control noEnterKey" name="cardNo" id="cardNo" value="<?php echo $card_number ?>" disabled/>
                  <?php if(isset($errors['cardNo'])): ?>
                    <span class="error"><?php echo $errors['cardNo'] ?></span>
                  <?php endif; ?>                   
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4" id="containerAuthCode" <?php echo $card_and_auth_style ?>>
                  <label class="control-label">Auth Code</label>
                  <input type="text" class="form-control noEnterKey" name="authCode" id="authCode" value="<?php echo $card_auth_code ?>" disabled />
                  <?php if(isset($errors['authCode'])): ?>
                    <span class="error"><?php echo $errors['authCode'] ?></span>
                  <?php endif; ?>
                </div>                
              </div>              
            </div>
          </div>
          <div class="panel" style="margin-bottom:10px;">
            <div class="panel-body" style="border: 2px dotted;">
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Date of sale (dd-mm-yyyy)</label>
                  <div class="form-group">
                    <div class="col-lg-12">
                      <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                        <input class="span2" value="<?php echo $current_date ?>" size="16" type="text" readonly name="saleDate" id="saleDate" />
                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                      </div>
                      <?php if(isset($errors['saleDate'])): ?>
                        <span class="error"><?php echo $errors['saleDate'] ?></span>
                      <?php endif; ?>                  
                    </div>
                  </div>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Store name</label>
                  <div class="select-wrap">
                    <select class="form-control" name="locationCode" id="locationCode" disabled>
                      <?php 
                        foreach($client_locations as $key=>$value): 
                          if($location_code === $key) {
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
                  <?php if(isset($form_errors['locationCode'])): ?>
                    <span class="error"><?php echo $form_errors['locationCode'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Sales executive name</label>
                  <div class="select-wrap">                        
                    <select 
                      class="form-control"
                      id="saExecutive" 
                      name="saExecutive"
                      style="font-size:12px;"
                      disabled                      
                    >
                      <?php
                        foreach($sa_executives as $key=>$value):
                          if($key === $executive_id) {
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
                  <label class="control-label">Customer mobile number</label>
                  <input type="text" class="form-control noEnterKey" name="mobileNo" id="mobileNo" maxlength="10" value="<?php echo $mobile_no ?>" disabled>
                  <?php if(isset($errors['mobileNo'])): ?>
                    <span class="error"><?php echo $errors['mobileNo'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Customer name</label>
                  <input type="text" class="form-control noEnterKey cnameAc" name="name" id="name" value="<?php echo $customer_name ?>" disabled/>
                  <?php if(isset($errors['name'])): ?>
                    <span class="error"><?php echo $errors['name'] ?></span>
                  <?php endif; ?>                  
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Tax calculation method</label>
                  <div class="select-wrap">                        
                    <select 
                      class="form-control taxCalcOption"
                      id="taxCalcOption" 
                      name="taxCalcOption"
                      style="font-size:12px;"
                      disabled                      
                    >
                      <?php
                        foreach($taxcalc_opt_a as $key=>$value):
                          if($key === $tax_calc_option) {
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
                  <label class="control-label">Referral code</label>
                  <input type="text" class="form-control noEnterKey" name="refCode" id="refCode" value="<?php echo $referral_code ?>" disabled />
                  <span class="error" id="refCodeStatus" style="display: none;"></span>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Referral name</label>
                  <input type="text" class="form-control noEnterKey" name="refMemberName" id="refMemberName" disabled />
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Promo code</label>
                  <div class="select-wrap">                  
                    <select class="form-control" name="promoCode" id="saPromoCode" style="border:2px dashed; color: #FFA902;" disabled>
                      <?php 
                        foreach($offers as $offer_code => $offer_name): 
                          if($offer_code === $promo_code) {
                            $selected = 'selected = selected';
                          } else {
                            $selected = '';
                          }
                      ?>
                        <option value="<?php echo $offer_code ?>" <?php echo $selected ?>><?php echo $offer_name ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <?php if(isset($errors['promoCode'])): ?>
                    <span class="error"><?php echo $errors['promoCode'] ?></span>
                  <?php endif; ?>
                </div>               
                <input type="hidden" class="form-control noEnterKey" name="refMemberMobile" id="refMemberMobile" disabled />
              </div>
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Customer type</label>
                  <div class="select-wrap">                
                    <select class="form-control" name="customerType" id="customerType" disabled>
                      <?php 
                        foreach($customer_types as $key=>$value): 
                          if($customer_type === $key) {
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
            </div>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>