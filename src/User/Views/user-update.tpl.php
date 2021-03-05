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
    $login_id = $submitted_data['email'];
  } elseif(isset($submitted_data['hEmail'])) {
    $login_id = $submitted_data['hEmail'];
  } else {
    $login_id = '';
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
  if(isset($submitted_data['locationCode'])) {
    $location_code = $submitted_data['locationCode'];
  } else {
    $location_code = '';
  }
  if(isset($submitted_data['whatsappOptIn'])) {
    $whatsapp_opt_in = $submitted_data['whatsappOptIn'];
  } else {
    $whatsapp_opt_in = 0;
  }
  if(isset($submitted_data['emailComm'])) {
    $email_comm = $submitted_data['emailComm'];
  } else {
    $email_comm = '';
  }  
  //remove app user from the list
  unset($user_types[127]);
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
          <div class="pull-right text-right">
            <a href="/users/list" class="btn btn-default">
              <i class="fa fa-users"></i> Users List
            </a>
          </div>
        </div>
        <!-- Right links ends --> 
        
        <!-- Form starts -->
        <form class="form-validate form-horizontal" method="POST" autocomplete="off">
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Platform login id (auto)</label>
              <p style="font-size:18px;font-weight:bold;color:#009bdf;"><?php echo $login_id ?></p>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">User name</label>
              <input 
                type="text" 
                class="form-control" 
                name="userName" 
                id="userName" 
                value="<?php echo $user_name ?>" 
                maxlength="50"
              >              
              <?php if(isset($form_errors['userName'])): ?>
                <span class="error"><?php echo $form_errors['userName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Password (max 15 chars.)</label>
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
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">User type</label>
              <div class="select-wrap">
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
              </div>
              <?php if(isset($form_errors['userType'])): ?>
                <span class="error"><?php echo $form_errors['userType'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle labelStyle">Mobile no.</label>
              <input 
                type="text" class="form-control" name="userPhone" id="userPhone" 
                value="<?php echo $user_phone ?>"
              >
              <?php if(isset($form_errors['userPhone'])): ?>
                <span class="error"><?php echo $form_errors['userPhone'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Status</label>
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
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Default store / location</label>
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
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Whatsapp Opt-in?</label>
              <div class="select-wrap">              
                <select class="form-control" name="whatsappOptIn" id="whatsappOptIn">
                  <?php 
                    foreach($whatsapp_opt_in_a as $key=>$value): 
                      if((int)$whatsapp_opt_in === (int)$key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }                      
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>              
                </select>
              </div>
              <?php if(isset($form_errors['whatsappOptIn'])): ?>
                <span class="error"><?php echo $form_errors['whatsappOptIn'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
              <label class="control-label labelStyle labelStyle">User email</label>
              <input 
                type="text" 
                class="form-control" 
                name="emailComm" 
                id="emailComm" 
                value="<?php echo $email_comm ?>"
                maxlength="50"
              >
              <?php if(isset($form_errors['emailComm'])): ?>
                <span class="error"><?php echo $form_errors['emailComm'] ?></span>
              <?php endif; ?>
            </div>
          </div>          
          <input type="hidden" id="uuid" name="uuid" value="<?php echo $uuid ?>" />
          <input type="hidden" id="hEmail" name="hEmail" value="<?php echo $login_id ?>" />          
          <div class="text-center">
            <button class="btn btn-success" id="Save">
              <i class="fa fa-edit"></i> Update
            </button>&nbsp;
            <button class="btn btn-danger cancelButton" id="cancelUpdateUser" onclick="javascript: window.location.href='/users/list';">
              <i class="fa fa-times"></i> Cancel
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