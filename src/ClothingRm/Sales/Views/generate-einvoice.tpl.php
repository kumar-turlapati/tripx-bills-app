<?php
  use Atawa\Utilities;

  // dump($form_data);

  $billing_address = '';
  $invoice_date = isset($form_data['invoiceDate']) ? date("d-m-Y", strtotime($form_data['invoiceDate'])) : '';
  $invoice_value = isset($form_data['netPay']) ? $form_data['netPay'] : '';
  $customer_name = isset($form_data['customerName']) ? $form_data['customerName'] : '';
  $customer_gst_no = isset($form_data['customerGstNo']) ? $form_data['customerGstNo'] : '';
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
  $qb_invoice_number = isset($form_data['billNo']) && $form_data['billNo'] !== '' ? $form_data['billNo'] : '';
  if(isset($submitted_data['gstInvoiceNo'])) {
    $gst_invoice_no = $submitted_data['gstInvoiceNo'];
  } elseif(isset($form_data['gstInvoiceNo']) && $form_data['gstInvoiceNo'] !== '') {
    $gst_invoice_no = $form_data['gstInvoiceNo'];
  } else {
    $gst_invoice_no = $form_data['gstDocNo'];
  }
  if(isset($form_data['shippingGstin']) && $form_data['shippingGstin'] !== '') {
    $shipping_gstin = $form_data['shippingGstin'];
  } else {
    $shipping_gstin = '';
  }
  if(isset($form_data['distance']) && $form_data['distance'] !== '') {
    $distance = $form_data['distance'];
  } else {
    $distance = '';
  }  
  if(isset($form_data['transporterGstin']) && $form_data['transporterGstin'] !== '') {
    $transporter_gstin = $form_data['transporterGstin'];
  } else {
    $transporter_gstin = '';
  }
  $is_irn_generated = isset($form_data['gstIrn']) && strlen($form_data['gstIrn']) > 0 ? true : false;
  // dump($is_irn_generated);
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
        <?php /*
        <h3 style="color: red;margin-top: 0px;">(*** This is a Sandbox environment and only used for Testing ***)</h3> */?>
        <div class="table-responsive">
          <table class="table table-striped table-hover font14" style="margin-bottom:10px;">
            <tr>
              <td width="20%" class="text-center valign-middle" style="font-size:14px;color:#2E1114;font-weight:bold;">QwikBills Invoice No.</td>
              <td width="20%" class="text-center valign-middle" style="font-size:14px;color:#2E1114;font-weight:bold;">QwikBills Invoice Date</td>  
              <td width="20%" class="text-center valign-middle" style="font-size:14px;color:#2E1114;font-weight:bold;">Customer Name</td>
              <td width="20%" class="text-center valign-middle" style="font-size:14px;color:#2E1114;font-weight:bold;">Customer GSTIN</td>
              <td width="10%" class="text-center valign-middle" style="font-size:14px;color:#2E1114;font-weight:bold;">Customer Pincode</td>
              <td width="10%" class="text-center valign-middle" style="font-size:14px;color:#2E1114;font-weight:bold;">Our Pincode</td>
            </tr>
            <tr>
              <td style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:center;"><?php echo $qb_invoice_number ?></td>
              <td style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:center;"><?php echo $invoice_date ?></td>
              <td style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:center;"><?php echo $customer_name ?></td>
              <td style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:center;"><?php echo $customer_gst_no ?></td>
              <td style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center; color: green;"><?php echo $form_data['pincode'] ?></td>
              <td style="vertical-align:middle;font-weight:bold;font-size:16px;text-align:center; color: green;"><?php echo $form_data['locPincode'] ?></td>
            </tr>
            <tr>
              <td width="10%" class="text-center valign-middle" style="font-size:14px;color:#2E1114;font-weight:bold;">Invoice Value (in Rs.)</td>
              <td width="5%"  colspan="6" class="text-center valign-middle" style="font-size:14px;color:#2E1114;font-weight:bold;">Billing Address</td>
            </tr>
            <tr>
              <td style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:center;"><?php echo number_format($invoice_value,2,'.','') ?></td>
              <td style="vertical-align:middle;font-weight:bold;font-size:14px;text-align:center;" colspan="6"><?php echo $billing_address ?></td>
            </tr>            
          </table>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="eInvoiceForm">
          <div class="panel" style="margin-bottom:5px;">
            <div class="panel-body" style="border: 1px dotted;">
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">GST Document No.*</label>
                  <input 
                    type="text" 
                    class="form-control noEnterKey" 
                    name="gstInvoiceNo" 
                    id="gstInvoiceNo" 
                    value="<?php echo $gst_invoice_no ?>" 
                    maxlength="15" 
                    <?php echo $is_irn_generated ? 'disabled="disabled"': '' ?>"
                  />
                  <?php if(isset($errors['gstInvoiceNo'])): ?>
                    <span class="error"><?php echo $errors['gstInvoiceNo'] ?></span>
                  <?php endif; ?>
                </div>
                <?php /*         
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">Shipping Location GSTIN</label>
                  <input type="text" class="form-control noEnterKey" name="shippingGstin" id="shippingGstin" value="<?php echo $shipping_gstin ?>" maxlength="15" />
                  <?php if(isset($errors['shippingGstin'])): ?>
                    <span class="error"><?php echo $errors['shippingGstin'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <p style="font-size: 12px; padding-top: 24px; color: red; font-weight: bold;"><span style="text-decoration: underline; color: #225992;font-weight: bold;"><i>Shipping Location GSTIN:</i></span> GSTIN of the entity to whom the goods are shipped. Enter GSTIN if the Shipping address is different. <span style="font-weight: bold;">QwikBills</span> will automatically fetch the relevant information from GSTIN portal.</p>
                </div> */?>
              </div>
            </div>
          </div>
          <?php if(!$is_irn_generated): ?>
            <h2 style="color: #225992;">eWayBill Information <span style="font-size: 12px; font-weight: bold;">( Distance is mandatory if you want to generate eWayBill along with eInvoice )</span></h2>
            <div class="panel" style="margin-bottom:5px;">
              <div class="panel-body" style="border: 1px dotted;">
                <div class="form-group">
                  <input type="hidden" name="locationID" value="<?php echo $form_data['locationID'] ?>" />
                  <input type="hidden" name="locationCode" value="<?php echo $form_data['locationCode'] ?>" />
                  <input type="hidden" name="invoiceCode" value="<?php echo $form_data['invoiceCode'] ?>" />
                  <input type="hidden" name="invoiceNo" value="<?php echo $form_data['billNo'] ?>" />
                  <div class="col-sm-12 col-md-4 col-lg-4">
                    <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">Transporter GSTIN</label>
                    <input type="text" class="form-control noEnterKey" name="transporterGstin" id="transporterGstin" value="<?php echo $transporter_gstin ?>" />
                    <?php if(isset($errors['transporterGstin'])): ?>
                      <span class="error"><?php echo $errors['transporterGstin'] ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="col-sm-12 col-md-4 col-lg-4">
                    <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">Distance (in Kms.)                   <a 
                      href="https://einvoice1.gst.gov.in/Others/GetPinCodeDistance" 
                      target="_blank" 
                      style="font-size:14px; color: green; font-weight: bold; padding-left: 10px;">
                        Distance finder&nbsp;<i class="fa fa-window-restore" style="text-decoration: none;" aria-hidden="true"></i>
                    </a></label>
                    <input type="text" class="form-control noEnterKey" name="distance" id="distance" value="<?php echo $distance ?>" />
                    <?php if(isset($errors['distance'])): ?>
                      <span class="error"><?php echo $errors['distance'] ?></span>
                    <?php endif; ?>
                  </div>
                  <?php /*
                  <div class="col-sm-12 col-md-4 col-lg-4">
                    <p style="font-size: 12px; padding-top: 35px; color: red; font-weight: bold;">If only Transporter GSTIN is provided, then only Part-A is generated.</p>
                  </div> */ ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
          <div class="text-center" style="margin-top: 15px;">
            <button class="btn btn-success" id="generateEinvoice" <?php echo $is_irn_generated ? 'disabled="disabled"': '' ?>>
              <i class="fa fa-money"></i> Generate eInvoice
            </button>
            <button class="btn btn-danger cancelButton" id="cancelGenerateEinvoice" <?php echo $is_irn_generated ? 'disabled="disabled"': '' ?>>
              <i class="fa fa-times"></i> Cancel
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>