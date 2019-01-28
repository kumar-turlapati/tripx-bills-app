<?php
  if(isset($submitted_data['mfgName'])) {
    $mfg_name = $submitted_data['mfgName'];
  } else {
    $mfg_name = '';
  }
  if(isset($submitted_data['locationID'])) {
    $location_id = $submitted_data['locationID'];
  } else {
    $location_id = 0;
  }  
  if(isset($submitted_data['status'])) {
    $status = $submitted_data['status'];
  } else {
    $status = '';
  }
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Update Brand / Manufacturer</h2>
      <div class="panel-body">
        <?php echo $flash_obj->print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/mfgs/list" class="btn btn-default">
              <i class="fa fa-book"></i> Brands/Mfgs. List 
            </a>&nbsp;
            <a href="/mfg/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Brand/Mfg.
            </a>&nbsp;            
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Brand / Manufacturer name</label>
              <input
                type="text" 
                class="form-control" 
                name="mfgName" 
                id="mfgName"
                value="<?php echo $mfg_name ?>"
              >
              <?php if(isset($form_errors['mfgName'])): ?>
                <span class="error"><?php echo $form_errors['mfgName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Store name</label>
              <div class="select-wrap">
                <select class="form-control" name="locationCode" id="locationCode">
                  <?php 
                    foreach($client_locations as $key => $value):
                      $selected = $location_code = '';
                      if($key !== '') {
                        $location_key_a = explode('`', $key);
                        if((int)$location_id === (int)$location_key_a[1]) {
                          $selected = 'selected="selected"';
                          $location_code = $location_key_a[0];
                        }
                      }
                  ?>
                    <option value="<?php echo $location_code ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if(isset($form_errors['locationCode'])): ?>
                <span class="error"><?php echo $form_errors['locationCode'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Status</label>
              <div class="select-wrap">
                <select class="form-control" name="status" id="status">
                  <?php 
                    foreach($status_options as $key=>$value):
                      if((int)$status === (int)$key) {
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