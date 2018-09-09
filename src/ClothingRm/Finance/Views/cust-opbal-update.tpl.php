<?php
  use Atawa\Utilities;

  if(isset($submitted_data['custName'])) {
    $customer_name = $submitted_data['custName'];
  } elseif(isset($submitted_data['customerName'])) {
    $customer_name = $submitted_data['customerName'];    
  } else {
    $customer_name = '';
  }
  $amount = isset($submitted_data['amount']) && is_numeric($submitted_data['amount']) ? $submitted_data['amount'] : '';
  $mode = isset($submitted_data['action']) ? $submitted_data['action'] : '';
  if(isset($submitted_data['billNo'])) {
    $bill_no = $submitted_data['billNo'];
  } else {
    $bill_no = '';
  }
  if(isset($submitted_data['billDate']) && $submitted_data['billDate']!=='') {
    $bill_date = date("d-m-Y", strtotime($submitted_data['billDate']));
  } else {
    $bill_date = date("d-m-Y");
  }
  if(isset($submitted_data['creditDays']) && $submitted_data['creditDays'] !== '') {
    $credit_days = $submitted_data['creditDays'];
  } else {
    $credit_days = '';
  }
  // dump($submitted_data);
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Add Customer Opening Balance</h2>
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/fin/cust-opbal/list" class="btn btn-default">
              <i class="fa fa-book"></i> List Customer's Opening Balances
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Customer name</label>
              <input
                type="text"
                class="form-control cnameAc" 
                name="custName"
                id="custName"
                value="<?php echo $customer_name ?>"
              >              
              <?php if(isset($form_errors['custName'])): ?>
                <span class="error"><?php echo $form_errors['custName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Amount</label>
              <input 
                type="text" 
                class="form-control" 
                name="amount" 
                id="amount" 
                value="<?php echo $amount ?>"
              >
              <?php if(isset($form_errors['amount'])): ?>
                <span class="error"><?php echo $form_errors['amount'] ?></span>
              <?php endif; ?> 
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Action</label>
              <select class="form-control" name="action" id="action">
                <?php 
                  foreach($modes as $key=>$value): 
                    if((int)$mode === (int)$key) {
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
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Bill no.</label>
              <input
                type="text"
                class="form-control" 
                name="billNo"
                id="billNo"
                value="<?php echo $bill_no ?>"
              >              
              <?php if(isset($form_errors['billNo'])): ?>
                <span class="error"><?php echo $form_errors['billNo'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Bill date (dd-mm-yyyy)</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $bill_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $bill_date ?>" size="16" type="text" readonly name="billDate" id="billDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($errors['billDate'])): ?>
                    <span class="error"><?php echo $errors['billDate'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Credit days</label>
              <input
                type="text"
                class="form-control" 
                name="creditDays"
                id="creditDays"
                value="<?php echo $credit_days ?>"
              >              
              <?php if(isset($form_errors['creditDays'])): ?>
                <span class="error"><?php echo $form_errors['creditDays'] ?></span>
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