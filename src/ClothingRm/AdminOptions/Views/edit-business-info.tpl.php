<?php
  use Atawa\Utilities;

  if(isset($form_data['businessName'])) {
    $business_name = $form_data['businessName'];
  } else {
    $business_name = '';
  }
  if(isset($form_data['gstNo'])) {
    $gst_no = $form_data['gstNo'];
  } else {
    $gst_no = '';
  }
  if(isset($form_data['dlNo'])) {
    $dl_no = $form_data['dlNo'];
  } else {
    $dl_no = '';
  }
  if(isset($form_data['addr1'])) {
    $address1 = $form_data['addr1'];
  } elseif(isset($form_data['address1'])) {
    $address1 = $form_data['address1'];
  } else {
    $address1 = '';
  }
  if(isset($form_data['addr2'])) {
    $address2 = $form_data['addr2'];
  } elseif(isset($form_data['address2'])) {
    $address2 = $form_data['address2'];
  } else {
    $address2 = '';
  }
  if(isset($form_data['locState'])) {
    $state_id = $form_data['locState'];
  } else {
    $state_id = 0;
  }
  if(isset($form_data['pincode']) && $form_data['pincode']) {
    $pincode = $form_data['pincode'];
  } else {
    $pincode = '';
  }
  if(isset($form_data['phones'])) {
    $phones = $form_data['phones'];
  } else {
    $phones = '';
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
        
        <!-- Form starts -->
        <form class="form-validate form-horizontal" method="POST" enctype="multipart/form-data">

          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Business Name</label>
              <input type="text" class="form-control" name="businessName" id="businessName" value="<?php echo $business_name ?>" />              
              <?php if(isset($form_errors['businessName'])): ?>
                <span class="error"><?php echo $form_errors['businessName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">GST No.</label>
              <input type="text" class="form-control" name="gstNo" id="gstNo" value="<?php echo $gst_no ?>" maxlength="15" />              
              <?php if(isset($form_errors['gstNo'])): ?>
                <span class="error"><?php echo $form_errors['gstNo'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Trade Licence No.</label>
              <input type="text" class="form-control" name="dlNo" id="dlNo" value="<?php echo $dl_no ?>" />              
              <?php if(isset($form_errors['dlNo'])): ?>
                <span class="error"><?php echo $form_errors['dlNo'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Door No. / Building Name / Street Name</label>
              <input type="text" class="form-control" name="address1" id="address1" value="<?php echo $address1 ?>"
              >
              <?php if(isset($form_errors['address1'])): ?>
                <span class="error"><?php echo $form_errors['address1'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Colony / Locality / City</label>
              <input 
                type="text" class="form-control" name="address2" id="address2" 
                value="<?php echo $address2 ?>"
              >
              <?php if(isset($form_errors['address2'])): ?>
                <span class="error"><?php echo $form_errors['address2'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">State</label>
              <select class="form-control" name="locState" id="locState">
                <?php 
                  foreach($states as $key=>$value): 
                    if((int)$state_id === $key) {
                      $selected = 'selected="selected"';
                    } else {
                      $selected = '';
                    }
                ?>
                  <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                <?php endforeach; ?>              
              </select>
              <?php if(isset($form_errors['locState'])): ?>
                <span class="error"><?php echo $form_errors['locState'] ?></span>
              <?php endif; ?>
            </div>            
          </div>

          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Phone(s)</label>
              <input type="text" class="form-control" name="phones" id="phones" value="<?php echo $phones ?>" />              
              <?php if(isset($form_errors['phones'])): ?>
                <span class="error"><?php echo $form_errors['phones'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Pincode</label>
              <input type="text" class="form-control" name="pincode" id="pincode" maxlength="6" value="<?php echo $pincode ?>" />
              <?php if(isset($form_errors['pincode'])): ?>
                <span class="error"><?php echo $form_errors['pincode'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Business Logo</label>
              <input type="file" class="form-control" name="logoName" id="logoName" />
              <?php if(isset($form_errors['logoName'])): ?>
                <span class="error"><?php echo $form_errors['logoName'] ?></span>
              <?php endif; ?>
              <p class="blue">Only <b>.jpeg or .jpg</b> format is allowed with 240 x 81 dimensions. The files size should be less than 30kB.</p>
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