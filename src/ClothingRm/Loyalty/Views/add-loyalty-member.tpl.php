<?php
  use Atawa\Utilities;

  if(isset($submitted_data['memberName'])) {
    $member_name = $submitted_data['memberName'];
  } else {
    $member_name = '';
  }
  if(isset($submitted_data['mobileNo'])) {
    $mobile_no = $submitted_data['mobileNo'];
  } else {
    $mobile_no = '';
  }
  if(isset($submitted_data['cardNo'])) {
    $card_no = $submitted_data['cardNo'];
  } else {
    $card_no = '';
  }
  if(isset($submitted_data['refCardNo'])) {
    $ref_card_no = $submitted_data['refCardNo'];
  } else {
    $ref_card_no = '';
  }  
  if(isset($submitted_data['locationCode'])) {
    $location_code = $submitted_data['locationCode'];
  } else {
    $location_code = '';
  }
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Add Loyalty Member</h2>
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/loyalty-members/list" class="btn btn-default">
              <i class="fa fa-diamond"></i> Loyalty Members List 
            </a> 
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Member name</label>
              <input
                type="text" 
                class="form-control" 
                name="memberName" 
                id="memberName"
                value="<?php echo $member_name ?>"
                maxlength="50"
              >
              <?php if(isset($form_errors['memberName'])): ?>
                <span class="error"><?php echo $form_errors['memberName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Mobile no.</label>
              <input 
                type="text" 
                class="form-control" 
                name="mobileNo" 
                id="mobileNo" 
                value="<?php echo $mobile_no ?>"
                maxlength="10"
              >
              <?php if(isset($form_errors['mobileNo'])): ?>
                <span class="error"><?php echo $form_errors['mobileNo'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Card number</label>
              <input 
                type="text" 
                class="form-control" 
                name="cardNo" 
                id="cardNo" 
                value="<?php echo $card_no ?>"
                maxlength="10"
              >
              <?php if(isset($form_errors['cardNo'])): ?>
                <span class="error"><?php echo $form_errors['cardNo'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Referral card number</label>
              <input 
                type="text" 
                class="form-control" 
                name="refCardNo"
                id="refCardNo" 
                value="<?php echo $ref_card_no ?>"
                maxlength="10"
              >
              <?php if(isset($form_errors['refCardNo'])): ?>
                <span class="error"><?php echo $form_errors['refCardNo'] ?></span>
              <?php endif; ?>
            </div>            
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Store name</label>
              <select class="form-control" name="locationCode" id="locationCode">
                <?php 
                  foreach($client_locations as $location_key=>$value):
                    $location_key_a = explode('`', $location_key);
                    if($location_code === $location_key_a[0]) {
                      $selected = 'selected="selected"';
                    } else {
                      $selected = '';
                    }
                ?>
                 <option value="<?php echo $location_key_a[0] ?>" <?php echo $selected ?>>
                    <?php echo $value ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if(isset($form_errors['locationCode'])): ?>
                <span class="error"><?php echo $form_errors['locationCode'] ?></span>
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