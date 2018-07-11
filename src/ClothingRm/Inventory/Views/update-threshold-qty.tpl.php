<?php
  use Atawa\Utilities;

  if(isset($template_vars) && is_array($template_vars)) {
    extract($template_vars); 
  }
  
  if(isset($submitted_data['itemName']) && $submitted_data['itemName'] !== '') {
    $item_name = $submitted_data['itemName'];
  } else {
    $item_name = '';
  }
  if(isset($submitted_data['thrQty']) && $submitted_data['thrQty'] !== '') {
    $thr_qty = $submitted_data['thrQty'];
  } else {
    $thr_qty = '';
  }
  if(isset($submitted_data['supplierName']) && $submitted_data['supplierName'] !== '') {
    $supplier_name = $submitted_data['supplierName'];
  } else {
    $supplier_name = '';
  }  
?>

<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12"> 
    
    <!-- Panel starts -->
    <section class="panel">
      <div class="panel-body">

        <?php echo Utilities::print_flash_message() ?>

        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php elseif($page_success !== ''): ?>
          <div class="alert alert-success" role="alert">
            <strong>Success!</strong> <?php echo $page_success ?> 
          </div>
        <?php endif; ?>        
        
        <!-- Right links starts -->
        <div class="global-links actionButtons clearfix">
          <!-- Button style -->
          <div class="pull-right text-right">
            <a href="/inventory/item-threshold-list" class="btn btn-default">
              <i class="fa fa-book"></i> Threshold Quantities List
            </a> 
          </div>
          <!-- Button style -->
        </div>
        <!-- Right links ends --> 
        
        <!-- Form starts -->
        <form class="form-validate form-horizontal" method="POST">
          <h2>Item Details</h2>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Item name</label>
              <input type="text" class="form-control inameAc" name="itemName" id="itemName" value="<?php echo $item_name ?>">
              <?php if(isset($errors['itemName'])): ?>
                <span class="error"><?php echo $errors['itemName'] ?></span>
              <?php endif; ?>           
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Threshold qty.</label>
              <input type="text" class="form-control" name="thrQty" id="thrQty" value="<?php echo $thr_qty ?>">
              <?php if(isset($errors['thrQty'])): ?>
                <span class="error"><?php echo $errors['thrQty'] ?></span>
              <?php endif; ?>              
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Supplier name</label>
                <input type="text" class="form-control" name="supplierName" id="supplierName" value="<?php echo $supplier_name ?>">
                <?php if(isset($errors['supplierName'])): ?>
                  <span class="error"><?php echo $errors['supplierName'] ?></span>
                <?php endif; ?>
            </div>
          </div>

          <div class="text-center">
            <button class="btn btn-primary" id="Save">
              <i class="fa fa-save"></i> Save
            </button>
          </div>
          <input type="hidden" id="thrCode" name="thrCode" value="<?php echo $thr_code ?>" />
        </form>  
      </div>
    </section>
    <!-- Panel ends --> 
  </div>
</div>
<!-- Basic Forms ends -->