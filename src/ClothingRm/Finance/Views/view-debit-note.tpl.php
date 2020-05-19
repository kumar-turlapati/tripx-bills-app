<?php
  // dump($errors);
  // dump($offers_raw);
  // exit;
  // dump($print_format, $bill_to_print);

  // dump($dn_details);
  // exit;

  use Atawa\Constants;

  if($dn_details['dnType'] === 'w' || $dn_details['dnType'] === 'wo') {
    $dn_type = $dn_details['dnType'] === 'w' ? 'With items' : 'W/o Items';
  } elseif($dn_details['dnType'] === 'ma') {
    $dn_type = 'Pur.Return';
  } else {
    $dn_type = 'Auto';
  }
  $dn_date = date("d-m-Y", strtotime($dn_details['dnDate']));
  $supplier_name = $dn_details['supplierName'];
  $supplier_code = $dn_details['supplierCode'];
  $bill_no = $dn_details['billNo'];
  $store_name = $location_ids[$dn_details['locationID']];
  $dn_value = $dn_details['dnValue'];
  $dn_reason = $dn_details['adjReason'];
  $dn_items = $dn_details['dnItems'];
  $tax_calc_option = $dn_details['taxCalcOption'] === 'i' ? 'Including' : 'Excluding';
?>

<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $flash_obj->print_flash_message(); ?>
        <div class="global-links actionButtons clearfix"> 
          <div class="pull-right text-right">
            <a href="/fin/debit-note/create" class="btn btn-default">
              <i class="fa fa-share"></i> Create debit note
            </a>            
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font12" style="margin-bottom: 0px;">
            <thead>
              <tr>
                <th width="5%"  class="text-center">Sno.</th>                  
                <th width="28%" class="text-center">Item name</th>
                <th width="10%" class="text-center">Lot no.</th>
                <th width="11%"  class="text-center">Return<br />qty.</th>
                <th width="8%" class="text-center">Rate<br />( in Rs. )</th>
                <th width="10%" class="text-center">GST<br />( in % )</th>
              </tr>
            </thead>
            <tbody>
              <?php if(count($dn_items) > 0): ?>
                <?php
                  $tot_return_qty = 0;
                  $i = 0;
                  foreach($dn_items as $dn_item_details):
                    $i++;
                    $item_name = $dn_item_details['itemName'];
                    $lot_no = $dn_item_details['lotNo'];
                    $return_qty = $dn_item_details['returnQty'];
                    $return_rate = $dn_item_details['actualRate'];
                    $tax_percent = $dn_item_details['taxPercent'];

                    $tot_return_qty += $return_qty;                    
                ?>
                  <tr class="font12">
                    <td align="right" style="vertical-align:middle;"><?php echo $i ?></td>
                    <td style="vertical-align:middle;"><?php echo $item_name ?></td>
                    <td style="vertical-align:middle;"><?php echo $lot_no ?></td>              
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
              <th width="25%" class="text-center valign-middle">Debit note date</th>
              <th width="25%"  class="text-center valign-middle">Debit note type</th>             
              <th width="25%" class="text-center valign-middle">Supplier name</th>
              <th width="25%"  class="text-center valign-middle">Bill no.</th>             
            </tr>
            <tr>
              <td width="25%" style="vertical-align:middle;text-align:center;"><?php echo $dn_date ?></td>
              <td width="25%" style="vertical-align:middle;text-align:center;"><?php echo $dn_type ?></td>
              <td width="25%" style="vertical-align:middle;text-align:center;"><?php echo $supplier_name ?></td>
              <td width="25%" style="vertical-align:middle;text-align:center;"><?php echo $bill_no ?></td>
            </tr>
            <tr>
              <th width="25%"  class="text-center valign-middle">Store name</th>
              <th width="25%"  class="text-center valign-middle">Debit note value</th>
              <th width="25%"  class="text-center valign-middle">Debit note reason</th>
              <th width="25%"  class="text-center valign-middle">Tax calculation</th>
            </tr>
            <tr>
              <td width="25%"  class="text-center valign-middle"><?php echo $store_name ?></td>
              <td width="25%"  class="text-center valign-middle" style="font-size: 16px; color: red;"><?php echo $dn_value ?></td>           
              <td width="25%"  class="text-center valign-middle"><?php echo $dn_reason ?></td>           
              <td width="25%"  class="text-center valign-middle"><?php echo $tax_calc_option ?></td>           
            </tr>
          </table>
        </div><br /><br />
        <div class="text-center">
          <button class="btn btn-primary" onclick="window.location.href='/fin/debit-notes'">
            <i class="fa fa-save"></i> Back
          </button>            
        </div>          
      </div>
    </section>
  </div>
</div>