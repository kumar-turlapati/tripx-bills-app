<?php
  if(isset($return_details['returnDate'])) {
    $return_date = date("d-m-Y", strtotime($return_details['returnDate']));
  } else {
    $return_date = 'Invalid';
  }
  if(isset($return_details['supplierName'])) {
    $supplier_name = $return_details['supplierName'];
  } else {
    $supplier_name = 'Invalid';
  }
  if( isset($return_details['mrnNo']) ) {
    $mrn_no = $return_details['mrnNo'];
  } else {
    $mrn_no = 'Invalid';
  }
  if(isset($return_details['poNo'])) {
    $po_no = $return_details['poNo'];
  } else {
    $po_no = '';
  }
  if(isset($return_details['purchaseDate'])) {
    $po_date = date("d-m-Y", strtotime($return_details['purchaseDate']));
  } else {
    $po_date = 'Invalid';
  }
  if(isset($return_details['grnNo'])) {
    $grn_no = $return_details['grnNo'];
  } else {
    $grn_no = '';
  }
  $taxable_amount = $return_details['taxableAmount'];
  $tax_amount = $return_details['taxAmount'];
  $round_off = $return_details['roundOff'];
  $netpay = $return_details['netpay'];
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $utilities->print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/purchase-return/register" class="btn btn-default">
              <i class="fa fa-book"></i> Purchase Return Register
            </a>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover item-detail-table font12" id="purchaseTable">
            <tbody>
              <tr>
                <td style="width:10%;text-align:center;font-weight:bold;font-size:17px;color:#225992;">Return Date</td>
                <td style="width:30%;text-align:center;font-weight:bold;font-size:17px;color:#225992;">Supplier Name</td>
                <td style="width:10%;text-align:center;font-weight:bold;font-size:17px;color:#225992;">Return Note No.</td>
                <td style="width:10%;text-align:center;font-weight:bold;font-size:17px;color:#225992;">PO No.& PO Date</td>
                <td style="width:10%;text-align:center;font-weight:bold;font-size:17px;color:#225992;">GRN No.</td>
              </tr>
              <tr>
                <td style="text-align:center;font-size:14px;font-weight:bold;"><?php echo $return_date ?></td>
                <td style="text-align:center;font-size:14px;font-weight:bold;"><?php echo $supplier_name ?></td>
                <td style="text-align:center;font-size:14px;font-weight:bold;"><?php echo $mrn_no ?></td>
                <td style="text-align:center;font-size:14px;font-weight:bold;"><?php echo $po_no.' / '.$po_date ?></td>                
                <td style="text-align:center;font-size:14px;font-weight:bold;"><?php echo $grn_no ?></td>
              </tr>
              <tr>
                <td colspan="5">
                  <table class="table table-bordered font12">
                    <thead>
                      <tr>
                        <td style="text-align:left;font-size:14px;font-weight:bold;width:30%;color:#225992;">Item name</td>
                        <td style="text-align:left;font-size:14px;font-weight:bold;width:10%;color:#225992;">Lot no</td>
                        <td style="text-align:right;font-size:14px;font-weight:bold;width:10%;color:#225992;">Return qty.</td>
                        <td style="text-align:right;font-size:14px;font-weight:bold;width:10%;color:#225992;">Item rate (Rs.)</td>                        
                        <td style="text-align:right;font-size:14px;font-weight:bold;width:10%;color:#225992;">Tax (%)</td>
                        <td style="text-align:right;font-size:14px;font-weight:bold;width:10%;color:#225992;">Amount (Rs.)</td>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $total_return_value = 0;
                        foreach($return_item_details as $item_details):
                          $item_amount = $item_details['returnRate'] * $item_details['returnQty'];
                          $total_return_value += $item_amount;
                      ?>
                        <tr>
                          <td><?php echo $item_details['itemName'] ?></td>
                          <td><?php echo $item_details['lotNo'] ?></td>
                          <td align="right"><?php echo $item_details['returnQty'] ?></td>
                          <td align="right"><?php echo $item_details['returnRate'] ?></td>
                          <td align="right"><?php echo $item_details['taxPercent'] ?></td>
                          <td align="right"><?php echo number_format(round($item_amount, 2), 2, '.', '') ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </td>
              </tr>
              <tr>
                <td style="font-weight:bold;font-size:16px;text-align:right;" colspan="2">Taxable Amount (in Rs.)</td>
                <td style="font-weight:bold;font-size:16px;text-align:right;">G.S.T (in Rs.)</td>
                <td style="font-weight:bold;font-size:16px;text-align:right;">Round Off (in Rs.)</td>
                <td style="font-weight:bold;font-size:16px;text-align:right;">Return Value (in Rs.)</td>
              </tr>
              <tr>
                <td style="font-size:18px;font-weight:bold;text-align:right;" colspan="2"><?php echo number_format($total_return_value, 2, '.', '')?></td>
                <td style="font-size:18px;font-weight:bold;text-align:right;"><?php echo number_format($round_off, 2, '.', '')?></td>                
                <td style="font-size:18px;font-weight:bold;text-align:right;"><?php echo number_format($tax_amount, 2, '.', '')?></td>
                <td style="font-size:18px;font-weight:bold;text-align:right;"><?php echo number_format($netpay, 2, '.', '')?></td>                
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>