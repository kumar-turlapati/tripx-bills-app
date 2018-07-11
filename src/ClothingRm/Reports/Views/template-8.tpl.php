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
        <div class="">
          <h2 id="filters1">Report Filters <i class="fa fa-history"></i></h2>
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
                    <label class="control-label">Month - I</label>
                    <div class="select-wrap">
                      <select class="form-control" name="month1" id="month1">
                        <?php 
                          foreach($months as $key=>$value):
                            if($key==$def_month-1) {
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
                    <label class="control-label">Year - I</label>
                    <div class="select-wrap">
                      <select class="form-control" name="year1" id="year1">
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
                  <div class="col-sm-12 col-md-2 col-lg-2">
                    <label class="control-label">Month - II</label>
                    <div class="select-wrap">
                      <select class="form-control" name="month2" id="month2">
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
                    <label class="control-label">Year - II</label>
                    <div class="select-wrap">
                      <select class="form-control" name="year2" id="year2">
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
                  <div class="col-sm-12 col-md-2 col-lg-2">
                    <label class="control-label"><?php echo $dropDownlabel ?></label>
                    <div class="select-wrap m-bot15">
                      <select class="form-control <?php echo $movement_class ?>" name="optionType" id="optionType">
                        <?php 
                          foreach($filter_types as $key=>$value):                          
                        ?>
                          <option value="<?php echo $key ?>"><?php echo $value ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>                  
                </div>
                <div class="form-group">
                  <div class="col-sm-12 col-md-3 col-lg-3">
                    <input type="hidden" id="reportHook" name="reportHook" value="<?php echo $reportHook ?>" />
                    <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons-js.helper.php" ?>
                  </div>
                </div>
              </form>
          </div>
        </div>
      </div>
    </section>
    <!-- Panel ends -->
  </div>
</div>