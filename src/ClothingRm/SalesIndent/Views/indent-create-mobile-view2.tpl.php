<?php
  if(isset($_SESSION['indentItemsM'])) {
    $total_qty = $total_items  = $total_value = 0;
    foreach($_SESSION['indentItemsM'] as $indent_order_details) {
      $total_qty += $indent_order_details['orderQty'];
      $total_value += $total_qty * $indent_order_details['mrp'];
      $total_items++;
    }
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
        <div class="panel" style="margin-bottom:10px;">
          <div class="panel-body" style="padding:10px;">
            <div class="form-group">
              <div class="col-sm-12 col-md-12 col-lg-12" style="margin-bottom:8px;">
                <label class="control-label"><b>Customer name</b></label>
                <input
                  type="text"
                  size="10"
                  id="customerName"
                  name="customerName"
                  maxlength="10"
                  style="font-weight:bold;font-size:14px;border:1px dashed;padding:5px;"
                  class="form-control cnameAc noEnterKey"
                />
              </div>
              <div class="col-sm-12 col-md-12 col-lg-12" style="margin-bottom:8px;">
                <label class="control-label"><b>Remarks</b></label>
                <input
                  type="text"
                  size="10"
                  id="remarks"
                  name="remarks"
                  maxlength="300"
                  style="font-weight:bold;font-size:14px;border:1px dashed;padding:5px;"
                  class="form-control noEnterKey"
                />
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead>
                <tr class="font11">
                  <th class="text-center valign-middle" style="width:10%">Total Items</th>
                  <th class="text-center valign-middle" style="width:10%">Order Qty.</th>
                  <th class="text-center valign-middle" style="width:10%">Order Value</th>
                </tr>
              </thead>
              <tbody>
                <tr class="font12">
                  <td align="right" class="valign-middle"><?php echo $total_items ?></td>
                  <td align="right" class="valign-middle"><?php echo number_format($total_qty,2,'.','') ?></td>
                  <td align="right" class="valign-middle"><?php echo number_format($total_value,2,'.','') ?></td>                  
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="formButtons" <?php echo $btn_class ?>>
          <button class="btn btn-primary btn-md btn-block" type="submit" name="op" value="SaveIndent">Save this Indent</button>
          <button class="btn btn-danger btn-md btn-block" type="submit" name="op" value="CancelIndent">Cancel this Indent</button>
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