<?php
  use Atawa\Utilities;
?>
<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12"> 
    
    <!-- Panel starts -->
    <section class="panel">
      <h2 class="hdg-reports text-center">Change Financial Year</h2>
      <div class="panel-body">

        <?php echo Utilities::print_flash_message() ?>
        
        <!-- Form starts -->
        <form class="form-validate form-horizontal" method="POST">

          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Default year (current setting)</label>
              <select class="form-control" name="defYear" id="defYear">
                <?php 
                  foreach($fin_years as $key=>$value): 
                    if((int)$sel_fin_year === $key) {
                      $selected = 'selected="selected"';
                    } else {
                      $selected = '';
                    }                      
                ?>
                  <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                <?php endforeach; ?>              
              </select>
              <?php if(isset($form_errors['defYear'])): ?>
                <span class="error"><?php echo $form_errors['defYear'] ?></span>
              <?php endif; ?>
              <p class="hint">Note: This change is applicable only for this session.</p>
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