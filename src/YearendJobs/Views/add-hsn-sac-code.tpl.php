<?php
  use Atawa\Utilities;
  if(isset($submitted_data['hsnSacCodeDesc'])) {
    $hsn_sac_desc = $submitted_data['hsnSacCodeDesc'];
  } else {
    $hsn_sac_desc = '';
  }
  if(isset($submitted_data['hsnSacCodeDescShort'])) {
    $hsn_sac_desc_short = $submitted_data['hsnSacCodeDescShort'];
  } else {
    $hsn_sac_desc_short = '';
  }  
  if(isset($submitted_data['hsnSacCode'])) {
    $hsn_sac_code = $submitted_data['hsnSacCode'];
  } else {
    $hsn_sac_code = '';
  }
  if(isset($submitted_data['status'])) {
    $status = $submitted_data['status'];
  } else {
    $status = -1;
  }
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Add HSN/SAC Code</h2>
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/hsnsac/list" class="btn btn-default">
              <i class="fa fa-book"></i> HSN/SAC Codes List
            </a> 
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">HSN/SAC Code</label>
              <input 
                type="text"
                class="form-control"
                name="hsnSacCode"
                id="hsnSacCode" 
                value="<?php echo $hsn_sac_code ?>"
                style="border:2px dashed;background-color:yellow;"
              >
              <?php if(isset($form_errors['hsnSacCode'])): ?>
                <span class="error"><?php echo $form_errors['hsnSacCode'] ?></span>
              <?php endif; ?>
            </div>            
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">HSN/SAC Code Description</label>
              <textarea
                class="form-control" 
                name="hsnSacCodeDesc" 
                id="hsnSacCodeDesc"
              ><?php echo $hsn_sac_desc ?></textarea>
              <?php if(isset($form_errors['hsnSacCodeDesc'])): ?>
                <span class="error"><?php echo $form_errors['hsnSacCodeDesc'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label">HSN/SAC Code Short Description (100 characters)</label>
              <input
                type="text" 
                class="form-control" 
                name="hsnSacCodeDescShort" 
                id="hsnSacCodeDescShort"
                value="<?php echo $hsn_sac_desc_short ?>"
                maxlength="100"
              >
              <?php if(isset($form_errors['hsnSacCodeDesc'])): ?>
                <span class="error"><?php echo $form_errors['hsnSacCodeDescShort'] ?></span>
              <?php endif; ?>
            </div>            
          </div>
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Status</label>
              <div class="select-wrap">
                <select class="form-control" name="status" id="status">
                  <?php 
                    foreach($status_options as $key => $value):
                      if((int)$status === (int)$key) {
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