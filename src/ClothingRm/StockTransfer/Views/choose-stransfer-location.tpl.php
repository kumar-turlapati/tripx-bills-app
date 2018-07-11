<?php
  if(isset($form_data['fromLocation'])) {
    $from_loc_code = $form_data['fromLocation'];
  } else {
    $from_loc_code = '';
  }
  if(isset($form_data['toLocation'])) {
    $to_loc_code = $form_data['toLocation'];
  } else {
    $to_loc_code = '';
  }
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        
        <?php echo $flash->print_flash_message() ?>

        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/stock-transfer/register" class="btn btn-default">
              <i class="fa fa-book"></i> Stock Transfer Register
            </a>
          </div>
        </div>

        <form class="form-validate form-horizontal" method="POST" id="inwardEntryForm" autocomplete="off">
          <div class="panel">
            <div class="panel-body">
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">From Location</label>
                  <div class="select-wrap">
                    <select class="form-control" name="fromLocation" id="fromLocation">
                      <?php 
                        foreach($from_locations as $key=>$value): 
                          if($from_loc_code === $key) {
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
                  <?php if(isset($form_errors['fromLocation'])): ?>
                    <span class="error"><?php echo $form_errors['fromLocation'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">To Location</label>
                  <div class="select-wrap">
                    <select class="form-control" name="toLocation" id="toLocation">
                      <?php 
                        foreach($to_locations as $key=>$value): 
                          if($to_loc_code === $key) {
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
                  <?php if(isset($form_errors['toLocation'])): ?>
                    <span class="error"><?php echo $form_errors['toLocation'] ?></span>
                  <?php endif; ?>
                </div>                
              </div>
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-danger cancelButton" id="stoCancel">
              <i class="fa fa-times"></i> Cancel
            </button>
            <button class="btn btn-primary" id="stSave">
              Next >>>
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>