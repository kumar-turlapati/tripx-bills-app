<?php
  //  dump($form_data);
  // dump($errors);
  // dump($offers_raw);
  // exit;

  // dump($print_format, $bill_to_print);

  use Atawa\Constants;

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
  $agent_code = isset($form_data['agentCode']) ? $form_data['agentCode'] : '';

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
            <table class="table table-striped table-hover font12" style="margin-bottom: 0px;">
              <thead>
                <tr>
                  <th width="5%"  class="text-center">Sno.</th>                  
                  <th width="28%" class="text-center">Item name</th>
                  <th width="10%" class="text-center">Lot no.</th>
                  <th width="8%" class="text-center">Case / <br />Box No.</th>
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
                      $cno = isset($form_data['itemDetails']['cnos'][$ex_index]) ? $form_data['itemDetails']['cnos'][$ex_index] : '';
                    } else {
                      $item_name = '';
                      $lot_no = '';
                      $item_qty_available = 0;
                      $item_qty = 0;
                      $item_rate = 0;
                      $item_discount = 0;
                      $tax_percent = 0;
                      $cno = '';
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
                  <tr class="font11">
                    <td align="right" style="vertical-align:middle;"><?php echo $i ?></td>
                    <td style="vertical-align:middle;"><?php echo $item_name ?></td>
                    <td style="vertical-align:middle;"><?php echo $lot_no ?></td>              
                    <td style="vertical-align:middle;"><?php echo $cno ?></td>
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
                    <td colspan="5" style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:right;">Total Bill Qty.</td>
                    <td id="totalItems" name="totalItems" style="vertical-align:middle;font-weight:bold;font-size:18px;text-align:right;"><?php echo $tot_bill_qty > 0 ? $tot_bill_qty : ''  ?></td>
                    <td colspan="3" style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:right;">Gross Amount</td>
                    <td id="grossAmount" class="" style="font-size:16px;text-align:right;font-weight:bold;"><?php echo $tot_item_amount > 0 ? number_format($tot_item_amount, 2, '.', '') : '' ?></td>
                    <td>&nbsp;</td>
                  </tr>
                  <tr>
                    <th colspan="2" style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:left;">Discount (in Rs.)</th>
                    <th colspan="2" style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:left;">Taxable amount (in Rs.)</th>
                    <th colspan="2" style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:left;">GST (in Rs.)</th>
                    <th colspan="2" style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:left;">Round off (in Rs.)</th>
                    <th colspan="3" style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:left;">Net pay (in Rs.)</th>
                  </tr>
                  <tr>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;" id="totDiscount"><?php echo $tot_discount > 0 ? number_format($tot_discount, 2, '.', '') : '' ?></td>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;" id="taxableAmount" class="taxableAmount"><?php echo $tot_taxable_amount > 0 ? number_format($tot_taxable_amount, 2, '.', '') : '' ?></td>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;" id="gstAmount" class="gstAmount"><?php echo $tot_tax_amount >0 ? number_format($tot_tax_amount, 2, '.', '') : '<span style="color:green;font-weight:bold;">Inclusive</span>' ?></td>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;" id="roundOff" class="roundOff"><?php echo $rounded_off !== '' ? number_format($rounded_off, 2, '.', '') : '' ?></td>
                    <td colspan="3" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;" id="netPayBottom" class="netPay"><?php echo $net_pay > 0 ? number_format($net_pay, 2, '.', '') : '' ?></td>
                  </tr>
                  <tr>
                    <th colspan="2" style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:left;">Credit Note No.</th>
                    <th colspan="2" style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:left;">Cr.Note Value (in Rs.)</th>
                    <th colspan="2" style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:left;">Cash Value (in Rs.)</th>
                    <th colspan="2" style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:left;">Card Value(in Rs.)</th>
                    <th colspan="3">&nbsp;</th>
                  </tr>
                  <tr>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;"><?php echo $cn_no > 0 ? $cn_no : '' ?></td>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;"><?php echo $split_payment_cn ?></td>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;"><?php echo $split_payment_cash ?></td>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;"><?php echo $split_payment_card ?></td>
                    <td colspan="3">&nbsp;</td>
                  </tr>
              </tbody>
            </table>
          </div>

          <div class="table-responsive">
            <table class="table table-striped table-hover font14" id="owItemsTable" style="margin-bottom:0px;">
              <tr>
                <th width="10%" class="text-left valign-middle">Packing charges (in Rs.)</th>
                <th width="10%"  class="text-left valign-middle">Shipping charges (in Rs.)</th>             
                <th width="10%" class="text-left valign-middle">Insurance charges (in Rs.)</th>
                <th width="10%"  class="text-left valign-middle">Other charges (in Rs.)</th>             
              </tr>
              <tr>
                <td style="vertical-align:middle;text-align:center;"><?php echo $packing_charges > 0 ? number_format($packing_charges, 2, '.', '')  : '&nbsp;' ?></td>
                <td style="vertical-align:middle;text-align:center;"><?php echo $shipping_charges > 0 ? number_format($shipping_charges, 2, '.', '') : '&nbsp;' ?></td>
                <td style="vertical-align:middle;text-align:center;"><?php echo $insurance_charges > 0 ? number_format($insurance_charges, 2, '.', '') : '&nbsp;' ?></td>
                <td style="vertical-align:middle;text-align:center;"><?php echo $other_charges > 0 ? number_format($other_charges, 2, '.', '') : '&nbsp;' ?></td>
              </tr>
              <tr>
                <th width="10%" class="text-left valign-middle">Transporter name</th>
                <th width="10%"  class="text-left valign-middle">L.R. No(s)</th>             
                <th width="10%" class="text-left valign-middle">L.R. Date</th>
                <th width="10%"  class="text-left valign-middle">Challan No.</th>             
              </tr>
              <tr>
                <td style="vertical-align:middle;text-align:center;"><?php echo $transporter_name ?></td>
                <td style="vertical-align:middle;text-align:center;"><?php echo $lr_no ?></td>
                <td style="vertical-align:middle;text-align:center;"><?php echo $lr_date !== '' ? date("d-m-Y", strtotime($lr_date)) : '' ?></td>
                <td style="vertical-align:middle;text-align:center;"><?php echo $challan_no ?></td>
              </tr>                            
            </table>
          </div>
          <div class="table-responsive">
            <table class="table table-striped table-hover font12" id="owItemsTable" style="margin-bottom:0px;">
              <tr>
                <th width="10%" class="text-left valign-middle">Payment method</th>
                <th width="10%"  class="text-left valign-middle">Credit days</th>             
                <th width="10%" class="text-left valign-middle">Card no.</th>
                <th width="10%"  class="text-left valign-middle">Auth code</th>             
              </tr>
              <tr>
                <td style="vertical-align:middle;text-align:center;"><?php echo Constants::$PAYMENT_METHODS_RC[$payment_method] ?></td>
                <td style="vertical-align:middle;text-align:center;"><?php echo $credit_days ?></td>
                <td style="vertical-align:middle;text-align:center;"><?php echo $card_number ?></td>
                <td style="vertical-align:middle;text-align:center;"><?php echo $card_auth_code ?></td>
              </tr>
              <tr>
                <th width="10%" class="text-left valign-middle">Date of sale</th>
                <th width="10%"  class="text-left valign-middle">Store name</th>             
                <th width="10%" class="text-left valign-middle">Sales executive name</th>
                <th width="10%"  class="text-left valign-middle">Customer mobile</th>             
              </tr>
              <tr>
                <td style="vertical-align:middle;text-align:center;"><?php echo $current_date ?></td>
                <td style="vertical-align:middle;text-align:center;"><?php echo $form_data['locationName'] ?></td>
                <td style="vertical-align:middle;text-align:center;"><?php echo $form_data['executiveName'] ?></td>
                <td style="vertical-align:middle;text-align:center;"><?php echo $mobile_no ?></td>
              </tr>
              <tr>
                <th width="10%" class="text-left valign-middle">Customer name</th>
                <th width="10%" class="text-left valign-middle">Customer type</th>
                <th width="10%"  class="text-left valign-middle">Agent name</th>             
                <th width="10%"  class="text-left valign-middle">Tax calculation method</th>             
              </tr>
              <tr>
                <td style="vertical-align:middle;text-align:center;"><?php echo $customer_name ?></td>
                <td style="vertical-align:middle;text-align:center;"><?php echo strtoupper($form_data['customerType']) ?></td>
                <td style="vertical-align:middle;text-align:center;"><?php echo $form_data['agentName'] ?></td>
                <td style="vertical-align:middle;text-align:center;"><?php echo $form_data['taxCalcOption'] === 'e' ? 'Exclusive' : 'Inclusive' ?></td>
              </tr>
              <tr>
                <th width="10%" class="text-left valign-middle">Referral code</th>
                <th width="10%" class="text-left valign-middle">Referral name</th>
                <th width="10%" class="text-left valign-middle">Promo code</th>             
                <th width="10%" class="text-left valign-middle">&nbsp;</th>             
              </tr>
              <tr>
                <td style="vertical-align:middle;text-align:center;">&nbsp;</td>
                <td style="vertical-align:middle;text-align:center;">&nbsp;</td>
                <td style="vertical-align:middle;text-align:center;"><?php echo $promo_code ?></td>
                <td style="vertical-align:middle;text-align:center;">&nbsp;</td>
              </tr>
              <tr>
                <th colspan="4">Remarks:&nbsp;<span style="color:#000;font-weight: bold;"><?php echo $form_data['remarksInvoice'] ?></span></th>
              </tr>
            </table>
          </div>          
        </form>
      </div>
    </section>
  </div>
</div>