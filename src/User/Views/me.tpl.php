<?php
  use Atawa\Utilities;

  // dump($submitted_data);

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
?>
<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12"> 
    
    <!-- Panel starts -->
    <section class="panel">
      <h2 class="hdg-reports text-center">Update My Account</h2>
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
        
        <!-- Form starts -->
        <form class="form-validate form-horizontal" method="POST">

          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Name</label>
              <input 
                type="text" class="form-control" name="userName" id="userName" 
                value="<?php echo $user_name ?>"
              >              
              <?php if(isset($form_errors['userName'])): ?>
                <span class="error"><?php echo $form_errors['userName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Password</label>
              <input
                type="password" class="form-control" name="password" id="password"
              >              
              <?php if(isset($form_errors['password'])): ?>
                <span class="error"><?php echo $form_errors['password'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Email / Login ID (not editable)</label>
              <input 
                type="text" class="form-control" name="emailID" id="emailID" 
                value="<?php echo $email_id ?>"
                disabled
              >              
              <?php if(isset($form_errors['emailID'])): ?>
                <span class="error"><?php echo $form_errors['emailID'] ?></span>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Mobile No.</label>
              <input 
                type="text" class="form-control" name="userPhone" id="userPhone" 
                value="<?php echo $user_phone ?>"
              >
              <?php if(isset($form_errors['userPhone'])): ?>
                <span class="error"><?php echo $form_errors['userPhone'] ?></span>
              <?php endif; ?>
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