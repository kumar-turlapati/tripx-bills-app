<?php
  use Atawa\Utilities;

  // dump($form_data);
  // dump($errors);
  // dump($offers_raw);

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
  if(isset($form_data['saExecutive'])) {
    $executive_id = $form_data['saExecutive'];
  } elseif(isset($form_data['saExecutiveId'])) {
    $executive_id = $form_data['saExecutiveId'];
  } else {
    $executive_id = '';
  }

  $tot_products = isset($form_data['itemDetails']['itemName']) ? count($form_data['itemDetails']['itemName']) : 0;
  $payment_method = isset($form_data['paymentMethod']) ? $form_data['paymentMethod'] : $payment_method = 0;
  $discount_method = isset($form_data['discountMethod']) ? $form_data['discountMethod'] : $discount_method = 0;
  $mobile_no = isset($form_data['mobileNo']) ? $form_data['mobileNo'] : '';
  $card_number = isset($form_data['cardNo']) ? $form_data['cardNo'] : ''; 
  $card_auth_code = isset($form_data['authCode']) ? $form_data['authCode'] : '';
  $coupon_code = isset($form_data['couponCode']) ? $form_data['couponCode'] : '';
  $tax_calc_option = isset($form_data['taxCalcOption']) ? $form_data['taxCalcOption'] : 'i';
  $cn_no = isset($form_data['cnNo']) && $form_data['cnNo'] > 0 ? $form_data['cnNo'] : '';
  $promo_code = isset($form_data['promoCode']) ? $form_data['promoCode'] : '';
  $referral_code = isset($form_data['refCode']) ? $form_data['refCode'] : '';
  $customer_type = isset($form_data['customerType']) ? $form_data['customerType'] : 'b2c';
  $credit_days = isset($form_data['saCreditDays']) ? $form_data['saCreditDays'] : '';  

  $packing_charges = isset($form_data['packingCharges']) ? $form_data['packingCharges'] : '';
  $shipping_charges = isset($form_data['shippingCharges']) ? $form_data['shippingCharges'] : '';
  $insurance_charges = isset($form_data['insuranceCharges']) ? $form_data['shippingCharges'] : '';
  $other_charges = isset($form_data['otherCharges']) ? $form_data['otherCharges'] : '';
  $transporter_name = isset($form_data['transporterName']) ? $form_data['transporterName'] : '';
  $lr_no = isset($form_data['lrNo']) ? $form_data['lrNo'] : '';
  $lr_date = isset($form_data['lrDate']) ? $form_data['lrDate'] : '';
  $challan_no = isset($form_data['challanNo']) ? $form_data['challanNo'] : '';
  $sales_category = isset($form_data['saleCategory']) ? $form_data['saleCategory'] : '';  

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

  $card_and_auth_style = (int)$payment_method === 0 || (int)$payment_method === 3 ? 'style="display:none;"' : '';
  $split_payment_input_style = (int)$payment_method === 2 ? '' : 'disabled';
  $credit_days_input_style = (int)$payment_method === 3 ? '' : 'style="display:none;"';
  $coupon_code_input_style = (int)$discount_method === 1 ? '' : 'disabled';

  $remarks_invoice = isset($form_data['remarksInvoice']) ? $form_data['remarksInvoice'] : '';
  $ow_items_class = $tot_products > 0 ? '' : 'style="display:none;"';
  $form_submit_url = '/sales/update-with-barcode/'.$ic;

  $editable_mrps = isset($_SESSION['editable_mrps']) ? $_SESSION['editable_mrps'] : 0;
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $flash_obj->print_flash_message(); ?>
        <div class="global-links actionButtons clearfix"> 
          <div class="pull-right text-right">
            <a href="/sales/entry" class="btn btn-default"><i class="fa fa-inr"></i> Sales Entry W/o Barcode</a>&nbsp;
            <a href="/sales/list" class="btn btn-default"><i class="fa fa-book"></i> Sales Register</a>            
          </div>
        </div>        
        <form id="outwardEntryForm" method="POST">
          <div class="table-responsive">
            <table class="table table-hover font12" style="border-top:none;border-left:none;border-right:none;border-bottom:1px solid;">
              <thead>
                <tr>
                  <td style="vertical-align:middle;font-size:16px;font-weight:bold;border-right:none;border-left:none;border-top:none;text-align:right;width:10%;">Scan Barcode</td>
                  <td style="vertical-align:middle;border-right:none;border-left:none;border-top:none;width:10%;">
                    <input
                      type="text"
                      id="owBarcode"
                      class="saleItem"
                      style="font-size:16px;font-weight:bold;border:1px dashed #225992;padding-left:5px;font-weight:bold;width:150px;"
                      maxlength="13"
                      <?php echo $from_indent ? 'disabled' : '' ?>
                    />
                  </td>

                  <td style="vertical-align:middle;font-size:16px;font-weight:bold;border-right:none;border-left:none;border-top:none;text-align:right;width:8%;">Store Name</td>
                  <td style="vertical-align:middle;border-right:none;border-left:none;border-top:none;width:15%;text-align:left;">
                    <select class="form-control" name="locationCode" id="locationCode">
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
                  </td>

                  <td style="vertical-align:middle;font-size:16px;font-weight:bold;border-right:none;border-left:none;border-top:none;text-align:right;width:10%;">Customer Type</td>
                  <td style="vertical-align:middle;border-right:none;border-left:none;border-top:none;width:15%;text-align:left;">
                    <select class="form-control" name="customerType" id="customerType">
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
                  </td>                  
                </tr>
              </thead>
            </table>
          </div>
          <div class="table-responsive">
            <table <?php echo $ow_items_class ?> class="table table-striped table-hover font12" id="owItemsTable">
              <thead>
                <tr>
                  <th width="5%"  class="text-center">Sno.</th>                  
                  <th width="12%" class="text-center">Item name</th>
                  <th width="11%" class="text-center">Lot no.</th>
                  <th width="11%" class="text-center">Available<br />qty.</th>                
                  <th width="11%" class="text-center">Ordered<br />qty.</th>
                  <th width="8%"  class="text-center">M.R.P<br />( Rs. )</th>
                  <th width="8%"  class="text-center">Gross Amt.<br />( Rs. )</th>
                  <th width="8%"  class="text-center">Discount<br />( Rs. )</th>                  
                  <th width="8%"  class="text-center">Taxable Amt.<br />( Rs. )</th>
                  <th width="10%" class="text-center">GST<br />( in % )</th>
                  <th width="10%" class="text-center">Options</th>                
                </tr>
              </thead>
              <tbody id="tBodyowItems">
                <?php 
                  $tot_item_amount = $tot_taxable_amount = $tot_tax_amount = $tot_discount = 0;
                  $tot_bill_qty = $netpay = $netpay_actual = $round_off = 0;               
                  if($tot_products > 0):
                    for($i=0;$i<$tot_products;$i++):
                      $item_name = isset($form_data['itemDetails']['itemName'][$i]) ? $form_data['itemDetails']['itemName'][$i] : '';
                      $item_qty_available = isset($form_data['itemDetails']['itemAvailQty'][$i]) ? $form_data['itemDetails']['itemAvailQty'][$i] : 0;
                      $item_qty = isset($form_data['itemDetails']['itemSoldQty'][$i]) ? $form_data['itemDetails']['itemSoldQty'][$i] : 0;
                      $item_rate = isset($form_data['itemDetails']['itemRate'][$i]) ? $form_data['itemDetails']['itemRate'][$i] : '';
                      $item_discount = isset($form_data['itemDetails']['itemDiscount'][$i]) ? $form_data['itemDetails']['itemDiscount'][$i] : '';
                      $tax_percent = isset($form_data['itemDetails']['itemTaxPercent'][$i]) ? $form_data['itemDetails']['itemTaxPercent'][$i] : '';
                      $lot_no = isset($form_data['itemDetails']['lotNo'][$i]) ? $form_data['itemDetails']['lotNo'][$i] : '';
                      $barcode = isset($form_data['itemDetails']['barcode'][$i]) ? $form_data['itemDetails']['barcode'][$i] : '';                      
                      if($item_qty > 0 && $item_rate > 0) {
                        $item_amount = round($item_qty*$item_rate, 2);
                        $taxable_amount = round($item_amount-$item_discount, 2);
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
                  <tr id="tr_<?php echo $barcode ?>">
                    <td align="right" style="vertical-align:middle;" class="itemSlno"><?php echo $i+1 ?></td>
                    <td style="vertical-align:middle;">
                      <input 
                        type="text" 
                        name="itemDetails[itemName][]" 
                        id="iname_<?php echo $i+1 ?>" 
                        size="30" 
                        class="saleItem noEnterKey" 
                        index="<?php echo $i+1 ?>" 
                        value="<?php echo $item_name ?>"
                      />
                      <?php if(isset($errors['itemDetails']['itemName'][$i])): ?>
                        <span class="error">Invalid</span>
                      <?php endif; ?>
                    </td>
                    <td style="vertical-align:middle;">
                      <div class="select-wrap">
                        <select 
                          class="form-control lotNo"
                          name="itemDetails[lotNo][]"
                          id="lotNo_<?php echo $i+1 ?>"
                          index="<?php echo $i+1 ?>"              
                        >
                          <option value="">Choose</option>
                          <?php if($lot_no !== ''): ?>
                          <option value="<?php echo $lot_no ?>" selected><?php echo $lot_no ?></option>
                          <?php endif; ?>
                        </select>
                      </div>
                      <?php if(isset($errors['itemDetails']['lotNo'][$i])): ?>
                        <span class="error">Invalid</span>
                      <?php endif; ?>                      
                    </td>                
                    <td style="vertical-align:middle;">
                      <input
                        type="text"
                        class="qtyAvailable text-right noEnterKey"
                        id="qtyava_<?php echo $i+1 ?>"
                        name="itemDetails[itemAvailQty][]"
                        index="<?php echo $i+1 ?>"
                        value="<?php echo $item_qty_available ?>"
                        size="10"
                        readonly
                      />
                      <?php if(isset($errors['itemDetails']['itemAvailQty'][$i])): ?>
                        <span class="error">Invalid</span>
                      <?php endif; ?>                      
                    </td>
                    <td style="vertical-align:middle;" align="center">
                      <input
                        type="text"
                        class="saleItemQty text-right noEnterKey"
                        id="qty_<?php echo $i+1 ?>"
                        name="itemDetails[itemSoldQty][]"
                        index="<?php echo $i+1 ?>"
                        value="<?php echo $item_qty ?>"
                        size="10"
                      />
                      <?php if(isset($errors['itemDetails']['itemSoldQty'][$i])): ?>
                        <span class="error">Invalid</span>
                      <?php endif; ?>                      
                    </td>
                    <td style="vertical-align:middle;" align="center">
                      <input 
                        <?php echo Utilities::is_mrp_editable() ? "" : "readonly" ?>
                        class = "mrp text-right noEnterKey"
                        id = "mrp_<?php echo $i+1 ?>"
                        index = "<?php echo $i+1 ?>"
                        size = "10"
                        value = "<?php echo $item_rate ?>"
                        name = "itemDetails[itemRate][]"
                      />
                      <?php if(isset($errors['itemDetails']['mrp'][$i])): ?>
                        <span class="error">Invalid</span>
                      <?php endif; ?>                      
                    </td>
                    <td class="grossAmount" id="grossAmount_<?php echo $i+1 ?>" index="<?php echo $i+1 ?>" style="vertical-align:middle;text-align:right;"><?php echo $item_amount ?></td>
                    <td align="center" style="vertical-align:middle;">
                      <input
                        type="text" 
                        name="itemDetails[itemDiscount][]" 
                        id="discount_<?php echo $i+1 ?>" 
                        size="10" 
                        class="saDiscount noEnterKey" 
                        index="<?php echo $i+1 ?>" 
                        value="<?php echo $item_discount ?>"
                      />
                      <?php if(isset($errors['itemDetails']['itemDiscount'][$i])): ?>
                        <span class="error">Invalid</span>
                      <?php endif; ?>                      
                    </td>
                    <td class="taxableAmt text-right" id="taxableAmt_<?php echo $i+1 ?>" index="<?php echo $i+1 ?>" style="vertical-align:middle;text-align:right;"><?php echo $taxable_amount ?></td>
                    <td style="vertical-align:middle;">
                      <div class="select-wrap">
                        <select 
                          class="form-control saItemTax"
                          id="saItemTax_<?php echo $i+1 ?>" 
                          name="itemDetails[itemTaxPercent][]"
                          style="font-size:12px;"
                        >
                          <?php 
                            foreach($taxes as $key=>$value):
                              if((float)$value === (float)$tax_percent) {
                                $selected = 'selected="selected"';
                              } else {
                                $selected = '';
                              }
                          ?>
                            <option value='<?php echo number_format($value,2) ?>' <?php echo $selected ?>>
                              <?php echo number_format($value,2) ?>
                            </option>
                          <?php endforeach; ?>                            
                        </select>
                      </div>
                      <?php if(isset($errors['itemDetails']['itemTaxPercent'][$i])): ?>
                        <span class="error">Invalid</span>
                      <?php endif; ?>                      
                      <input type="hidden" class="taxAmount" id="taxAmount_<?php echo $i+1 ?>" value="<?php echo $tax_amount ?>" />
                      <input type="hidden" class="itemType" id="itemType_<?php echo $i+1 ?>" />
                    </td>
                    <td style="vertical-align:middle;text-align:center;">
                      <div class="btn-actions-group">
                        <a class="btn btn-danger deleteOwItem" href="javascript:void(0)" title="Delete Row" id="delrow_<?php echo $barcode ?>">
                          <i class="fa fa-times"></i>
                        </a>
                      </div>
                    </td>                    
                  </tr>
                  <?php endfor; ?>
                <?php endif; ?>
              </tbody>
              <tfoot id="tFootowItems">
                <?php
                  if(isset($form_data['taxCalcOption']) && $form_data['taxCalcOption'] === 'i') {
                    $tot_tax_amount = 0;
                  }
                  $netpay_actual = $tot_taxable_amount + $tot_tax_amount;
                  $round_off = round($netpay_actual,0) - $netpay_actual;
                  $netpay = round($netpay_actual,0);
                ?>
                <tr>
                  <td colspan="4" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Total Bill Qty.</td>
                  <td id="totalItems" name="totalItems" style="vertical-align:middle;font-weight:bold;font-size:18px;text-align:right;"><?php echo $tot_bill_qty > 0 ? $tot_bill_qty : '' ?></td>
                  <td colspan="4" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Gross Amount</td>
                  <td colspan="2" id="grossAmount" class="" style="font-size:16px;text-align:right;font-weight:bold;"><?php echo $tot_item_amount > 0 ? number_format($tot_item_amount, 2, '.', '') : '' ?></td>
                </tr>
                <tr>
                  <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;">Discount (in Rs.)</td>
                  <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;">Taxable amount (in Rs.)</td>
                  <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;">GST (in Rs.)</td>
                  <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;">Round off (in Rs.)</td>
                  <td colspan="3" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;">Net pay (in Rs.)</td>
                </tr>
                <tr>
                  <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" id="totDiscount"><?php echo $tot_discount > 0 ? number_format($tot_discount, 2, '.', '') : '' ?></td>
                  <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" id="taxableAmount" class="taxableAmount"><?php echo $tot_taxable_amount > 0 ? number_format($tot_taxable_amount, 2, '.', '') : '' ?></td>
                  <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" id="gstAmount" class="gstAmount"><?php echo $tot_tax_amount > 0 ? number_format($tot_tax_amount, 2, '.', '') : '' ?></td>
                  <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" id="roundOff" class="roundOff"><?php echo number_format($round_off, 2, '.', '') ?></td>
                  <td colspan="3" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" id="netPayBottom" class="netPay"><?php echo number_format($netpay, 2, '.', '') ?></td>
                </tr>
              </tfoot>
            </table>
            <input type="hidden" name="promoKey" id="promoKey" value="<?php echo $promo_key ?>" />
            <input type="hidden" name="editKey" id="editKey" value="<?php echo $editable_mrps ?>" />            
          </div>
          <div class="panel" style="margin-bottom:15px;<?php echo $tot_products > 0 && $customer_type === 'b2b' ? '' : 'display:none;' ?>" id="siOtherInfoWindow">
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
                  />
                  <?php if(isset($errors['challanNo'])): ?>
                    <span class="error"><?php echo $errors['challanNo'] ?></span>
                  <?php endif; ?>                   
                </div>
              </div>
            </div>
          </div>
          <div class="panel" style="margin-bottom:10px;<?php echo $tot_products > 0 ? '' : 'display:none;' ?>" id="paymentMethodWindow">
            <div class="panel-body" style="border: 1px dotted;">
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Payment method</label>
                  <div class="select-wrap">
                    <select class="form-control" name="paymentMethod" id="saPaymentMethod" style="border:2px solid; color: #FFA902;">
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
                    <select class="form-control" name="saCreditDays" id="saCreditDays">
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
                  <input type="text" class="form-control noEnterKey" name="cardNo" id="cardNo" value="<?php echo $card_number ?>" />
                  <?php if(isset($errors['cardNo'])): ?>
                    <span class="error"><?php echo $errors['cardNo'] ?></span>
                  <?php endif; ?>                   
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4" id="containerAuthCode" <?php echo $card_and_auth_style ?>>
                  <label class="control-label">Auth Code</label>
                  <input type="text" class="form-control noEnterKey" name="authCode" id="authCode" value="<?php echo $card_auth_code ?>" />
                  <?php if(isset($errors['authCode'])): ?>
                    <span class="error"><?php echo $errors['authCode'] ?></span>
                  <?php endif; ?>
                </div>
              </div>              
            </div>
          </div>        
          <div class="panel" style="margin-bottom:10px;<?php echo $tot_products > 0 ? '' : 'display:none;' ?>" id="customerWindow">
            <div class="panel-body" style="border: 1px dotted;">
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
                  <label class="control-label">Sales executive name</label>
                  <div class="select-wrap">                        
                    <select 
                      class="form-control"
                      id="saExecutive" 
                      name="saExecutive"
                      style="font-size:12px;"
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
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Tax calculation method</label>
                  <div class="select-wrap">                        
                    <select 
                      class="form-control taxCalcOption"
                      id="taxCalcOption" 
                      name="taxCalcOption"
                      style="font-size:12px;"
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
                  <label class="control-label">Customer mobile number</label>
                  <input type="text" class="form-control noEnterKey" name="mobileNo" id="mobileNo" maxlength="10" value="<?php echo $mobile_no ?>">
                  <?php if(isset($errors['mobileNo'])): ?>
                    <span class="error"><?php echo $errors['mobileNo'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Customer name</label>
                  <input type="text" class="form-control noEnterKey cnameAc" name="name" id="name" value="<?php echo $customer_name ?>" />
                  <?php if(isset($errors['name'])): ?>
                    <span class="error"><?php echo $errors['name'] ?></span>
                  <?php endif; ?>                  
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Promo code</label>
                  <div class="select-wrap">                  
                    <select class="form-control" name="promoCode" id="saPromoCode" style="border:2px dashed; color: #FFA902;">
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
              </div>
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Referral code</label>
                  <input type="text" class="form-control noEnterKey" name="refCode" id="refCode" value="<?php echo $referral_code ?>" />
                  <span class="error" id="refCodeStatus" style="display: none;"></span>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Referral name</label>
                  <input type="text" class="form-control noEnterKey" name="refMemberName" id="refMemberName" disabled />
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Referral mobile</label>
                  <input type="text" class="form-control noEnterKey" name="refMemberMobile" id="refMemberMobile" disabled />
                </div>                
              </div>
            </div>
          </div>
          <div class="panel" style="margin-bottom:15px;<?php echo $tot_products > 0 && (int)$payment_method === 2 ? '' : 'display:none;' ?>" id="splitPaymentWindow">
            <div class="panel-body" style="border: 1px dotted;">
              <div class="form-group">
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label">Credit Note No.</label>
                  <input
                    type="text"
                    size="10"
                    id="cnNo"
                    name="cnNo"
                    maxlength="8"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    <?php echo $split_payment_input_style ?>
                    value="<?php echo $cn_no ?>"
                    class="form-control"
                  />
                  <?php if(isset($errors['cnNo'])): ?>
                    <span class="error"><?php echo $errors['cnNo'] ?></span>
                  <?php endif; ?>                  
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label">Credit Note Value</label>
                  <input
                    type="text"
                    size="15"
                    id="splitPaymentCn"
                    name="splitPaymentCn"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    <?php echo $split_payment_input_style ?>
                    value="<?php echo $split_payment_cn ?>"
                    class="form-control"                    
                  />
                  <?php if(isset($errors['splitPaymentCn'])): ?>
                    <span class="error"><?php echo $errors['splitPaymentCn'] ?></span>
                  <?php endif; ?>                   
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label">Cash Value</label>
                  <input
                    type="text"
                    size="15"
                    id="splitPaymentCash"
                    name="splitPaymentCash"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    <?php echo $split_payment_input_style ?>
                    value="<?php echo $split_payment_cash ?>"
                    class="form-control"                    
                  />
                  <?php if(isset($errors['splitPaymentCash'])): ?>
                    <span class="error"><?php echo $errors['splitPaymentCash'] ?></span>
                  <?php endif; ?>                   
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label">Card Value</label>
                  <input 
                    type="text"
                    size="15"
                    id="splitPaymentCard"
                    name="splitPaymentCard"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:1px dashed;"
                    <?php echo $split_payment_input_style ?>
                    value="<?php echo $split_payment_card ?>"
                    class="form-control"                    
                  />
                  <?php if(isset($errors['splitPaymentCard'])): ?>
                    <span class="error"><?php echo $errors['splitPaymentCard'] ?></span>
                  <?php endif; ?>                  
                </div>
              </div>
            </div>
          </div>
          <div class="panel" style="margin-bottom:10px;<?php echo $tot_products > 0 ? '' : 'display:none;' ?>" id="remarksWindow">
            <div class="panel-body" style="border: 2px dashed;">
              <div class="form-group">
                <div class="col-sm-12 col-md-8 col-lg-8">
                  <label class="control-label">Remarks / Notes (200 characters maximum)</label>
                  <input type="text" class="form-control noEnterKey" name="remarksInvoice" id="remarksInvoice" maxlength="200" value="<?php echo $remarks_invoice ?>">
                  <?php if(isset($errors['remarksInvoice'])): ?>
                    <span class="error"><?php echo $errors['remarksInvoice'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Sales category</label>
                  <div class="select-wrap">                
                    <select class="form-control" name="salesCategory" id="salesCategory">
                      <?php 
                        foreach($sa_categories as $key=>$value): 
                          if($sales_category === $key) {
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
          <div class="text-center" id="saveWindow" style="<?php echo $tot_products > 0 ? '' : 'display:none;' ?>">
            <?php 
              if($promo_key !== '') {
                $button_icon = 'fa fa-lemon-o';
                $button_class = 'btn btn-danger';
              } else {
                $button_icon = 'fa fa-save';
                $button_class = 'btn btn-primary';
              }
            ?>
            <button class="<?php echo $button_class ?> cancelOp" id="SaveInvoice" name="op" value="SaveandPrintBill">
              <i class="<?php echo $button_icon ?>"></i> Save Bill & Print
            </button>
            <button class="btn btn-danger cancelButton" id="seWithBarcode">
              <i class="fa fa-times"></i> Cancel
            </button>            
            <?php /*
            <button class="btn btn-warning" id="SaveBill" name="op" value="SaveandPrintInvoice">
              <i class="fa fa-save"></i> Save &amp; Print Invoice
            </button> */?>
          </div>
          <?php if($from_indent): ?>
            <input type="hidden" name="fi" id="fi" value="" />
          <?php endif; ?>
        </form>
      </div>
    </section>
  </div>
</div>

<?php if($bill_to_print !== '' && $print_format === 'bill'): ?>
  <script>
    (function() {
      var printUrl = '/print-sales-bill-small?billNo=<?php echo $bill_to_print ?>';
      var printWindow = window.open(printUrl, "_blank", "left=0,top=0,width=300,height=300,toolbar=0,scrollbars=0,status=0");
    })();
  </script>
<?php elseif($bill_to_print !== '' && $print_format === 'invoice'): ?>
  <script>
    (function() {
      var printUrl = '/print-sales-bill?billNo=<?php echo $bill_to_print ?>';
      var printWindow = window.open(printUrl, "_blank", "left=0,top=0,width=300,height=300,toolbar=0,scrollbars=0,status=0");
    })();
  </script>
<?php endif; ?>