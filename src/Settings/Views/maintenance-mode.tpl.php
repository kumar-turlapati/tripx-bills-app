<?php
  //dump($form_data);
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <?php /*
      <h2 class="hdg-reports text-left"><i class="fa fa-info-circle" aria-hidden="true"></i>&nbsp;<?php echo $def_finy_name ?> is active in your Instance. You can change it from below setting.</h2> */ ?>
      <div class="panel-body">
        <?php echo $flash_obj->print_flash_message(); ?>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4">
              <label class="control-label"><b>Maintenance Mode</b></label>
              <div class="select-wrap">
                <select class="form-control" name="status" id="status">
                  <?php 
                    foreach($status_a as $key=>$value):
                      if((int)$current_status === (int)$key) {
                        $disabled = 'disabled="disabled"';
                        $value .= ' [Not Selectable]';
                      } else {
                        $disabled = '';
                      } 
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $disabled ?>><?php echo $value ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if(isset($errors['status'])): ?>
                <span class="error"><?php echo $errors['status'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-danger" id="Save"><i class="fa fa-lightbulb-o" aria-hidden="true"></i> I know what i am doing. Proceed >></button>
          </div>
        </form>
      </div>
      <div style="font-weight:bold;text-align:center;padding:5px;font-size:16px;margin-top:50px;color:red;text-decoration:underline;">
        Note: This option will Logout all the connected users instantly. Only admin users are allowed to login further.
      </div>
    </section>
  </div>
</div>