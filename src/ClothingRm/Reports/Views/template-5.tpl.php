<?php
  use Atawa\Utilities;

  if(isset($template_vars) && is_array($template_vars)) {
    extract($template_vars); 
  }

  $current_date = date("d-m-Y");  
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
                  <label class="control-label">Date</label>
                  <div class="form-group">
                    <div class="col-lg-12">
                      <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                        <input class="span2" size="16" type="text" readonly name="date" id="date" value="<?php echo $current_date ?>" />
                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <label class="control-label"><?php echo $dropDownlabel ?></label>
                  <div class="select-wrap">
                    <select class="form-control" name="optionType" id="optionType">
                      <?php 
                        foreach($filter_types as $key=>$value):                          
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