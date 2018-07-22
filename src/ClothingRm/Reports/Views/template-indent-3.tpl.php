<?php
  use Atawa\Utilities;
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
              <div class="col-sm-12 col-md-1 col-lg-1" style="font-size:16px;font-weight:bold;text-align:right;padding-top:5px;">Filters</div>
              <?php if(isset($show_fromto_dates) && $show_fromto_dates): ?>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" size="16" type="text" readonly name="fromDate" id="fromDate" value="<?php echo $current_date ?>" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" size="16" type="text" readonly name="toDate" id="toDate" value="<?php echo $current_date ?>" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                </div>
              <?php endif; ?>
              <div class="col-sm-12 col-md-2 col-lg-2">
                <div class="select-wrap">
                  <select class="form-control" name="campaignCode" id="campaignCode">
                    <?php 
                      foreach($campaigns as $key=>$value):
                    ?>
                      <option value="<?php echo $key ?>"><?php echo $value ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-sm-12 col-md-2 col-lg-2">
                <div class="select-wrap">
                  <select class="form-control" name="agentCode" id="agentCode">
                    <?php 
                      foreach($agents as $key=>$value):
                    ?>
                      <option value="<?php echo $key ?>"><?php echo $value ?></option>
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
  </div>
</div>