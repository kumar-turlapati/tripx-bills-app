<?php

  // dump($form_data);
  // dump($errors);
  // dump($offers_raw);
  // exit;

  if(isset($form_data['invoiceDate']) && $form_data['invoiceDate']!=='') {
    $current_date = date("d-m-Y", strtotime($form_data['invoiceDate']));
  } else {
    $current_date = date("d-m-Y");
  }
  if(isset($form_data['paymentMethod'])) {
    $payment_method = $form_data['paymentMethod'];
  } else {
    $payment_method = 0;
  }
  if(isset($form_data['discountMethod'])) {
    $discount_method = $form_data['discountMethod'];
  } else {
    $discount_method = 0;
  }
  if(isset($form_data['mobileNo'])) {
    $mobile_no = $form_data['mobileNo'];
  } else {
    $mobile_no = '';
  }
  if(isset($form_data['name'])) {
    $customer_name = $form_data['name'];
  } else {
    $customer_name = '';
  }
  if(isset($form_data['cardNo'])) {
    $card_number = $form_data['cardNo'];
  } else {
    $card_number = '';
  }
  if(isset($form_data['authCode'])) {
    $card_auth_code = $form_data['authCode'];
  } else {
    $card_auth_code = '';
  }
  if(isset($form_data['couponCode'])) {
    $coupon_code = $form_data['couponCode'];
  } else {
    $coupon_code = '';
  }  
  if(isset($form_data['taxCalcOption'])) {
    $tax_calc_option = $form_data['taxCalcOption'];
  } else {
    $tax_calc_option = 'i';
  }
  if(isset($form_data['splitPaymentCash'])) {
    $split_payment_cash = $form_data['splitPaymentCash'];
  } else {
    $split_payment_cash = '';
  }
  if(isset($form_data['splitPaymentCard'])) {
    $split_payment_card = $form_data['splitPaymentCard'];
  } else {
    $split_payment_card = '';
  }
  if(isset($form_data['saExecutive'])) {
    $executive_id = $form_data['saExecutive'];
  } else {
    $executive_id = '';
  }
  if(isset($form_data['locationCode'])) {
    $location_code = $form_data['locationCode'];
  } elseif($default_location !== '') {
    $location_code = $default_location;
  } else {
    $location_code = '';
  }
  if(isset($form_data['cnNo'])) {
    $cn_no = $form_data['cnNo'];
  } else {
    $cn_no = '';
  }
  if(isset($form_data['splitPaymentCn'])) {
    $split_payment_cn = $form_data['splitPaymentCn'];
  } else {
    $split_payment_cn = '';
  }
  if(isset($form_data['promoCode'])) {
    $promo_code = $form_data['promoCode'];
  } else {
    $promo_code = '';
  }
  if(isset($form_data['refCode'])) {
    $referral_code = $form_data['refCode'];
  } else {
    $referral_code = '';
  }

  $card_and_auth_style = (int)$payment_method === 0 ? 'style="display:none;"' : '';
  $split_payment_input_style = (int)$payment_method === 2 ? '' : 'disabled';
  $coupon_code_input_style = (int)$discount_method === 1 ? '' : 'disabled';
?>

<div class="row">
  <div class="col-lg-12"> 
    
    <section class="panelBox">
      <div class="panelBody">

        <?php echo $flash_obj->print_flash_message(); ?>
       
        <div class="global-links actionButtons clearfix"> 
          <div class="pull-right text-right">
            <a href="/sales/list" class="btn btn-default"><i class="fa fa-inr"></i> Sales Register</a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="outwardEntryForm">
          <div class="panel" style="margin-bottom:0px;">
            <div class="panel-body">
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
                  <label class="control-label">Store name (against which store this entry effects)</label>
                  <div class="select-wrap">
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
                  <input type="text" class="form-control noEnterKey" name="mobileNo" id="mobileNo" maxlength="10" value="<?php echo $mobile_no ?>">
                  <?php if(isset($errors['mobileNo'])): ?>
                    <span class="error"><?php echo $errors['mobileNo'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Customer name</label>
                  <input type="text" class="form-control noEnterKey" name="name" id="name" value="<?php echo $customer_name ?>" />
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
                  <input type="text" class="form-control noEnterKey" name="refCode" id="refCode" value="<?php echo $referral_code ?>" />
                  <span class="error" id="refCodeStatus" style="display: none;"></span>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Referral name</label>
                  <input type="text" class="form-control noEnterKey" name="refMemberName" id="refMemberName" disabled />
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
                <input type="hidden" class="form-control noEnterKey" name="refMemberMobile" id="refMemberMobile" disabled />
              </div>   
            </div>
          </div>
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
                  for($i=1;$i<=10;$i++):
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
                    <td style="vertical-align:middle;">
                      <input 
                        type="text" 
                        name="itemDetails[itemName][]" 
                        id="iname_<?php echo $i-1 ?>" 
                        size="30" 
                        class="inameAc saleItem noEnterKey" 
                        index="<?php echo $i-1 ?>" 
                        value="<?php echo $item_name ?>"
                      />
                    </td>
                    <td style="vertical-align:middle;">
                      <div class="select-wrap">
                        <select 
                          class="form-control lotNo"
                          name="itemDetails[lotNo][]"
                          id="lotNo_<?php echo $i-1 ?>"
                          index="<?php echo $i-1 ?>"              
                        >
                          <option value="">Choose</option>
                          <?php if($lot_no !== ''): ?>
                          <option value="<?php echo $lot_no ?>" selected><?php echo $lot_no ?></option>
                          <?php endif; ?>
                        </select>
                      </div>                      
                    </td>                
                    <td style="vertical-align:middle;">
                      <input
                        type="text"
                        class="qtyAvailable text-right noEnterKey"
                        id="qtyava_<?php echo $i-1 ?>"
                        name="itemDetails[itemAvailQty][]"
                        index="<?php echo $i-1 ?>"
                        value="<?php echo $item_qty_available ?>"
                        size="10"
                        readonly
                      />
                    </td>
                    <td style="vertical-align:middle;" align="center">
                      <div class="select-wrap">
                        <select 
                          class="form-control saleItemQty"
                          name="itemDetails[itemSoldQty][]"
                          id="qty_<?php echo $i-1 ?>"
                          index="<?php echo $i-1 ?>"              
                        >
                          <?php 
                            foreach($qtys_a as $key=>$value):
                               if((int)$item_qty === (int)$key) {
                                $selected = 'selected="selected"';
                               } else {
                                $selected = '';
                               }                                 
                          ?>
                            <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </td>
                    <td style="vertical-align:middle;" align="center">
                      <input 
                        readonly
                        class = "mrp text-right noEnterKey"
                        id = "mrp_<?php echo $i-1 ?>"
                        index = "<?php echo $i-1 ?>"
                        size = "10"
                        value = "<?php echo $item_rate ?>"
                        name = "itemDetails[itemRate][]"
                      />
                    </td>
                    <td 
                      class="grossAmount" 
                      id="grossAmount_<?php echo $i-1 ?>" 
                      index="<?php echo $i-1 ?>"
                      style="vertical-align:middle;text-align:right;"
                    ><?php echo $item_amount ?></td>
                    <td align="center" style="vertical-align:middle;">
                      <input
                        type="text" 
                        name="itemDetails[itemDiscount][]" 
                        id="discount_<?php echo $i-1 ?>" 
                        size="10" 
                        class="saDiscount noEnterKey" 
                        index="<?php echo $i-1 ?>" 
                        value="<?php echo $item_discount ?>"
                      />                      
                    </td>
                    <td
                      class="taxableAmt text-right"
                      id="taxableAmt_<?php echo $i-1 ?>" 
                      index="<?php echo $i-1 ?>"
                      style="vertical-align:middle;text-align:right;"                      
                    ><?php echo $taxable_amount ?></td>
                    <td
                      style="vertical-align:middle;"
                    >
                      <div class="select-wrap">
                        <select 
                          class="form-control saItemTax"
                          id="saItemTax_<?php echo $i-1 ?>" 
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
                      <input type="hidden" class="taxAmount" id="taxAmount_<?php echo $i-1 ?>" value="<?php echo $tax_amount ?>" />
                      <input type="hidden" class="itemType" id="itemType_<?php echo $i-1 ?>" />
                    </td>
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
                  $net_pay_actual = $tot_taxable_amount + $tax_amount;
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
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" id="gstAmount" class="gstAmount"><?php echo $tot_tax_amount >0 ? number_format($tot_tax_amount, 2, '.', '') : '' ?></td>
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
                          disabled
                          style="font-weight:bold;font-size:14px;padding-left:5px;background-color:#f1f442;border:1px dashed;"
                        />
                      </span>
                    </td>
                  </tr>
              </tbody>
            </table>
            <input type="hidden" name="promoKey" id="promoKey" value="<?php echo $promo_key ?>" />
          </div>
          <div class="panel" style="margin-bottom:20px;">
            <div class="panel-body" style="border: 2px dotted;">
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
          <div class="text-center">
            <?php 
              if($promo_key !== '') {
                $button_icon = 'fa fa-lemon-o';
                $button_class = 'btn btn-danger';
              } else {
                $button_icon = 'fa fa-save';
                $button_class = 'btn btn-primary';
              }
            ?>
            <button class="<?php echo $button_class ?>" id="SaveInvoice" name="op" value="SaveandPrintBill">
              <i class="<?php echo $button_icon ?>"></i> Save Bill &amp; Print
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>
<?php if($bill_to_print>0) : ?>
  <script>
    (function() {
      var printUrl = '/print-sales-bill-small?billNo='+<?php echo $bill_to_print ?>;
      var printWindow = window.open(printUrl, "_blank", "left=0,top=0,width=300,height=300,toolbar=0,scrollbars=0,status=0");
    })();
  </script>
<?php endif; ?>