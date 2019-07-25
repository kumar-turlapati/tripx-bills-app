<?php
  use Atawa\Utilities;

  if(isset($form_data['locationCode'])) {
    $location_code = $form_data['locationCode'];
  } elseif($default_location !== '') {
    $location_code = $default_location;
  } else {
    $location_code = '';
  }

  $current_date = isset($form_data['invoiceDate']) && $form_data['invoiceDate'] !== '' ? $form_data['invoiceDate'] : date("d-m-Y");
  $payment_method = isset($form_data['paymentMethod']) && $form_data['paymentMethod'] !== '' ? $form_data['paymentMethod'] : '';
  $mobile_no =  isset($form_data['mobileNo']) && $form_data['mobileNo'] !== '' ? $form_data['mobileNo'] : '';
  $name = isset($form_data['name']) && $form_data['name'] !== '' ? $form_data['name'] : '';

  $split_payment_cash = isset($form_data['splitPaymentCash']) ? $form_data['splitPaymentCash'] : '';
  $split_payment_card = isset($form_data['splitPaymentCard']) ? $form_data['splitPaymentCard'] : '';
  $split_payment_cn = isset($form_data['splitPaymentCn']) ? $form_data['splitPaymentCn'] : '';
  $split_payment_wallet = isset($form_data['splitPaymentWallet']) ? $form_data['splitPaymentWallet'] : '';

  $cn_no = isset($form_data['cnNo']) ? $form_data['cnNo'] : '';
  $card_number = isset($form_data['cardNo']) ? $form_data['cardNo'] : ''; 
  $card_auth_code = isset($form_data['authCode']) ? $form_data['authCode'] : '';

  $card_and_auth_style = (int)$payment_method === 0 || (int)$payment_method === 4 ? 'style="display:none;"' : '';
  $split_payment_input_style = (int)$payment_method === 2 ? '' : 'style="display:none"';
  $card_input_style = (int)$payment_method === 1 ? '' : 'style="display:none;"';
  $wallet_payment_style = (int)$payment_method === 4 ? '' : 'style="display:none;"';

  $wallet_id = isset($form_data['walletID']) ? $form_data['walletID'] : '';
  $wallet_ref_no = isset($form_data['walletRefNo']) ? $form_data['walletRefNo'] : '';

  // dump($errors);
  // dump($form_data);
?>

<div class="row">
  <div class="col-lg-12"> 
    
    <section class="panelBox">
      <div class="panelBody">

        <?php echo $flash_obj->print_flash_message(); ?>
       
        <div class="global-links actionButtons clearfix"> 
          <div class="pull-right text-right">
            <a href="/sales/list" class="btn btn-default"><i class="fa fa-inr"></i> Sales Register</a>&nbsp;
            <a href="/sales/entry" class="btn btn-default"><i class="fa fa-file-text-o"></i> Sales Entry - Normal</a> 
          </div>
        </div>
        
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="comboBillEntry">
          <div class="table-responsive">
            <table class="table table-striped table-hover font12" style="margin-bottom:0px;">
              <thead>
                <tr>
                  <th width="4%"  class="text-center">Sno.</th>
                  <th width="5%"  class="text-center">Barcode</th>                                    
                  <th width="5%"  class="text-center">Combo Item Code</th>                                    
                  <th width="5%"  class="text-center">Sale qty.</th>
                  <th width="18%" class="text-center">Item name</th>
                  <th width="5%"  class="text-center">Available<br />qty.</th>
                  <th width="8%"  class="text-center">Rate</th>
                  <th width="10%" class="text-center">Amount</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $tot_gross_amount = $tot_round_off = $tot_netpay = 0;
                  for($i=1;$i<=7;$i++):
                    $bill_amount = $taxable_amount = $item_total = 0;
                    $ex_index = $i-1;
                    if(isset($form_data['itemDetails']['comboItemCode'][$ex_index])) {
                      $item_code = $form_data['itemDetails']['comboItemCode'][$ex_index];
                      $item_name = $form_data['itemDetails']['itemName'][$ex_index];
                      $item_qty_available = $form_data['itemDetails']['itemAvailQty'][$ex_index];
                      $item_qty = $form_data['itemDetails']['comboItemSoldQty'][$ex_index];
                      $item_rate = $form_data['itemDetails']['itemRate'][$ex_index];
                      $item_discount = $form_data['itemDetails']['itemDiscount'][$ex_index];
                    } else {
                      $item_code = '';
                      $item_name = '';
                      $item_qty_available = '';
                      $item_qty = '';
                      $item_rate = '';
                      $item_discount = '';
                    }

                    if($item_qty && $item_rate>0) {
                      $bill_amount = $item_qty*$item_rate;
                      $taxable_amount = $bill_amount - $item_discount;
                      $tot_gross_amount += $taxable_amount;
                    }
                ?>
                  <tr>
                    <td align="right" style="vertical-align:middle;"><?php echo $i ?></td>
                    <td align="center" style="vertical-align:middle;" title="Info: Remove the barcode to type Combo code">
                      <input 
                        type="text" 
                        name="itemDetails[barcode][]" 
                        id="cbarcode_<?php echo $i-1 ?>" 
                        size="13"
                        class="comboBarcode" 
                        style="border:1px dashed #00AEFF;font-weight:bold;color:#AA3E39;"
                        disabled
                      />
                    </td>
                    <td style="vertical-align:middle;" align="center">
                      <input 
                        type="text" 
                        name="itemDetails[comboItemCode][]" 
                        id="cicode_<?php echo $i-1 ?>" 
                        size="20" 
                        class="comboItemCode" 
                        value="<?php echo $item_code ?>"
                        style="width:100px;border:1px dashed #E8473A;font-weight:bold;color:#000;"
                      />
                    </td>
                    <td style="vertical-align:middle;">
                      <input
                        type="text"
                        id="qty_<?php echo $i-1 ?>"
                        name="itemDetails[comboItemSoldQty][]"
                        size="10"
                        value="<?php echo $item_qty ?>"
                        class="form-control comboItemQty"
                        index="<?php echo $i-1 ?>"
                        style="border:1px solid #000;color:#000;font-weight:bold;"
                        title="Enter Qty. and press enter to calculate item amount"                     
                      />
                    </td>
                    <td id="inameTd_<?php echo $i-1 ?>" style="vertical-align:middle;text-align:left;font-size:16px;color:#225992;font-weight:bold;">
                      <?php echo $item_name ?>
                    </td>
                    <td id="qtyavaTd_<?php echo $i-1 ?>" style="vertical-align:middle;text-align:right;font-size:16px;color:green;font-weight:bold;">
                      <?php echo $item_qty_available > 0 ? number_format($item_qty_available, 2, '.', '') : '' ?>
                    </td>
                    <td id="mrpTd_<?php echo $i-1 ?>" style="vertical-align:middle;text-align:right;font-size:16px;color:#225992;font-weight:bold;">
                      <?php echo $item_rate > 0 ? number_format($item_rate, 2, '.', '') : '' ?>
                    </td>
                    <td id="grossAmountTd_<?php echo $i-1 ?>" style="vertical-align:middle;text-align:right;font-size:16px;color:#2E1114;font-weight:bold;">
                      <?php echo $bill_amount > 0 ? number_format($bill_amount, 2, '.', '') : '' ?>
                    </td>
                    <input type="hidden" id = "iname_<?php echo $i-1 ?>" name = "itemDetails[itemName][]" value="<?php echo $item_name ?>" />
                    <input type="hidden" id = "qtyava_<?php echo $i-1 ?>" name = "itemDetails[itemAvailQty][]" value="<?php echo $item_qty_available ?>" />
                    <input type="hidden" id = "mrp_<?php echo $i-1 ?>" name = "itemDetails[itemRate][]" value="<?php echo $item_rate ?>" />
                    <input type="hidden" id = "discount_<?php echo $i-1 ?>" name = "itemDetails[itemDiscount][]" value="<?php echo $item_discount ?>" />
                  </tr>
                  <?php 
                    /* Show error tr if there are any errors in the line item */
                    if( 
                        isset($errors['itemDetails']['itemName'][$i-1]) ||
                        isset($errors['itemDetails']['comboItemCode'][$i-1]) ||
                        isset($errors['itemDetails']['comboItemSoldQty'][$i-1]) ||
                        isset($errors['itemDetails']['itemAvailQty'][$i-1]) ||
                        isset($errors['itemDetails']['itemRate'][$i-1])
                      ) {
                  ?>
                      <tr class="rowErrors" id="rowError_<?php echo $i-1 ?>">
                        <td style="border:none;">&nbsp;</td>
                        <td style="border:none;">&nbsp;</td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['comboItemCode'][$i-1]) ? '<span class="error">Invalid</span>': '' ?></td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['comboItemSoldQty'][$i-1]) ? '<span class="error">Invalid</span>': '' ?></td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemName'][$i-1]) ? '<span class="error">Invalid</span>': '' ?></td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemAvailQty'][$i-1]) ? '<span class="error">Invalid</span>': '' ?></td>
                        <td style="border:none;text-align:center;"><?php echo isset($errors['itemDetails']['itemRate'][$i-1]) ? '<span class="error">Invalid</span>': '' ?></td>
                      </tr>
                  <?php } ?>
                <?php 
                  endfor;
                  $item_total_round = round($tot_gross_amount, 0);
                  $round_off = $item_total_round - $tot_gross_amount;
                ?>
                  <tr>
                    <td colspan="3" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;">Gross Amount (in Rs.)</td>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;">Round Off (in Rs.)</td>
                    <td colspan="3" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;">NetPay (in Rs.)</td>
                  </tr>
                  <tr>
                    <td colspan="3" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;" class="grossAmount">
                      <?php echo $tot_gross_amount > 0 ? number_format($tot_gross_amount, 2, '.', '') : '' ?>
                    </td>
                    <td colspan="2" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;" class="roundOff">
                      <?php echo $round_off !== '' ? number_format($round_off, 2, '.', '') : '' ?>
                    </td>
                    <td colspan="3" style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center;" class="netPay">
                      <?php echo $item_total_round > 0 ? number_format($item_total_round, 2, '.', '') : '' ?>
                    </td>
                  </tr>
              </tbody>
            </table>
          </div>
          <div class="panel" style="margin-top:5px;">
            <div class="panel-body" style="padding-top:10px;border:2px dotted #000">
              <div class="form-group">
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label labelStyle">Date (dd-mm-yyyy)</label>
                    <?php if(Utilities::is_admin()): ?>
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
                    <?php else: ?>
                      <div style="font-size:16px;font-weight:bold;color:#225992;"><?php echo $current_date ?></div>
                      <input type="hidden" id="saleDate" name="saleDate" value="<?php echo $current_date ?>" />
                    <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label labelStyle">Store name</label>
                  <div class="select-wrap">
                    <select class="form-control" name="locationCode" id="locationCode">
                      <?php 
                        foreach($client_locations as $key=>$value): 
                          $location_key_a = explode('`', $key);
                          if($location_code === $location_key_a[0]) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                        <option value="<?php echo $location_key_a[0] ?>" <?php echo $selected ?>>
                          <?php echo $value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <?php if(isset($errors['locationCode'])): ?>
                    <span class="error"><?php echo $errors['locationCode'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label labelStyle">Payment method</label>
                  <div class="select-wrap">
                    <select class="form-control" name="paymentMethod" id="comPaymentMethod">
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
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label labelStyle">Customer mobile number</label>
                  <input type="text" class="form-control noEnterKey" name="mobileNo" id="mobileNo" maxlength="10" value="<?php echo $mobile_no ?>">
                  <?php if(isset($errors['mobileNo'])): ?>
                    <span class="error"><?php echo $errors['mobileNo'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label labelStyle">Customer name</label>
                  <input type="text" class="form-control noEnterKey" name="name" id="name" maxlength="20" value="<?php echo $name ?>">
                  <?php if(isset($errors['name'])): ?>
                    <span class="error"><?php echo $errors['name'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label labelStyle">Discount (in Rs.)</label>
                  <input type="text" class="form-control" name="comboDiscount" id="comboDiscount" value="<?php //echo $split_payment_cash ?>" style="border:1px dotted;color:red;">
                  <?php if(isset($errors['comboDiscount'])): ?>
                    <span class="error"><?php echo $errors['comboDiscount'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="form-group" id="comboSplitPaymentMethods" <?php echo $split_payment_input_style ?>>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label labelStyle">By Cash (Rs.)</label>
                  <input type="text" class="form-control noEnterKey" name="splitPaymentCash" id="splitPaymentCash" value="<?php echo $split_payment_cash ?>">
                  <?php if(isset($errors['splitPaymentCash'])): ?>
                    <span class="error"><?php echo $errors['splitPaymentCash'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label labelStyle">By Card (Rs.)</label>
                  <input type="text" class="form-control noEnterKey" name="splitPaymentCard" id="splitPaymentCard" value="<?php echo $split_payment_card ?>">
                  <?php if(isset($errors['splitPaymentCard'])): ?>
                    <span class="error"><?php echo $errors['splitPaymentCard'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label labelStyle">By UPI/EMI Cards (Rs.)</label>
                  <input type="text" class="form-control noEnterKey" name="splitPaymentWallet" id="splitPaymentWallet" value="<?php echo $split_payment_wallet ?>">
                  <?php if(isset($errors['splitPaymentWallet'])): ?>
                    <span class="error"><?php echo $errors['splitPaymentWallet'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label labelStyle">By Credit Note (Rs.)</label>
                  <input type="text" class="form-control noEnterKey" name="splitPaymentCn" id="splitPaymentCn" value="<?php echo $split_payment_cn ?>">
                  <?php if(isset($errors['splitPaymentCn'])): ?>
                    <span class="error"><?php echo $errors['splitPaymentCn'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label labelStyle">Credit Note No.</label>
                  <input
                    type="text"
                    size="10"
                    id="cnNo"
                    name="cnNo"
                    maxlength="8"
                    style="font-weight:bold;font-size:14px;padding-left:5px;border:2px dashed red"
                    value="<?php echo $cn_no ?>"
                    class="form-control"
                  />
                  <?php if(isset($errors['cnNo'])): ?>
                    <span class="error"><?php echo $errors['cnNo'] ?></span>
                  <?php endif; ?>
                </div>                
              </div>
              <div class="form-group" id="comboWalletDetails" <?php echo $wallet_payment_style ?>>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label labelStyle">Choose eWallet/UPI/EMI Cards</label>
                  <div class="select-wrap">
                    <select class="form-control" name="walletID" id="walletID">
                      <?php 
                        foreach($wallets as $key=>$value):
                          if((int)$wallet_id === (int)$key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                        <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <?php if(isset($errors['walletID'])): ?>
                    <span class="error"><?php echo $errors['walletID'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label labelStyle">eWallet/UPI/EMI Cards Ref.No.</label>
                  <input
                    type="text"
                    size="20"
                    id="walletRefNo"
                    name="walletRefNo"
                    maxlength="8"
                    value="<?php echo $wallet_ref_no ?>"
                  />
                  <?php if(isset($errors['walletRefNo'])): ?>
                    <span class="error"><?php echo $errors['walletRefNo'] ?></span>
                  <?php endif; ?>
                </div>                
              </div>
              <div class="form-group" id="comboCardDetails" <?php echo $card_input_style ?>>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label labelStyle">Card No.</label>
                  <input type="text" class="form-control noEnterKey" name="cardNo" id="cardNo" value="<?php echo $card_number ?>" maxlength="6" />
                  <?php if(isset($errors['cardNo'])): ?>
                    <span class="error"><?php echo $errors['cardNo'] ?></span>
                  <?php endif; ?>                   
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label labelStyle">Auth Code</label>
                  <input type="text" class="form-control noEnterKey" name="authCode" id="authCode" value="<?php echo $card_auth_code ?>" maxlength="10" />
                  <?php if(isset($errors['authCode'])): ?>
                    <span class="error"><?php echo $errors['authCode'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>          
          <div class="text-center">
            <button class="btn btn-primary" id="SaveCombo" name="op" value="Save">
              <i class="fa fa-save"></i> Save &amp; Print
            </button>
            <button class="btn btn-danger cancelButton" id="scombos">
              <i class="fa fa-times"></i> Cancel
            </button>            
          </div>
        </form>  
      </div>
    </section>
  </div>
</div>

<?php if($bill_to_print !== '' && $print_format === 'combo'): ?>
  <script>
    (function() {
      var printUrl = '/print-sales-bill-combo?billNo=<?php echo $bill_to_print ?>';
      var printWindow = window.open(printUrl, "_blank", "left=0,top=0,width=300,height=300,toolbar=0,scrollbars=0,status=0");
      printWindow.print();
    })();
  </script>
<?php endif; ?>