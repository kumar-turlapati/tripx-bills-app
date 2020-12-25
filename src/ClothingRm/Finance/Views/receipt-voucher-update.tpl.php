<?php
  use Atawa\Utilities;

  $current_date = date("d-m-Y");

  // dump($submitted_data);

  if(isset($submitted_data['tranDate'])) {
    $tran_date = $submitted_data['tranDate'];
  } else {
    $tran_date = $current_date;
  }
  if(isset($submitted_data['partyName'])) {
    $party_name = $submitted_data['partyName'];
  } else {
    $party_name = '';
  }
  if(isset($submitted_data['billNo'])) {
    $bill_no = $submitted_data['billNo'];
  } else {
    $bill_no = '';
  }  
  if(isset($submitted_data['amount'])) {
    $amount = $submitted_data['amount'];
  } else {
    $amount = '';
  }
  if(isset($submitted_data['paymentMode'])) {
    $mode = $submitted_data['paymentMode'];
  } else {
    $mode = 'c';
  }
  if(isset($submitted_data['narration'])) {
    $narration = $submitted_data['narration'];
  } else {
    $narration = '';
  }
  if(isset($submitted_data['refNo'])) {
    $ref_no = $submitted_data['refNo'];
  } else {
    $ref_no = '';
  }
  if(isset($submitted_data['bankName'])) {
    $bank_name = $submitted_data['bankName'];
  } else {
    $bank_name = '';
  }
  if(isset($submitted_data['remarks'])) {
    $remarks = $submitted_data['remarks'];
  } else {
    $remarks = '';
  }
  if(isset($submitted_data['processStatus'])) {
    $process_status = $submitted_data['processStatus'];
  } elseif(isset($submitted_data['isApproved'])) {
    $process_status = $submitted_data['isApproved'];    
  } else {
    $process_status = 0;
  }  
  if($mode==='b' || $mode==='p') {
    $div_style = '';
  } else {
    $div_style = 'display:none;';
  }
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Create Receipt Voucher</h2>
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/fin/receipt-vouchers" class="btn btn-default">
              <i class="fa fa-book"></i> Receipt Vouchers List
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" id="receiptVocForm">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label labelStyle">Voucher date (dd-mm-yyyy)</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $tran_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $tran_date ?>" size="16" type="text" readonly name="tranDate" id="tranDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($errors['tranDate'])): ?>
                    <span class="error"><?php echo $errors['tranDate'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label labelStyle">Party name</label>
              <input 
                type="text" 
                class="form-control cnameAc noEnterKey" 
                name="partyName" 
                id="partyName"
                value="<?php echo $party_name ?>"
              />
              <?php if(isset($form_errors['partyName'])): ?>
                <span class="error"><?php echo $form_errors['partyName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-1 col-lg-1" style="padding:2px 0 0 0;">
              <label>&nbsp;</label>
              <button class="btn btn-sm btn-danger" id="custBillNos">Get Bill Nos.</button>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Choose bill no.</label>
              <div class="select-wrap">
                <select
                  class="form-control noEnterKey"
                  name="billNo" 
                  id="custBillNo"
                  <?php echo $bill_no === '' ? 'disabled' : ''?>
                >
                  <option value="">Choose</option>
                  <?php if($bill_no !== ''): ?>
                    <option value="<?php echo $bill_no ?>" selected><?php echo $bill_no ?></option>
                  <?php endif; ?>
                </select>
              </div>
              <?php if(isset($form_errors['billNo'])): ?>
                <span class="error"><?php echo $form_errors['billNo'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Payment method</label>
              <div class="select-wrap">              
                <select class="form-control" name="paymentMode" id="paymentMode">
                  <?php 
                    foreach($payment_methods as $key=>$value): 
                      if($mode === $key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }                      
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>              
                </select>
              </div>
              <?php if(isset($form_errors['paymentMode'])): ?>
                <span class="error"><?php echo $form_errors['paymentMode'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Amount (in Rs.)</label>
              <input 
                type="text" 
                class="form-control noEnterKey" 
                name="amount" 
                id="amount"
                value="<?php echo $amount ?>"
              >
              <?php if(isset($form_errors['amount'])): ?>
                <span class="error"><?php echo $form_errors['amount'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Narration</label>
              <input 
                type="text" 
                class="form-control noEnterKey"
                name="narration"
                id="narration"
                value="<?php echo $narration ?>" 
                maxlength="250"
              >
              <?php if(isset($form_errors['narration'])): ?>
                <span class="error"><?php echo $form_errors['narration'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-3">
              <label class="control-label labelStyle">Instrument status (for Bank only)</label>
              <div class="select-wrap">
                <select
                  class="form-control noEnterKey"
                  name="processStatus"
                  id="processStatus"
                >
                  <?php 
                    foreach($process_status_a as $status => $status_name): 
                      if((int)$status === (int)$process_status) {
                        $selected = 'selected=selected';
                      } else {
                        $selected = '';
                      }
                  ?>
                    <option value="<?php echo $status ?>" <?php echo $selected ?>><?php echo $status_name ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if(isset($form_errors['processStatus'])): ?>
                <span class="error"><?php echo $form_errors['processStatus'] ?></span>
              <?php endif; ?>
            </div>            
          </div>
          <div class="form-group" id="refInfo" style="<?php echo $div_style ?>">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Institution name</label>
              <input 
                class="form-control"
                name="bankName"
                id="bankName"
                value="<?php echo $bank_name ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['bankName'])): ?>
                <span class="error"><?php echo $form_errors['bankName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Instrument no.</label>
              <input 
                type="text"
                class="form-control"
                name="refNo"
                id="refNo"
                value="<?php echo $ref_no ?>"
                maxlength="20"
              >
              <?php if(isset($form_errors['refNo'])): ?>
                <span class="error"><?php echo $form_errors['refNo'] ?></span>
              <?php endif; ?>
            </div>                 
            <div class="col-sm-12 col-md-3 col-lg-3"> 
              <label class="control-label labelStyle">Instrument date (dd-mm-yyyy)</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $current_date ?>" size="16" type="text" readonly name="refDate" id="refDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($errors['refDate'])): ?>
                    <span class="error"><?php echo $errors['refDate'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-12 col-lg-12">
              <label class="control-label labelStyle">Remarks</label>
              <input 
                type="text" 
                class="form-control noEnterKey"
                name="remarks"
                id="remarks"
                value="<?php echo $remarks ?>" 
                maxlength="300"
              >
              <?php if(isset($form_errors['remarks'])): ?>
                <span class="error"><?php echo $form_errors['remarks'] ?></span>
              <?php endif; ?>
            </div>            
          </div>
          <div class="text-center">
            <br />
            <button class="btn btn-success" id="Save">
              <i class="fa fa-save"></i> Save
            </button>
          </div>          
        </form>
      </div>
    </section>
  </div>
</div>