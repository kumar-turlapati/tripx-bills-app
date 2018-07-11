<?php
  use Atawa\Utilities;
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
                <div class="col-sm-12 col-md-1 col-lg-1">Filter by</div>
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