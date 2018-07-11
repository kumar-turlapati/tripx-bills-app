<?php
  use Atawa\Utilities;

  if(isset($template_vars) && is_array($template_vars)) {
    extract($template_vars); 
  }

  $current_date = date("d-m-Y");
  $from_date = date("d-m-Y", strtotime("$current_date -7 days"));
  $movement_class = ($reportHook==='/material-movement'?'mmovement':'');
?>

<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12">
    <!-- Panel starts -->
    <section class="panel">
      <div class="panel-body">

        <?php echo Utilities::print_flash_message() ?>

        <div class="filters-block">
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
                <div class="col-sm-12 col-md-2 col-lg-2 m-bot15">
                  <label class="control-label">From Date</label>
                  <div class="form-group">
                    <div class="col-lg-12">
                      <div class="input-append date" data-date="<?php echo $from_date ?>" data-date-format="dd-mm-yyyy">
                        <input class="span2" size="16" type="text" readonly name="fromDate" id="fromDate" value="<?php echo $from_date ?>" />
                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2 m-bot15">
                  <label class="control-label">To Date</label>
                  <div class="form-group">
                    <div class="col-lg-12">
                      <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                        <input class="span2" size="16" type="text" readonly name="toDate" id="toDate" value="<?php echo $current_date ?>" />
                        <span class="add-on"><i class="fa fa-calendar"></i></span>
                      </div>
                    </div>
                  </div>
                </div>                
                <div class="col-sm-12 col-md-2 col-lg-2 m-bot15">
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
                <div class="col-sm-12 col-md-2 col-lg-2 m-bot15">
                  <label class="control-label">No. of Units moved</label>
                  <div class="select-wrap m-bot15">
                    <select class="form-control" name="count" id="count">
                      <?php 
                        for($i=0;$i<=100;$i++):                       
                      ?>
                        <option value="<?php echo $i ?>">
                          <?php echo ($i===0?'Not applicable':$i); ?>
                        </option>
                      <?php endfor; ?>
                    </select>
                  </div>
                </div>                
                <input type="hidden" id="reportHook" name="reportHook" value="<?php echo $reportHook ?>" />
                <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons-js.helper.php" ?>
              </div>
            </form>        
          </div>
        </div>

      </div>
    </section>
    <!-- Panel ends -->
  </div>
</div>