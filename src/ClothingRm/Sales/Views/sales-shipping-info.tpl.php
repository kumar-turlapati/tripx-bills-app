<?php
  use Atawa\Utilities;
  $invoice_no = isset($form_data['billNo']) ? $form_data['billNo'] : '';
  $invoice_date = isset($form_data['invoiceDate']) ? date("d-m-Y", strtotime($form_data['invoiceDate'])) : '';
  $customer_name = isset($form_data['customerName']) ? $form_data['customerName'] : '';
  $store_name = isset($client_locations[$form_data['locationCode']]) ? $client_locations[$form_data['locationCode']] : '';
  $invoice_value = isset($form_data['netPay']) ? $form_data['netPay'] : '';
  $billing_address = '';
  if($form_data['address'] !== '') {
    $billing_address .= $form_data['address'];
  }
  if($form_data['cityName'] !== '') {
    $billing_address .= ', '.$form_data['cityName'];
  }
  if($form_data['stateID'] !== '') {
    $billing_address .= ', '.Utilities::get_location_state_name($form_data['stateID']);
  }
  if($form_data['pincode'] !== '') {
    $billing_address .= ' - '.$form_data['pincode'];
  }

  // dump($form_data, $submitted_data);

  // prefill shipping details
  if(isset($submitted_data['transporterName'])) {
    $transporter_name = $submitted_data['transporterName'];
  } elseif(isset($form_data['transporterName'])) {
    $transporter_name = $form_data['transporterName'];
  } else {
    $transporter_name = '';
  }
  if(isset($submitted_data['lrNo'])) {
    $lr_no = $submitted_data['lrNo'];
  } elseif(isset($form_data['lrNo'])) {
    $lr_no = $form_data['lrNo'];
  } else {
    $lr_no = '';
  }  
  if(isset($submitted_data['lrDate'])) {
    $lr_date = $submitted_data['lrDate'];
  } elseif(isset($form_data['lrDate']) && $form_data['lrDate'] !== '') {
    $lr_date = date("d-m-Y", strtotime($form_data['lrDate']));
  } else {
    $lr_date = date("d-m-Y");
  }
  if(isset($submitted_data['challanNo'])) {
    $challan_no = $submitted_data['challanNo'];
  } elseif(isset($form_data['challanNo'])) {
    $challan_no = $form_data['challanNo'];
  } else {
    $challan_no = '';
  }

  $qb_invoice_number = isset($form_data['billNo']) && $form_data['billNo'] !== '' ? $form_data['billNo'] : '';

  if(isset($form_data['customBillNo']) && $form_data['customBillNo'] !== '') {
    $invoice_no = $form_data['customBillNo'];
  } elseif(isset($form_data['billNo']) && $form_data['billNo'] !== '') {
    $invoice_no = $form_data['billNo'];
  } else {
    $invoice_no = '';
  }
  if(isset($submitted_data['wayBillNo']) && $submitted_data['wayBillNo'] !== '') {
    $way_bill_no = $submitted_data['wayBillNo'];
  } elseif(isset($form_data['wayBillNo']) && $form_data['wayBillNo'] !== '') {
    $way_bill_no = $form_data['wayBillNo'];
  } else {
    $way_bill_no = '';
  }

  if( isset($submitted_data['address']) && $submitted_data['address'] !== '' ) {
    $shipping_address = $submitted_data['address'];
  } elseif(isset($form_data['shippingAddress']) && $form_data['shippingAddress'] !== '') {
    $shipping_address = $form_data['shippingAddress'];
  } else {
    $shipping_address = $form_data['address'];
  }  
  if(isset($submitted_data['cityName']) && $submitted_data['cityName'] !== '') {
    $shipping_city_name = $submitted_data['cityName'];
  } elseif(isset($form_data['shippingCityName']) && $form_data['shippingCityName'] !== '') {
    $shipping_city_name = $form_data['shippingCityName'];
  } else {
    $shipping_city_name = $form_data['cityName'];
  }
  if(isset($submitted_data['stateID']) && $submitted_data['stateID'] !== '') {
    $shipping_state_id = $submitted_data['stateID'];
  } elseif(isset($form_data['shippingStateID']) && $form_data['shippingStateID'] !== '') {
    $shipping_state_id = $form_data['shippingStateID'];
  } else {
    $shipping_state_id = $form_data['stateID'];
  }
  if(isset($submitted_data['pincode']) && $submitted_data['pincode'] !== '') {
    $shipping_pincode = $submitted_data['pincode'];
  } elseif(isset($form_data['shippingPincode']) && $form_data['shippingPincode'] !== '') {
    $shipping_pincode = $form_data['shippingPincode'];
  } else {
    $shipping_pincode = $form_data['pincode'];
  }
  if(isset($submitted_data['mobileNo']) && $submitted_data['mobileNo'] !== '') {
    $shipping_mobile_no = $submitted_data['mobileNo'];
  } elseif(isset($form_data['shippingmobileNo']) && $form_data['shippingmobileNo'] !== '') {
    $shipping_mobile_no = $form_data['shippingmobileNo'];
  } else {
    $shipping_mobile_no = $form_data['mobileNo'];
  }
  if(isset($submitted_data['phones']) && $submitted_data['phones'] !== '') {
    $shipping_phones = $submitted_data['phones'];
  } elseif(isset($form_data['shippingPhones']) && $form_data['shippingPhones'] !== '') {
    $shipping_phones = $form_data['shippingPhones'];
  } else {
    $shipping_phones = $form_data['phones'];
  }
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/sales/list" class="btn btn-default">
              <i class="fa fa-book"></i> Sales Register
            </a>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font14" style="margin-bottom:10px;">
            <tr>
              <td width="10%" class="text-center valign-middle" style="font-size:14px;color:#2E1114;font-weight:bold;">QwikBills Invoice No.</td>
              <td width="5%"  class="text-center valign-middle" style="font-size:14px;color:#2E1114;font-weight:bold;">QwikBills Invoice Date</td>  
              <td width="15%" class="text-center valign-middle" style="font-size:14px;color:#2E1114;font-weight:bold;">Customer Name</td>
              <td width="10%" class="text-center valign-middle" style="font-size:14px;color:#2E1114;font-weight:bold;">Store Name</td>
            </tr>
            <tr>
              <td style="vertical-align:middle;font-weight:bold;font-size:12px;text-align:center;"><?php echo $qb_invoice_number ?></td>
              <td style="vertical-align:middle;font-weight:bold;font-size:12px;text-align:center;"><?php echo $invoice_date ?></td>
              <td style="vertical-align:middle;font-weight:bold;font-size:12px;text-align:center;"><?php echo $customer_name ?></td>
              <td style="vertical-align:middle;font-weight:bold;font-size:12px;text-align:center;"><?php echo $store_name ?></td>
            </tr>
            <tr>
              <td width="10%" class="text-center valign-middle" style="font-size:14px;color:#2E1114;font-weight:bold;">Invoice Value</td>
              <td width="5%"  colspan="3" class="text-center valign-middle" style="font-size:14px;color:#2E1114;font-weight:bold;">Billing Address</td>
            </tr>
            <tr>
              <td style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:center;"><?php echo number_format($invoice_value,2,'.','') ?></td>
              <td style="vertical-align:middle;font-weight:bold;font-size:12px;text-align:center;" colspan="3"><?php echo $billing_address ?></td>
            </tr>            
          </table>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="customerForm">
          <div class="panel" style="margin-bottom:10px;">
            <div class="panel-body" style="border: 1px dotted;">
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">Transporter Name</label>
                  <input type="text" class="form-control noEnterKey" name="transporterName" id="transporterName" value="<?php echo $transporter_name ?>">
                  <?php if(isset($errors['transporterName'])): ?>
                    <span class="error"><?php echo $errors['transporterName'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">L.R.No.</label>
                  <input type="text" class="form-control noEnterKey" name="lrNo" id="lrNo" value="<?php echo $lr_no ?>">
                  <?php if(isset($errors['lrNo'])): ?>
                    <span class="error"><?php echo $errors['lrNo'] ?></span>
                  <?php endif; ?>
                </div> 
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">L.R. Date</label>
                  <div class="col-lg-12" style="padding-left:0px;">
                    <div class="input-append date" data-date="<?php echo $lr_date ?>" data-date-format="dd-mm-yyyy">
                      <input class="span2" value="<?php echo $lr_date ?>" size="16" type="text" readonly name="lrDate" id="lrDate" style="height:34px;" />
                      <span class="add-on"><i class="fa fa-calendar"></i></span>
                    </div>
                    <?php if(isset($errors['lrDate'])): ?>
                      <span class="error"><?php echo $errors['lrDate'] ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">Challan No.</label>
                  <input type="text" class="form-control noEnterKey" name="challanNo" id="challanNo" maxlength="12" value="<?php echo $challan_no ?>" />
                  <?php if(isset($errors['challanNo'])): ?>
                    <span class="error"><?php echo $errors['challanNo'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">Way Bill No.</label>
                  <input type="text" class="form-control noEnterKey" name="wayBillNo" id="wayBillNo" value="<?php echo $way_bill_no ?>" />
                  <?php if(isset($errors['wayBillNo'])): ?>
                    <span class="error"><?php echo $errors['wayBillNo'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">Invoice No. (this will be sent in Message)</label>
                  <input type="text" class="form-control noEnterKey" name="billNo" id="billNo" value="<?php echo $invoice_no ?>" />
                  <?php if(isset($errors['billNo'])): ?>
                    <span class="error"><?php echo $errors['billNo'] ?></span>
                  <?php endif; ?>
                </div>    
              </div>
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">Shipping Address</label>
                  <input type="text" class="form-control noEnterKey" name="address1" id="address1" value="<?php echo $shipping_address ?>" />
                  <?php if(isset($errors['address1'])): ?>
                    <span class="error"><?php echo $errors['address1'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">Shipping City / Town</label>
                  <input type="text" class="form-control noEnterKey" name="cityName" id="cityName" value="<?php echo $shipping_city_name ?>" />
                  <?php if(isset($errors['cityName'])): ?>
                    <span class="error"><?php echo $errors['cityName'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">State</label>
                  <select class="form-control" name="stateID" id="stateID">
                    <?php 
                      foreach($states as $key=>$value): 
                        if((int)$shipping_state_id === (int)$key) {
                          $selected = 'selected="selected"';
                        } else {
                          $selected = '';
                        }
                    ?>
                      <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                    <?php endforeach; ?>              
                  </select>
                  <?php if(isset($form_errors['stateID'])): ?>
                    <span class="error"><?php echo $form_errors['stateID'] ?></span>
                  <?php endif; ?>
                </div>              
              </div>
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">Shipping Pincode</label>
                  <input type="text" class="form-control noEnterKey" name="pincode" id="pincode" maxlength="6" value="<?php echo $shipping_pincode ?>" />
                  <?php if(isset($errors['pincode'])): ?>
                    <span class="error"><?php echo $errors['pincode'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">Mobile No. (SMS will be pushed to this number)</label>
                  <input type="text" class="form-control noEnterKey" name="mobileNo" id="mobileNo" maxlength="10" value="<?php echo $shipping_mobile_no ?>" />
                  <?php if(isset($errors['mobileNo'])): ?>
                    <span class="error"><?php echo $errors['mobileNo'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">Phone Nos.</label>
                  <input type="text" class="form-control noEnterKey" name="phones" id="phones" value="<?php echo $shipping_phones ?>" />
                  <?php if(isset($errors['phones'])): ?>
                    <span class="error"><?php echo $errors['phones'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <div class="panel" style="margin-bottom:30px;">
            <div class="panel-body" style="border: 1px dotted;">
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;"><i class="fa fa-mobile" aria-hidden="true"></i> Push SMS?</label>
                  <select class="form-control" name="sendSMS" id="sendSMS">
                    <?php foreach($yes_no_options as $key=>$value): ?>
                      <option value="<?php echo $key ?>"><?php echo $value ?></option>
                    <?php endforeach; ?>
                  </select>
                  <?php if(isset($errors['sendSMS'])): ?>
                    <span class="error"><?php echo $errors['sendSMS'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-8 col-lg-8 m-bot15">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;"><i class="fa fa-info-circle" aria-hidden="true"></i> About SMS Charges</label>
                  <p style="font-size:14px;font-weight:bold;vertical-align:middle;color:#225992;">Standard SMS Charges @ Rs:0.40/message will be applicable and billed in your Next Billing Cycle.</p>
                </div>
              </div>
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-success" id="Save">
              <i class="fa fa-save"></i> Save
            </button>
            <button class="btn btn-danger cancelButton" id="shippingPage">
              <i class="fa fa-times"></i> Cancel
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>