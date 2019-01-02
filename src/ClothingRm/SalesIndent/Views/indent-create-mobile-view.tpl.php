<?php
  if(isset($_SESSION['indentItemsM'])) {
    $total_qty = array_sum(array_column($_SESSION['indentItemsM'], 'orderQty'));
    $total_items = count($_SESSION['indentItemsM']);
  } else {
    $total_qty = $total_items = 0;
  }
  if($total_items > 0) {
    $btn_class = '';
  } else {
    $btn_class = ' style="display:none"';
  }
?>
<section>
  <section class="wrapper">
    <form class="login-form" method="POST" autocomplete="off" id="salesIndentMobileV">
      <div class="login-wrap" style="padding-top:10px;">
        <h2 class="text-center" style="margin-bottom:0px;">
          <b>Sales Indent Entry - Mobile Ver.</b>
        </h2>
        <?php echo $flash_obj->print_flash_message(); ?>
        <?php if($total_qty > 0 && $total_items > 0): ?>
          <div class="col-sm-12 col-md-12 col-lg-12" style="font-size:14px;float:none;text-align:center;color:#000;font-weight:bold;">
            Order Summary: Qty.: <?php echo number_format($total_qty, 2, '.', '') ?> | 
            Items : <?php echo number_format($total_items,0) ?>
          </div>
        <?php endif; ?>
        <div class="panel" style="margin-bottom:10px;">
          <div class="panel-body" style="padding:10px;">
            <div class="form-group">
              <div class="col-sm-12 col-md-12 col-lg-12" style="margin-bottom:8px;">
                <label class="control-label"><b>Item name</b></label>
                <input
                  type="text"
                  size="10"
                  id="itemName"
                  name="itemName"
                  maxlength="30"
                  style="font-weight:bold;font-size:14px;border:1px dotted;padding:5px;"
                  class="form-control inameAc noEnterKey"
                />
                <input type="hidden" name="locationCode" id="locationCode" value="327eb4e7a3b4916ea1e112636c8b567a0bf2b223f53e2fadbd931dcccb1d8ff9" />
              </div>
              <div class="col-sm-12 col-md-12 col-lg-12" align="right">
                <button class="btn btn-warning btn-sm" id="mobileIndentItem">Check Availability</button>
              </div>
              <div class="itemOtherInfo" style="display:none;">
                <div class="col-sm-12 col-md-12 col-lg-12">
                  <label class="control-label"><b>Lot no.</b></label>
                  <select id="lotNo" name="lotNo" class="form-control noEnterKey indentLotNo" style="padding:5px;font-size:14px;"></select>
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12" style="padding-top:5px;">
                  <label class="control-label"><b>Order Qty.</b></label>
                  <input
                    type="text"
                    size="10"
                    id="orderQty"
                    name="orderQty"
                    maxlength="10"
                    style="font-weight:bold;font-size:14px;border:1px dotted;padding:5px;"
                    class="form-control noEnterKey indentOrderQty"
                  />
                </div>        
              </div>
            </div>
          </div>
        </div>
        <div class="formButtons" <?php echo $btn_class ?>>
          <button class="btn btn-primary btn-md btn-block" type="submit" name="op" value="SaveandItems">Save & add more items</button>
          <button class="btn btn-danger btn-sm btn-block" type="submit" name="op" value="SaveandCustomer">Add customer name and Save Indent</button>
        </div>
        <div class="input-group forgot-pass" style="padding-bottom:10px;">
          <a href="/dashboard"><b><i class="fa fa-home"></i>&nbsp;My Dashboard</b></a>
        </div>
        <div class="input-group login-copyrights" style="padding-bottom:10px;">
          <p>Powered by&nbsp;
            <a href="http://tripexpert.co.in/" target="_blank">
              <img src="/assets/img/tripexpert-logo.png">
            </a>
          </p>
        </div>
      </div>
      <input type="hidden" name="mrp" id="mrp" value="" />
    </form>
  </section>
</section>