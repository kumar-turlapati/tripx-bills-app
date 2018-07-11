<?php
  use Atawa\Utilities;

  $current_date = date("d-m-Y");  

  if(isset($submitted_data['tranDate'])) {
    $tran_date = date("d-m-Y", strtotime($submitted_data['tranDate']));
  } else {
    $tran_date = $current_date;
  }
  if(isset($submitted_data['partyCode'])) {
    $party_code = $submitted_data['partyCode'];
  } else {
    $party_code = '';
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
  if(isset($submitted_data['bankCode'])) {
    $bank_code = $submitted_data['bankCode'];
  } else {
    $bank_code = '';
  }
  if(isset($submitted_data['refDate'])) {
    $ref_date = date("d-m-Y",strtotime($submitted_data['refDate']));
  } else {
    $ref_date = $current_date;
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
      <h2 class="hdg-reports text-center">Update Payment Voucher</h2>
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/fin/payment-vouchers" class="btn btn-default">
              <i class="fa fa-book"></i> Payment Vouchers List
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" id="paymentVocForm">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">            
              <label class="control-label">Voucher date (dd-mm-yyyy)</label>
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
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Party name</label>
              <select class="form-control" name="partyCode" id="partyCode">
                <?php 
                  foreach($parties as $key=>$value): 
                    if($party_code === $key) {
                      $selected = 'selected="selected"';
                    } else {
                      $selected = '';
                    }                      
                ?>
                  <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                <?php endforeach; ?>
              </select>
              <?php if(isset($form_errors['partyCode'])): ?>
                <span class="error"><?php echo $form_errors['partyCode'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Bill no.</label>
              <input 
                type="text" class="form-control" name="billNo" id="billNo" 
                value="<?php echo $bill_no ?>"
              >
              <?php if(isset($form_errors['billNo'])): ?>
                <span class="error"><?php echo $form_errors['billNo'] ?></span>
              <?php endif; ?>
            </div>                        
          </div>

          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Payment method</label>
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
              <?php if(isset($form_errors['paymentMode'])): ?>
                <span class="error"><?php echo $form_errors['paymentMode'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Amount</label>
              <input 
                type="text" class="form-control" name="amount" id="amount" 
                value="<?php echo $amount ?>"
              >
              <?php if(isset($form_errors['amount'])): ?>
                <span class="error"><?php echo $form_errors['amount'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Narration</label>
              <input 
                type="text" class="form-control" name="narration" id="narration" 
                value="<?php echo $narration ?>" maxlength="250"
              >
              <?php if(isset($form_errors['narration'])): ?>
                <span class="error"><?php echo $form_errors['narration'] ?></span>
              <?php endif; ?>
            </div>            
          </div>

          <div class="form-group" id="refInfo" style="<?php echo $div_style ?>">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Bank name</label>
              <select class="form-control" name="bankCode" id="bankCode">
                <?php 
                  foreach($bank_names as $key=>$value): 
                    if($bank_code === $key) {
                      $selected = 'selected="selected"';
                    } else {
                      $selected = '';
                    }                      
                ?>
                  <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                <?php endforeach; ?>                
              </select>
              <?php if(isset($form_errors['bankCode'])): ?>
                <span class="error"><?php echo $form_errors['bankCode'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Reference/Cheque/DD No.</label>
              <input 
                type="text" class="form-control" name="refNo" id="refNo" 
                value="<?php echo $ref_no ?>"
              >
              <?php if(isset($form_errors['refNo'])): ?>
                <span class="error"><?php echo $form_errors['refNo'] ?></span>
              <?php endif; ?>
            </div>                 
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">            
              <label class="control-label">Reference date (dd-mm-yyyy)</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $ref_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $ref_date ?>" size="16" type="text" readonly name="refDate" id="refDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($errors['refDate'])): ?>
                    <span class="error"><?php echo $errors['refDate'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <input type="hidden" id="vocNo" name="vocNo" value="<?php echo $voc_no ?>" />
          <div class="text-center">
            <button class="btn btn-success" id="Save">
              <i class="fa fa-save"></i> Save
            </button>
          </div>      
        </form>
      </div>
    </section>
  </div>
</div>