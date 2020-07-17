<?php
  use Atawa\Utilities;

  // dump($submitted_data);

  if(isset($submitted_data['mobileNo'])) {
    $mobile_no = $submitted_data['mobileNo'];
  } else {
    $mobile_no = '';
  }
  if(isset($submitted_data['status'])) {
    $user_status = $submitted_data['status'];
  } else {
    $user_status = 1;
  }
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Create User</h2>
      <div class="panel-body">

        <?php echo Utilities::print_flash_message() ?>

        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/users/app" class="btn btn-default">
              <i class="fa fa-mobile"></i> App Users List
            </a>&nbsp;
            <a href="/customers/list" class="btn btn-default">
              <i class="fa fa-users"></i> Customers List
            </a>
          </div>
        </div>
        
        <form class="form-validate form-horizontal" method="POST" autocomplete="Off">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Mobile no.</label>
              <input 
                type="text" class="form-control" name="mobileNo" id="mobileNo" 
                value="<?php echo $mobile_no ?>"
                maxlength="10"
              >              
              <?php if(isset($form_errors['mobileNo'])): ?>
                <span class="error"><?php echo $form_errors['mobileNo'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Status</label>
              <select class="form-control" name="status" id="status">
                <?php 
                  foreach($status_a as $key=>$value): 
                    if((int)$user_status === $key) {
                      $selected = 'selected="selected"';
                    } else {
                      $selected = '';
                    }                      
                ?>
                  <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                <?php endforeach; ?>              
              </select>
              <?php if(isset($form_errors['status'])): ?>
                <span class="error"><?php echo $form_errors['status'] ?></span>
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