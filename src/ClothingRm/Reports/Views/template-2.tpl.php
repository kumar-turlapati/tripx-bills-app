<?php
  use Atawa\Utilities;

  if(isset($template_vars) && is_array($template_vars)) {
    extract($template_vars); 
  }

  $months = Utilities::get_calender_months();
  $years = Utilities::get_calender_years(1);
  $def_year = date("Y");
  $def_month = date("m");
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
                <div class="col-sm-12 col-md-2 col-lg-2">Report Filters</div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="month" id="month">
                      <?php 
                        foreach($months as $key=>$value):
                          if($key==$def_month) {
                            $selected = "selected";
                          } else {
                            $selected = '';
                          }
                      ?>
                        <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="year" id="year">
                      <?php 
                        foreach($years as $key=>$value):
                          if($key==$def_year) {
                            $selected = "selected";
                          } else {
                            $selected = '';
                          }                          
                      ?>
                        <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
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