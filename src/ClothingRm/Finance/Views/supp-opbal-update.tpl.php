<?php
  use Atawa\Utilities;

  if(isset($submitted_data['suppName'])) {
    $supplier_name = $submitted_data['suppName'];
  } elseif(isset($submitted_data['supplierName'])) {
    $supplier_name = $submitted_data['supplierName'];
  } else {
    $supplier_name = '';
  }
  if(isset($submitted_data['amount'])) {
    $amount = $submitted_data['amount'];
  } else {
    $amount = '';
  }
  if(isset($submitted_data['action'])) {
    $mode = $submitted_data['action'];
  } else {
    $mode = '';
  }
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
  if(isset($submitted_data['creditDays']) && $submitted_data['creditDays'] !=='' ) {
    $credit_days = $submitted_data['creditDays'];
  } else {
    $credit_days = '';
  }
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Add Supplier Opening Balance</h2>
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php endif; ?>        
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/fin/supp-opbal/list" class="btn btn-default">
              <i class="fa fa-book"></i> List Supplier's Openings
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Supplier name</label>
              <input
                type="text"
                class="form-control suppnameAc" 
                name="suppName"
                id="suppName"
                value="<?php echo $supplier_name ?>"
              >              
              <?php if(isset($form_errors['suppName'])): ?>
                <span class="error"><?php echo $form_errors['suppName'] ?></span>
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