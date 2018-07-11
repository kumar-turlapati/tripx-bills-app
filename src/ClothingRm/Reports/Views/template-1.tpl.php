<?php
  use Atawa\Utilities;
  $current_date = $fromDate = $toDate = date("d-m-Y");
?>

<div class="row">
  <div class="col-lg-12">
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
                  <div class="form-group">
                    <div class="col-lg-12">
                      <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                        <input class="span2" size="16" type="text" readonly name="fromDate" id="fromDate" value="<?php echo $fromDate ?>" />
                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="form-group">
                    <div class="col-lg-12">
                      <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                        <input class="span2" size="16" type="text" readonly name="toDate" id="toDate" value="<?php echo $toDate ?>" />
                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="locationCode" id="locationCode">
                      <?php 
                        foreach($location_codes as $key=>$value):
                      ?>
                        <option value="<?php echo $key ?>"><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <input type="hidden" id="reportHook" name="reportHook" value="<?php echo $reportHook ?>" />
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons-js.helper.php" ?>
                </div>
              </div>
            </form>
          </div>
      </div>
    </section>
  </div>
</div>