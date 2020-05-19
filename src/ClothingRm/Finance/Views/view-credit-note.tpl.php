<?php
  // dump($errors);
  // dump($offers_raw);
  // exit;
  // dump($print_format, $bill_to_print);

  // dump($cn_details);
  // exit;

  use Atawa\Constants;

  $cn_date = date("d-m-Y", strtotime($cn_details['cnDate']));
  $customer_name = $cn_details['customerName'];
  $customer_code = $cn_details['customerCode'];
  $bill_no = $cn_details['billNo'];
  $store_name = $location_ids[$cn_details['locationID']];
  $cn_value = $cn_details['cnValue'];
  $cn_reason = $cn_details['adjReason'];
  $cn_items = $cn_details['cn_items'];
  $tax_calc_option = $cn_details['taxCalcOption'] === 'i' ? 'Including' : 'Excluding';

  if(count($cn_items) > 0) {
    $cn_type = 'Sales Return with Items';
  } else {
    $cn_type = $cn_details['cnType'];
  }
?>

<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $flash_obj->print_flash_message(); ?>
        <div class="global-links actionButtons clearfix"> 
          <div class="pull-right text-right">
            <a href="/fin/credit-note/create" class="btn btn-default">
              <i class="fa fa-share"></i> Create credit note
            </a>            
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font12" style="margin-bottom: 0px;">
            <thead>
              <tr>
                <th width="5%"  class="text-center">Sno.</th>                  
                <th width="28%" class="text-center">Item name</th>
                <th width="11%"  class="text-center">Return<br />qty.</th>
                <th width="8%" class="text-center">Rate<br />( in Rs. )</th>
                <th width="10%" class="text-center">GST<br />( in % )</th>
              </tr>
            </thead>
            <tbody>
              <?php if(count($cn_items) > 0): ?>
                <?php
                  $tot_return_qty = 0;
                  $i = 0;
                  foreach($cn_items as $cn_item_details):
                    $i++;
                    $item_name = $cn_item_details['itemName'];
                    $return_qty = $cn_item_details['returnQty'];
                    $return_rate = $cn_item_details['mrp'];
                    $tax_percent = $cn_item_details['taxPercent'];

                    $tot_return_qty += $return_qty;                    
                ?>
                  <tr class="font12">
                    <td align="right" style="vertical-align:middle;"><?php echo $i ?></td>
                    <td style="vertical-align:middle;"><?php echo $item_name ?></td>
                    <td style="vertical-align:middle;text-align: right;"><?php echo $return_qty ?></td>
                    <td style="vertical-align:middle;text-align:right;"><?php echo number_format($return_rate,2,'.','') ?></td>
                    <td style="vertical-align:middle;text-align:right;"><?php echo number_format($tax_percent,2,'.','') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font12" style="margin-bottom:0px;">
            <tr>
              <th width="25%" class="text-center valign-middle">Credit note date</th>
              <th width="25%"  class="text-center valign-middle">Credit note type</th>             
              <th width="25%" class="text-center valign-middle">Customer name</th>
              <th width="25%"  class="text-center valign-middle">Bill no.</th>
            </tr>
            <tr>
              <td width="25%" style="vertical-align:middle;text-align:center;"><?php echo $cn_date ?></td>
              <td width="25%" style="vertical-align:middle;text-align:center;"><?php echo $cn_type ?></td>
              <td width="25%" style="vertical-align:middle;text-align:center;"><?php echo $customer_name ?></td>
              <td width="25%" style="vertical-align:middle;text-align:center;"><?php echo $bill_no ?></td>
            </tr>
            <tr>
              <th width="25%"  class="text-center valign-middle">Store name</th>
              <th width="25%"  class="text-center valign-middle">Credit note value</th>
              <th width="25%"  class="text-center valign-middle">Credit note reason</th>
              <th width="25%"  class="text-center valign-middle">Tax calculation</th>
            </tr>
            <tr>
              <td width="25%"  class="text-center valign-middle"><?php echo $store_name ?></td>
              <td width="25%"  class="text-center valign-middle" style="font-size: 16px; color: red;"><?php echo $cn_value ?></td>
              <td width="25%"  class="text-center valign-middle"><?php echo $cn_reason ?></td>           
              <td width="25%"  class="text-center valign-middle"><?php echo $tax_calc_option ?></td>           
            </tr>
          </table>
        </div><br /><br />
        <div class="text-center">
          <button class="btn btn-primary" onclick="window.location.href='/fin/credit-notes'">
            <i class="fa fa-backward"></i> Back
          </button>            
        </div>          
      </div>
    </section>
  </div>
</div>