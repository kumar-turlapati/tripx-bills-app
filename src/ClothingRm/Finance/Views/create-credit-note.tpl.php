<?php

  use Atawa\Utilities;

  if(isset($submitted_data['locationCode']) && $submitted_data['locationCode'] !== '' ) {
    $location_code = $submitted_data['locationCode'];
  } else {
    $location_code = '';
  }
  if(isset($submitted_data['cnType']) && $submitted_data['cnType'] !== '' ) {
    $cn_type = $submitted_data['cnType'];
  } else {
    $cn_type = '';
  }
  if(isset($submitted_data['refNo']) && $submitted_data['refNo'] !== '' ) {
    $ref_no = $submitted_data['refNo'];
  } else {
    $ref_no = '';
  }
  if(isset($submitted_data['cnValue']) && $submitted_data['cnValue'] !== '' ) {
    $cn_value = $submitted_data['cnValue'];
  } else {
    $cn_value = '';
  }
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Create Credit Note</h2>
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/fin/credit-notes" class="btn btn-default">
              <i class="fa fa-inr"></i> Credit Notes List 
            </a> 
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off">
          <div class="form-group">
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
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Credit note type</label>
              <div class="select-wrap">
                <select class="form-control" name="cnType" id="cnType">
                  <?php 
                    foreach($cn_types as $key=>$value): 
                      if($cn_type === $key) {
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
              <?php if(isset($form_errors['cnType'])): ?>
                <span class="error"><?php echo $form_errors['cnType'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Loyalty Card (or) Mobile No.</label>
              <input 
                type="text" 
                class="form-control" 
                name="refNo" 
                id="refNo" 
                value="<?php echo $ref_no ?>"
              >
              <?php if(isset($form_errors['refNo'])): ?>
                <span class="error"><?php echo $form_errors['refNo'] ?></span>
              <?php endif; ?>   
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">Credit note value</label>
              <input 
                type="text" 
                class="form-control" 
                name="cnValue" 
                id="cnValue" 
                value="<?php echo $cn_value ?>"
              >
              <?php if(isset($form_errors['cnValue'])): ?>
                <span class="error"><?php echo $form_errors['cnValue'] ?></span>
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