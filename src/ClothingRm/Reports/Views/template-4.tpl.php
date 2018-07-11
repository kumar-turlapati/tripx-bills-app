<?php
  use Atawa\Utilities;

  if(isset($template_vars) && is_array($template_vars)) {
    extract($template_vars); 
  }
  unset($patient_types[1]);
?>

<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12">
    <!-- Panel starts -->
    <section class="panel">
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
          <div id="filters-form">
            <form 
                class="form-validate form-horizontal" 
                method="POST" 
                id="reportsForm"
                action="<?php echo $formAction ?>"
                target="_blank"
             >
              <div class="form-group">
                <div class="col-sm-12 col-md-2 col-lg-2">
                <label class="control-label">&nbsp;</label>
                Report Filters</div> 
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label">Reference No.</label>
                  <input type="text" name="refNo" id="refNo" class="form-control" />
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label">Patient type</label>
                  <div class="select-wrap">
                    <select class="form-control" name="regType" id="regType">
                      <?php 
                        foreach($patient_types as $key=>$value):                          
                      ?>
                        <option value="<?php echo $key ?>"><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <label class="control-label">&nbsp;</label>
                <input type="hidden" id="reportHook" name="reportHook" value="<?php echo $reportHook ?>" />
                <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons-js.helper.php" ?>
                </div>
              </div>
            </form>        
          </div>

      </div>
    </section>
    <!-- Panel ends -->
  </div>
</div>