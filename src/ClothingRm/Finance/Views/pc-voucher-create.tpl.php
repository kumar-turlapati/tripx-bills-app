<?php
  use Atawa\Utilities;

  $current_date = date("d-m-Y");

  if(isset($submitted_data['tranDate'])) {
    $tran_date = $submitted_data['tranDate'];
  } else {
    $tran_date = $current_date;
  }
  if(isset($submitted_data['amount'])) {
    $amount = $submitted_data['amount'];
  } else {
    $amount = '';
  }
  if(isset($submitted_data['action'])) {
    $action = $submitted_data['action'];
  } else {
    $action = 'payment';
  }
  if(isset($submitted_data['refNo'])) {
    $ref_no = $submitted_data['refNo'];
  } else {
    $ref_no = '';
  }
  if(isset($submitted_data['refDate'])) {
    $ref_date = $submitted_data['refDate'];
  } else {
    $ref_date = '';
  }
  if(isset($submitted_data['narration'])) {
    $narration = $submitted_data['narration'];
  } else {
    $narration = '';
  }
  if(isset($submitted_data['locationCode'])) {
    $location_code = $submitted_data['locationCode'];
  } else {
    $location_code = $default_location;
  }
  if(isset($submitted_data['cnNo'])) {
    $cn_no = $submitted_data['cnNo'];
  } else {
    $cn_no = '';
  }

  $cn_no_class = $action === 'receipt' ? 'disabled' : '';
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Create Cash Voucher</h2>
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/fin/cash-vouchers" class="btn btn-default">
              <i class="fa fa-book"></i> Cash Vouchers List
            </a>
            <a href="/fin/cash-book" class="btn btn-default">
              <i class="fa fa-inr"></i> Cash Book
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="cashVoucherForm">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
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
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Voucher type</label>
              <select class="form-control" name="action" id="action">
                <?php
                  foreach($pc_tran_types as $key=>$value): 
                    if($action === $key) {
                      $selected = 'selected="selected"';
                    } else {
                      $selected = '';
                    }                      
                ?>
                  <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                <?php endforeach; ?>
              </select>
              <?php if(isset($form_errors['action'])): ?>
                <span class="error"><?php echo $form_errors['action'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Store name</label>
              <select class="form-control" name="locationCode" id="locationCode">
                <?php 
                  foreach($client_locations as $location_key=>$value):
                    $location_key_a = explode('`', $location_key);
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
              <?php if(isset($form_errors['locationCode'])): ?>
                <span class="error"><?php echo $form_errors['locationCode'] ?></span>
              <?php endif; ?>
            </div>            
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Bill no. (if applicable)</label>
              <input 
                type="text" class="form-control" name="refNo" id="refNo" 
                value="<?php echo $ref_no ?>"
              >
              <?php if(isset($form_errors['refNo'])): ?>
                <span class="error"><?php echo $form_errors['refNo'] ?></span>
              <?php endif; ?>
            </div>                 
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Bill date (dd-mm-yyyy)</label>
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
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Credit Note No.</label>
              <input 
                type="text" class="form-control" name="cnNo" id="cnNo" 
                value="<?php echo $cn_no ?>" <?php echo $cn_no_class ?>
              >              
              <?php if(isset($form_errors['action'])): ?>
                <span class="error"><?php echo $form_errors['action'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Amount (in Rs.)</label>
              <input
                type="text" class="form-control" name="amount" id="amount" 
                value="<?php echo $amount ?>"
              >
              <?php if(isset($form_errors['amount'])): ?>
                <span class="error"><?php echo $form_errors['amount'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-8 col-lg-8 m-bot15">
              <label class="control-label">Narration</label>
              <input 
                type="text" 
                class="form-control" 
                name="narration" 
                id="narration" 
                value="<?php echo $narration ?>" 
                maxlength="250"
              >
              <?php if(isset($form_errors['narration'])): ?>
                <span class="error"><?php echo $form_errors['narration'] ?></span>
              <?php endif; ?>
            </div>
          </div>
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