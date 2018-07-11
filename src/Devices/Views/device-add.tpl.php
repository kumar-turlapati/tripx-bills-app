<?php
  use Atawa\Utilities;

  if(isset($submitted_data['uid'])) {
    $uid = $submitted_data['uid'];
  } else {
    $uid = '';
  }
  if(isset($submitted_data['deviceName'])) {
    $device_name = $submitted_data['deviceName'];
  } else {
    $device_name = '';
  }
  if(isset($submitted_data['status'])) {
    $device_status = $submitted_data['status'];
  } else {
    $device_status = 99;
  }
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-center">Add a Device</h2>
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/devices/list" class="btn btn-default">
              <i class="fa fa-cogs"></i> Devices List
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="Off">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">User name</label>
              <div class="select-wrap">              
                <select class="form-control" name="uid" id="uid">
                  <?php 
                    foreach($users as $key=>$value): 
                      if($uid === $key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }                      
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                  <?php endforeach; ?>              
                </select>
              </div>
              <?php if(isset($form_errors['uid'])): ?>
                <span class="error"><?php echo $form_errors['uid'] ?></span>
              <?php endif; ?>
            </div>            
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Device name</label>
              <input
                type="text" 
                class="form-control"
                name="deviceName"
                id="deviceName"
                maxlength="32"
                value="<?php echo $device_name ?>"
              >              
              <?php if(isset($form_errors['deviceName'])): ?>
                <span class="error"><?php echo $form_errors['deviceName'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Status</label>
              <div class="select-wrap">              
                <select class="form-control" name="status" id="status">
                  <?php 
                    foreach($status_a as $key=>$value): 
                      if((int)$device_status === $key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }                      
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
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