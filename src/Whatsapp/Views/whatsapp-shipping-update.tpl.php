<?php
  use Atawa\Utilities;

  // dump($sales_data);

  $disclaimer_a = [1=>'Yes', 0=>'No'];
  $disclaimer_default_string = 'Please note that this message is for information purposes only and not used as an authorization to receive the Goods from the Shipping agency.';

  $indent_no = isset($sales_data['indentNo']) && $sales_data['indentNo'] !== '' ? $sales_data['indentNo'] : '';
  $customer_name_invoice = isset($sales_data['customerName']) && $sales_data['customerName'] !== '' ? $sales_data['customerName'] : '';
  $whatsapp_numbers_invoice = isset($sales_data['whatsappNumbers']) && $sales_data['whatsappNumbers'] !== '' ? $sales_data['whatsappNumbers'] : '';

  $whatsapp_optin = isset($sales_data['whatsappOptin']) && $sales_data['whatsappOptin'] !== '' ? $sales_data['whatsappOptin'] : '';
  $invoice_code = isset($sales_data['invoiceCode']) && $sales_data['invoiceCode'] !== '' ? $sales_data['invoiceCode'] : '';
  $customer_name = isset($form_data['customerName']) ? $form_data['customerName'] : $customer_name_invoice;
  $order_nos = isset($form_data['orderNos']) ? $form_data['orderNos'] : $indent_no;
  // $invoice_nos = isset($form_data['invoiceNos']) && $form_data['invoiceNos'] !== '' ? 
  //                $form_data['invoiceNos'] : $sales_data['billNo'];
  $lr_case_nos = isset($form_data['lrCaseNos']) && $form_data['lrCaseNos'] !== '' ? 
                 $form_data['lrCaseNos'] : $sales_data['lrNo'];
  $lr_date = isset($form_data['lrDate']) && $form_data['lrDate'] !== '' ? 
             date('d-m-Y', strtotime($form_data['lrDate'])) : $sales_data['lrDate'] !== '1970-01-01' ? date('d-m-Y', strtotime($sales_data['lrDate'])) : date("d-m-Y");

  $eway_bill_no = isset($form_data['eWayBillNo']) && $form_data['eWayBillNo'] !== '' ? $form_data['eWayBillNo'] : $sales_data['wayBillNo'];
  $transporter_name = isset($form_data['transporterName']) && $form_data['transporterName'] !== '' ? $form_data['transporterName'] : $sales_data['transporterName'];

  $contact_no_for_queries = isset($form_data['contactNoForQueries']) ? $form_data['contactNoForQueries'] : $delivery_contact;
  $show_disclaimer = isset($form_data['showDisclaimer']) ? $form_data['showDisclaimer'] : 1;
  $disclaimer_message = isset($form_data['disclaimerMessage']) && $form_data['disclaimerMessage'] !== '' ? $form_data['disclaimerMessage'] : $disclaimer_default_string;

  if(isset($form_data['whatsappNo']) && $form_data['whatsappNo'] !== '') {
    $whatsapp_no =  $form_data['whatsappNo'];
  } elseif((int)$whatsapp_optin) {
    $whatsapp_no =  $whatsapp_numbers_invoice;
  } else {
    $whatsapp_no = '';
  }

  if(isset($form_data['invoiceNos']) && $form_data['invoiceNos'] !== '') {
    $invoice_nos = $form_data['invoiceNos'];
  } elseif($sales_data['customBillNo'] !== '') {
    $invoice_nos = $sales_data['customBillNo'];
  } elseif($form_data['gstDocNo'] !== '') {
    $invoice_nos = $sales_data['gstDocNo'];
  } else {
    $invoice_nos = $sales_data['billNo'];
  }
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>

        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/lead/import" class="btn btn-default">
              <i class="fa fa-whatsapp"></i> Message Statuses 
            </a>&nbsp;
          </div>
        </div>
        
        <form class="form-validate form-horizontal" method="POST" autocomplete="Off" id="waShippingUpdateFrm">
          <div class="form-group">
            <div class="col-sm-12 col-md-6 col-lg-6 m-bot10">
              <label class="control-label labelStyle">Customer name&nbsp;<i style="font-size: 10px;">max 50 chars.</i></label>
              <input 
                type="text" 
                class="form-control noEnterKey cnameAc" 
                name="customerName" 
                id="customerName" 
                value="<?php echo $customer_name ?>"
                maxlength="50"
              >
              <?php if(isset($form_errors['customerName'])): ?>
                <span class="error"><?php echo $form_errors['customerName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-6 m-bot10">
              <label class="control-label labelStyle">Customer Whatsapp number&nbsp;<i>(use , for multiple numbers)&nbsp;<i style="font-size: 10px;">max 55 chars.</i></i></label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="whatsappNo" 
                id="whatsappNo" 
                value="<?php echo $whatsapp_no ?>"
                style="border: 2px dashed; border-color: 'green';"
                maxlength="50"
              >
              <?php if(isset($form_errors['whatsappNo'])): ?>
                <span class="error"><?php echo $form_errors['whatsappNo'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-6 col-lg-6 m-bot10">
              <label class="control-label labelStyle">Order / Indent Nos. <i>(use , for multiple orders)</i>&nbsp;<i style="font-size: 10px;">max 100 chars.</i></label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="orderNos" 
                id="orderNos" 
                value="<?php echo $order_nos ?>"
                maxlength="100"
              >
              <?php if(isset($form_errors['orderNos'])): ?>
                <span class="error"><?php echo $form_errors['orderNos'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-6 m-bot10">
              <label class="control-label labelStyle">Invoice Nos. <i>(use , for multiple invoices)</i>&nbsp;<i style="font-size: 10px;">max 150 chars.</i></label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="invoiceNos" 
                id="invoiceNos" 
                value="<?php echo $invoice_nos ?>"
                maxlength="150"
              >
              <?php if(isset($form_errors['invoiceNos'])): ?>
                <span class="error"><?php echo $form_errors['invoiceNos'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot10">
              <label class="control-label labelStyle">Transporter name&nbsp;<i style="font-size: 10px;">max 30 chars.</i></label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="transporterName" 
                id="transporterName" 
                value="<?php echo $transporter_name ?>"
                maxlength="30"
              >
              <?php if(isset($form_errors['transporterName'])): ?>
                <span class="error"><?php echo $form_errors['transporterName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot10">
              <label class="control-label labelStyle">L.R. Nos. / Cases (or) Vehicle No.&nbsp;<i style="font-size: 10px;">max 100 chars.</i></label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="lrCaseNos" 
                id="lrCaseNos" 
                value="<?php echo $lr_case_nos ?>"
                maxlength="100"
              >
              <?php if(isset($form_errors['lrCaseNos'])): ?>
                <span class="error"><?php echo $form_errors['lrCaseNos'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot10">
              <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">L.R. Date</label>
              <div class="col-lg-12" style="padding-left:0px;">
                <div class="input-append date" data-date="<?php echo $lr_date ?>" data-date-format="dd-mm-yyyy">
                  <input class="span2" value="<?php echo $lr_date ?>" size="16" type="text" readonly name="lrDate" id="lrDate" style="height:34px;" />
                  <span class="add-on"><i class="fa fa-calendar"></i></span>
                </div>
                <?php if(isset($form_errors['lrDate'])): ?>
                  <span class="error"><?php echo $form_errors['lrDate'] ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot10">
              <label class="control-label labelStyle">Eway Bill No.&nbsp;<i style="font-size: 10px;">max 70 chars.</i></label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="eWayBillNo" 
                id="eWayBillNo" 
                value="<?php echo $eway_bill_no ?>"
                maxlength="70"
              >
              <?php if(isset($form_errors['eWayBillNo'])): ?>
                <span class="error"><?php echo $form_errors['eWayBillNo'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-8 col-lg-8 m-bot10">
              <label class="control-label labelStyle">Contact numbers for queries <i>(use , for multiple numbers)</i>&nbsp;<i style="font-size: 10px;">max 50 chars.</i></label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="contactNoForQueries" 
                id="contactNoForQueries" 
                value="<?php echo $contact_no_for_queries ?>"
                maxlength="50"
              >
              <?php if(isset($form_errors['contactNoForQueries'])): ?>
                <span class="error"><?php echo $form_errors['contactNoForQueries'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot10">
              <label class="control-label labelStyle">Show disclaimer in the message?</label>
              <div class="select-wrap m-bot15">
                <select class="form-control" name="showDisclaimer" id="showDisclaimer">
                  <?php 
                    foreach($disclaimer_a as $key=>$value):
                      if((int)$key === (int)$show_disclaimer) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }
                  ?>
                  <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
               </div>
            </div>
            <div class="col-sm-12 col-md-8 col-lg-8 m-bot10" id="disclaimerMessageDiv" style="<?php echo (int)$show_disclaimer === 1 ? '' : 'display: none;'?>">
              <label class="control-label labelStyle">Disclaimer message&nbsp;<i style="font-size: 10px;">max 142 chars.</i></label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="disclaimerMessage" 
                id="disclaimerMessage" 
                value="<?php echo $disclaimer_message ?>"
                maxlength="142"
                readonly
              >
              <?php if(isset($form_errors['disclaimerMessage'])): ?>
                <span class="error"><?php echo $form_errors['disclaimerMessage'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="text-center margin-top-20" style="margin-top: 20px;">
            <button class="btn btn-danger cancelButton" id="waShippingUpdateCancel">
              <i class="fa fa-times"></i> Cancel
            </button>&nbsp;&nbsp;
            <button class="btn btn-primary" id="waShippingUpdate">
              <i class="fa fa-send"></i> Send message
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>
