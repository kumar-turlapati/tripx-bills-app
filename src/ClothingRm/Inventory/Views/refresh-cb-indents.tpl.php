<?php
  use Atawa\Utilities;
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panel">
      <div class="panel-body" align="center">
        <div align="left"><?php echo Utilities::print_flash_message() ?></div>
        <?php if(is_null($refreshed)) : ?>
          <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="refreshCbForm">
            <input type="hidden" name="refreshHidden" value="1" />
            <?php /*<h3>Are you sure. You want to refresh the closing balances?</h3> */?>
            <div class="col-sm-12 col-md-6 col-lg-6 m-bot15">
              <label class="control-label labelStyle">Choose a store / location to Refresh the stock</label>
              <div class="select-wrap">
                <select class="form-control" name="locationCode" id="locationCode">
                  <?php 
                    foreach($client_locations as $location_key => $value):
                      $location_key_a = explode('`', $location_key);
                      if($locationCode === $location_key_a[0]) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }
                  ?>
                    <option value="<?php echo $location_key_a[0] ?>" <?php echo $selected ?>>
                      <?php echo $value ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-sm-12 col-md-12 col-lg-12">
              <div class="text-center">
                <button class="btn btn-primary" name="Refresh" id="refreshCbWithIndentYes"><i class="fa fa-refresh"></i> Refresh</button>&nbsp;
                <button class="btn btn-danger cancelButton" id="refreshCbWithIndentCancel"><i class="fa fa-times"></i> Cancel</button>
              </div>
            </div>
          </form>
        <?php else : ?>
          <?php if($refreshed): ?>
            <h2>Stock refreshed successfully! <i class="fa fa-smile-o" aria-hidden="true"></i></h2>
          <?php else: ?>
            <h2>Unable to refresh Stock <i class="fa fa-frown-o" aria-hidden="true"></i></h2>
          <?php endif; ?>
          <div class="text-center">
            <button class="btn btn-primary" onclick="window.location.href='/dashboard'">
              <i class="fa fa-home"></i> Goto Dashboard
            </button>&nbsp;
            <button class="btn btn-warning" onclick="window.location.href='/inventory/refresh-cb-indents'">
              <i class="fa fa-refresh"></i> Refresh Again
            </button>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>
</div>