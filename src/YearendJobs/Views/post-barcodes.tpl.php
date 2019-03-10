<?php
  use Atawa\Utilities;
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <h2 class="hdg-reports text-left"><i class="fa fa-info-circle" aria-hidden="true"></i>&nbsp;This option will transfer all the available Barcodes to a new financial year.</h2>
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <form class="form-validate form-horizontal" method="POST">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">From Financial Year</label>
              <select class="form-control" name="fromFinyCode" id="fromFinyCode">
                <?php 
                  foreach($from_finys as $key => $value): 
                    if($def_finy_code !== $key && $key !== '') {
                      $disabled = 'disabled="disabled"';
                      $selected = '';
                    } else {
                      $disabled = '';
                      $selected = 'selected = "selected"';
                    }                      
                ?>
                  <option value="<?php echo $key ?>" <?php echo $disabled ?> <?php echo $selected ?>><?php echo $value ?></option>
                <?php endforeach; ?>              
              </select>
              <?php if(isset($form_errors['fromFinyCode'])): ?>
                <span class="error"><?php echo $form_errors['fromFinyCode'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">To Financial Year</label>
              <select class="form-control" name="toFinyCode" id="toFinyCode">
                <?php 
                  foreach($to_finys as $key => $value): 
                    if($def_finy_code === $key) {
                      $disabled = 'disabled="disabled"';
                      $disabled_text = ' [ Not Selectable ]';
                    } else {
                      $disabled = $disabled_text = '';
                    }
                ?>
                  <option value="<?php echo $key ?>" <?php echo $disabled ?>><?php echo $value.$disabled_text ?></option>
                <?php endforeach; ?>
              </select>
              <?php if(isset($form_errors['toFinyCode'])): ?>
                <span class="error"><?php echo $form_errors['toFinyCode'] ?></span>
              <?php endif; ?>
            </div>            
          </div>
          <div class="text-center">
            <button class="btn btn-danger" id="Save"><i class="fa fa-lightbulb-o" aria-hidden="true"></i> I know what i am doing. Proceed >></button>
          </div>
        </form>
      </div>
      <div style="font-weight:bold;text-align:center;padding:5px;font-size:16px;margin-top:50px;color:red;">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Warning: Please make sure that you are choosing correct financial years. You may get surprising results if you choose in correct financial years.
      </div>      
    </section>
  </div>
</div>