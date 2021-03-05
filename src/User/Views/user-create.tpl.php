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
  if(isset($submitted_data['emailComm'])) {
    $email_comm = $submitted_data['emailComm'];
  } else {
    $email_comm = '';
  }
  if(isset($submitted_data['whatsappOptIn'])) {
    $whatsapp_opt_in = $submitted_data['whatsappOptIn'];
  } else {
    $whatsapp_opt_in = 0;
  }
  if(isset($submitted_data['locationCode'])) {
    $location_code = $submitted_data['locationCode'];
  } else {
    $location_code = '';
  }  
  unset($user_types[127]);
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <div class="panel-body">

        <?php echo Utilities::print_flash_message() ?>

        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/users/list" class="btn btn-default">
              <i class="fa fa-users"></i> Platform Users List
            </a>            
          </div>
        </div>
        
        <form class="form-validate form-horizontal" method="POST" autocomplete="Off">
          <div class="form-group">
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">User name</label>
              <input
                type="text" 
                class="form-control" 
                name="userName" 
                id="userName" 
                maxlength="50"
                value="<?php echo $user_name ?>"
              >              
              <?php if(isset($form_errors['userName'])): ?>
                <span class="error"><?php echo $form_errors['userName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">Mobile no.</label>
              <input 
                type="text" 
                class="form-control" 
                name="userPhone" 
                id="userPhone"
                maxlength="10" 
                value="<?php echo $user_phone ?>"
              >
              <?php if(isset($form_errors['userPhone'])): ?>
                <span class="error"><?php echo $form_errors['userPhone'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
              <label class="control-label labelStyle">User email</label>
              <input 
                type="text" 
                class="form-control" 
                name="emailComm" 
                id="emailComm" 
                value="<?php echo $email_id ?>" 
                maxlength="50"
              >              
              <?php if(isset($form_errors['emailComm'])): ?>
                <span class="error"><?php echo $form_errors['emailComm'] ?></span>
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
            <div class="col-sm-12 col-md-3 col-lg-3 m-bot15">
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
          <div class="text-center">
            <button class="btn btn-success" id="Save">
              <i class="fa fa-save"></i> Create
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>