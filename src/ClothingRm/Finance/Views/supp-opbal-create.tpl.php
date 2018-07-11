<?php
  use Atawa\Utilities;
  $current_date = date("d-m-Y");

  if(isset($submitted_data['suppCode'])) {
    $supp_code = $submitted_data['suppCode'];
  } else {
    $supp_code = '';
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
  if(isset($submitted_data['openDate'])) {
    $open_date = date('d-m-Y', strtotime($submitted_data['openDate']));
  } else {
    $open_date = $current_date;
  }
  // dump($submitted_data);
?>
<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12"> 
    
    <!-- Panel starts -->
    <section class="panel">
      <h2 class="hdg-reports text-center">Add Supplier Opening Balance</h2>
      <div class="panel-body">

        <?php echo Utilities::print_flash_message() ?>
        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php endif; ?>        

        <!-- Right links starts -->
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/fin/supp-opbal/list" class="btn btn-default">
              <i class="fa fa-book"></i> List Supplier's Openings
            </a>
          </div>
        </div>
        <!-- Right links ends --> 
        
        <!-- Form starts -->
        <form class="form-validate form-horizontal" method="POST">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Supplier name</label>
              <select class="form-control" name="suppCode" id="suppCode">
                <?php 
                  foreach($suppliers as $key=>$value): 
                    if($supp_code === $key) {
                      $selected = 'selected="selected"';
                    } else {
                      $selected = '';
                    }                      
                ?>
                  <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                <?php endforeach; ?>
              </select>
              <?php if(isset($form_errors['suppCode'])): ?>
                <span class="error"><?php echo $form_errors['suppCode'] ?></span>
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
              <label class="control-label">Action</label>
              <select class="form-control" name="action" id="action">
                <?php 
                  foreach($modes as $key=>$value): 
                    if($mode === $key) {
                      $selected = 'selected="selected"';
                    } else {
                      $selected = '';
                    }                      
                ?>
                  <option value="<?php echo $key ?>"><?php echo $value ?></option>
                <?php endforeach; ?>
              </select>
              <?php if(isset($form_errors['action'])): ?>
                <span class="error"><?php echo $form_errors['action'] ?></span>
              <?php endif; ?> 
            </div>
          </div>      
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">            
              <label class="control-label">Date of opening (dd-mm-yyyy)</label>
              <div class="form-group">
                <div class="col-lg-12">
                  <div class="input-append date" data-date="<?php echo $open_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" value="<?php echo $open_date ?>" size="16" type="text" readonly name="openDate" id="openDate" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                  <?php if(isset($errors['openDate'])): ?>
                    <span class="error"><?php echo $errors['openDate'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-success" id="Save">
              <i class="fa fa-save"></i> Save
            </button>
          </div>          
        </form>
        <!-- Form ends -->
      </div>
    </section>
    <!-- Panel ends --> 
  </div>
</div>
<!-- Basic Forms ends -->