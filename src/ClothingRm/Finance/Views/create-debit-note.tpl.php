<?php
  use Atawa\Utilities;

  $supplier_code = isset($form_data['supplierCode']) && $form_data['supplierCode'] !== '' ? $form_data['supplierCode'] : '';
  $bill_no = isset($form_data['billNo']) && $form_data['billNo'] !== '' ? $form_data['billNo'] : '';
  $amount = isset($form_data['amount']) && $form_data['amount'] !== '' ? $form_data['amount'] : '';
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Create Debit Note</h2>
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/fin/debit-notes" class="btn btn-default">
              <i class="fa fa-inr"></i> Debit Notes List 
            </a> 
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="debitVocEntryForm">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Supplier name</label>
              <div class="select-wrap">
                <select class="form-control" name="supplierCode" id="supplierCode" readonly>
                  <?php 
                    foreach($suppliers_a as $key => $value): 
                      if($supplier_code === $key) {
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
              <?php if(isset($form_errors['supplierCode'])): ?>
                <span class="error"><?php echo $form_errors['supplierCode'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Bill no.</label>
              <input 
                type="text"
                class="form-control" 
                name="billNo" 
                id="suppBillNo" 
                value ="<?php echo $bill_no ?>" 
                readonly
              />
              <?php if(isset($form_errors['billNo'])): ?>
                <span class="error"><?php echo $form_errors['billNo'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Debit amount</label>
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