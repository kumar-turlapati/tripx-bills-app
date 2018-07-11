<?php

  // dump($form_data);
  // dump($errors);
  // dump($offers_raw);

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

  if(isset($form_data['locationCode'])) {
    $location_code = $form_data['locationCode'];
  } else {
    $location_code = '';
  }  

  $card_and_auth_style = (int)$payment_method === 0 ? 'style="display:none;"' : '';
  $split_payment_input_style = (int)$payment_method === 2 ? '' : 'disabled';
  $coupon_code_input_style = (int)$discount_method === 1 ? '' : 'disabled';
?>

<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12"> 
    
    <!-- Panel starts -->
    <section class="panelBox">
      <div class="panelBody">

        <?php echo $flash_obj->print_flash_message(); ?>
       
        <!-- Right links starts -->
        <div class="global-links actionButtons clearfix"> 
          <div class="pull-right text-right">
            <a href="/sales/list" class="btn btn-default"><i class="fa fa-book"></i> Daywise Sales List</a>
            <a href="/sales/entry" class="btn btn-default"><i class="fa fa-file-text-o"></i> New Sale </a> 
          </div>
        </div>
        <!-- Right links ends -->
        
        <!-- Form starts -->
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="outwardEntryForm">
          <h2 class="hdg-reports">Transaction Details</h2>
          <div class="panel">
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
                  <label class="control-label">Payment method</label>
                  <div class="select-wrap">
                    <select class="form-control" name="paymentMethod" id="saPaymentMethod">
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
                          // if($key === $tax_calc_option) {
                            $selected = 'selected="selected"';
/*                          } else {
                            $selected = '';
                          }*/
                      ?>
                        <option value="<?php echo $key ?>" <?php echo $selected ?>>
                          <?php echo $value ?>
                        </option>
                      <?php endforeach; ?>                            
                    </select>
                  </div>
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
          <h2 class="hdg-reports">Item Details</h2>
          <div class="table-responsive">
            <table class="table table-striped table-hover font12">
              <thead>
                <tr>
                  <th width="5%"  class="text-center">Sno.</th>                  
                  <th width="18%" class="text-center">Item name</th>
                  <th width="12%" class="text-center">Lot No</th>
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
                <tr>
                  <td colspan="10" align="right" style="vertical-align:middle;">
                    <span style="padding-right:5px;font-weight:bold;font-size:14px;">Credit Note</span>
                    <span style="padding-right:10px;">
                      <input
                        type="text"
                        size="15"
                        id="splitPaymentCn"
                        name="splitPaymentCn"
                        style="font-weight:bold;font-size:14px;padding-left:5px;"
                        <?php echo $split_payment_input_style ?>
                        value="<?php //echo $split_payment_cash ?>"
                      />
                    </span>                    
                    <span style="padding-right:5px;font-weight:bold;font-size:14px;">Cash</span>
                    <span style="padding-right:10px;">
                      <input
                        type="text"
                        size="15"
                        id="splitPaymentCash"
                        name="splitPaymentCash"
                        style="font-weight:bold;font-size:14px;padding-left:5px;"
                        <?php echo $split_payment_input_style ?>
                        value="<?php echo $split_payment_cash ?>"
                      />
                    </span>
                    <span style="padding-right:5px;font-weight:bold;font-size:14px;">Card</span>
                    <span style="padding-right:10px;">
                      <input 
                        type="text"
                        size="15"
                        id="splitPaymentCard"
                        name="splitPaymentCard"
                        style="font-weight:bold;font-size:14px;padding-left:5px;"
                        <?php echo $split_payment_input_style ?>
                        value="<?php echo $split_payment_card ?>"
                      />
                    </span>
                    <span style="padding-right:5px;font-weight:bold;font-size:14px;">Net Pay</span>
                    <span>
                      <input
                        type="text"
                        size="20"
                        id="netPayTop"
                        name="netPayTop"
                        class="netPay"
                        disabled
                        style="font-weight:bold;font-size:14px;padding-left:5px;background-color:#f1f442;border:1px solid #000;"
                      />
                    </span>
                  </td>
                </tr>
                <?php
                  for($i=1;$i<=10;$i++):
                    $bill_amount = $taxable_amount = $tax_amount = $item_total = 0;
                    $ex_index = $i-1;
                    if(isset($form_data['itemDetails'])) {
                      $item_name = $form_data['itemDetails']['itemName'][$ex_index];
                      $item_qty_available = $form_data['itemDetails']['itemAvailQty'][$ex_index];
                      $item_qty = $form_data['itemDetails']['itemSoldQty'][$ex_index];
                      $item_rate = $form_data['itemDetails']['itemRate'][$ex_index];
                      $item_discount = $form_data['itemDetails']['itemDiscount'][$ex_index];
                      $tax_percent = $form_data['itemDetails']['itemTaxPercent'][$ex_index];
                    } else {
                      $item_name = '';
                      $item_qty_available = '';
                      $item_qty = '';
                      $item_rate = '';
                      $item_discount = '';
                      $tax_percent = '';      
                    }

                    if($item_qty && $item_rate>0) {
                      $bill_amount = $item_qty * $item_rate;
                      $taxable_amount = $bill_amount - $item_discount;
                      $tax_amount = $tax_percent !== '' ? round($taxable_amount*$tax_percent/100,2):'';
                      $item_total = $taxable_amount + $tax_amount;
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
                    >
                    </td>
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
                    >&nbsp;</td>
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
                      <input type="hidden" class="taxAmount" id="taxAmount_<?php echo $i-1 ?>" />
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
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Gross Amount</td>
                    <td id="grossAmount" class="" style="font-size:16px;text-align:right;font-weight:bold;"></td>
                  </tr>
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">(-) Discount</td>
                    <td style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" id="totDiscount"></td>
                  </tr>
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Taxable Amount</td>
                    <td id="taxableAmount" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" class="taxableAmount"></td>
                  </tr>
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">(+) GST</td>
                    <td id="gstAmount" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" class="gstAmount"></td>
                  </tr>
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">(+/-) Round off</td>
                    <td id="roundOff" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;" class="roundOff"></td>
                  </tr>
                  <tr>
                    <td colspan="9" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;">Net Pay</td>
                    <td id="netPayBottom" class="netPay" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:right;"></td>
                  </tr>
              </tbody>
            </table>
          </div>
          <div class="text-center">
            <button class="btn btn-primary" id="Save" name="op" value="Save">
              <i class="fa fa-save"></i> <?php echo $btn_label ?>
            </button>
            <button class="btn btn-primary" id="SaveandPrint" name="op" value="SaveandPrint">
              <i class="fa fa-print"></i> Save &amp; Print
            </button>
            <button class="btn btn-danger btn-sm" id="SaveandPrintBill" name="op" value="SaveandPrintBill">
              <i class="fa fa-files-o"></i> Save &amp; Bill Print
            </button>
          </div>
          <?php 
            if( isset($offers_raw) && count($offers_raw) > 0 ):
              foreach($offers_raw as $offer_details):
                $offer_props = [];
                if( (int)$offer_details['promoType'] === 1) {
                  $offer_props['total'] = $offer_details['totalQty'];
                  $offer_props['free'] = $offer_details['freeQty'];
                } elseif( (int)$offer_details['promoType'] === 2) {
                  $offer_props['bv'] = $offer_details['billValue'];
                  $offer_props['dp'] = $offer_details['discountPercent'];
                } elseif( (int)$offer_details['promoType'] === 0) {
                }
          ?>
              <input 
                type="hidden" 
                id="<?php echo $offer_details['promoCode'] ?>" 
                name="<?php echo $offer_details['promoCode'] ?>"
                <?php echo http_build_query($offer_props, '', ' ') ?>
              />

          <?php 
              endforeach;
            endif; 
          ?>
        </form>  
      </div>
    </section>
    <!-- Panel ends --> 
  </div>
</div>
<!-- Basic Forms ends -->

<?php if($bill_to_print>0) : ?>
  <script>
    (function() {
      <?php if($print_format === 'bill'): ?>
        var printUrl = '/print-sales-bill-small?billNo='+<?php echo $bill_to_print ?>;
        var printWindow = window.open(printUrl, "_blank", "left=0,top=0,width=300,height=300,toolbar=0,scrollbars=0,status=0");
      <?php else: ?>
        var printUrl = '/print-sales-bill?billNo='+<?php echo $bill_to_print ?>;
        var printWindow = window.open(printUrl, "_blank", "scrollbars=yes, titlebar=yes, resizable=yes, width=400, height=400");     
      <?php endif; ?>
    })();
  </script>
<?php endif; ?>



<?php 
/*
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Discount method</label>
                  <div class="select-wrap">
                    <select class="form-control" name="discountMethod" id="discountMethod">
                      <?php 
                        foreach($offers as $key=>$value):
                          if($discount_method === $key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                        <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                    <?php if(isset($errors['discountMethod'])): ?>
                      <span class="error"><?php echo $errors['discountMethod'] ?></span>
                    <?php endif; ?>
                  </div>
                </div> 

                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Offer / Coupon code</label>
                  <input type="text" class="form-control noEnterKey" name="couponCode" id="couponCode" value="<?php echo $coupon_code ?>" <?php echo $coupon_code_input_style ?> />
                </div>                

*/ ?>