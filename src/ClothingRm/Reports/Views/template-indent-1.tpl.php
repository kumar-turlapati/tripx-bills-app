<?php
  use Atawa\Utilities;
  $format_options = ['pdf'=>'PDF Format', 'csv' => 'CSV Format'];
  $pkd_options = ['pkd' => 'Ordered * Packed Qty.', 'wpkd' => 'Ordered Qty.'];
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
              <div class="col-sm-12 col-md-2 col-lg-2">
                <input type="text" name="nearbyQty" id="nearbyQty" class="form-control" value="" placeholder="Threshold Qty." />
              </div>              
              <div class="col-sm-12 col-md-2 col-lg-2">
                <?php /*
                <div class="select-wrap">
                  <select class="form-control" name="format" id="format">
                    <?php 
                      foreach($pkd_options as $key=>$value):
                    ?>
                      <option value="<?php echo $key ?>"><?php echo $value ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                */ ?>
                <div class="select-wrap">
                  <select class="form-control" name="qtyFormat" id="qtyFormat">
                    <?php 
                      foreach($pkd_options as $key=>$value):
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