<?php
  use Atawa\Utilities;

  if(isset($submitted_data['userType'])) {
    $user_type = $submitted_data['userType'];
  } else {
    $user_type = '';
  }
  if(isset($submitted_data['userName'])) {
    $user_name = $submitted_data['userName'];
  } else {
    $user_name = '';
  }  
  if(isset($submitted_data['email'])) {
    $email_id = $submitted_data['email'];
  } else {
    $email_id = '';
  }
  if(isset($submitted_data['userPhone'])) {
    $user_phone = $submitted_data['userPhone'];
  } else {
    $user_phone = '';
  }
  if(isset($submitted_data['status'])) {
    $user_status = $submitted_data['status'];
  } else {
    $user_status = 1;
  }

  $location_code = '';
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Create User</h2>
      <div class="panel-body">

        <?php echo Utilities::print_flash_message() ?>

        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/users/list" class="btn btn-default">
              <i class="fa fa-users"></i> Users List
            </a>
          </div>
        </div>
        
        <form class="form-validate form-horizontal" method="POST" autocomplete="Off">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">User name</label>
              <input 
                type="text" class="form-control" name="userName" id="userName" 
                value="<?php echo $user_name ?>"
              >              
              <?php if(isset($form_errors['userName'])): ?>
                <span class="error"><?php echo $form_errors['userName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Email / Login ID</label>
              <input 
                type="text" class="form-control" name="emailID" id="emailID" 
                value="<?php echo $email_id ?>"
              >              
              <?php if(isset($form_errors['emailID'])): ?>
                <span class="error"><?php echo $form_errors['emailID'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">User type</label>
              <select class="form-control" name="userType" id="userType">
                <?php 
                  foreach($user_types as $key=>$value): 
                    if((int)$user_type === $key) {
                      $selected = 'selected="selected"';
                    } else {
                      $selected = '';
                    }                      
                ?>
                  <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                <?php endforeach; ?>              
              </select>
              <?php if(isset($form_errors['userType'])): ?>
                <span class="error"><?php echo $form_errors['userType'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Mobile No.</label>
              <input 
                type="text" class="form-control" name="userPhone" id="userPhone" 
                value="<?php echo $user_phone ?>"
              >
              <?php if(isset($form_errors['userPhone'])): ?>
                <span class="error"><?php echo $form_errors['userPhone'] ?></span>
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
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Store name</label>
              <div class="select-wrap">
                <select class="form-control" name="locationCode" id="locationCode">
                  <?php 
                    foreach($client_locations as $key=>$value): 
                      if($location_code === $key) {
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
              <?php if(isset($form_errors['locationCode'])): ?>
                <span class="error"><?php echo $form_errors['locationCode'] ?></span>
              <?php endif; ?>
            </div>            
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Password (max 15 chars.)</label>
              <input 
                type="password" 
                class="form-control" 
                name="userPass" 
                id="userPass" 
                maxlength="15" 
              >
              <?php if(isset($form_errors['userPass'])): ?>
                <span class="error"><?php echo $form_errors['userPass'] ?></span>
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