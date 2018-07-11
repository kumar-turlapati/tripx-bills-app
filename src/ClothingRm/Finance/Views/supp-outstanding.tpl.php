<?php
  use Atawa\Utilities;
  $page_url = '/fin/billwise-outstanding';
?>
<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12">
    
    <!-- Panel starts -->
    <section class="panelBox">
      <div class="panelBody">

        <?php echo Utilities::print_flash_message() ?>

        <!-- Right links starts -->
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/suppliers/list" class="btn btn-default">
              <i class="fa fa-book"></i> Suppliers List 
            </a> 
          </div>
        </div>
        <!-- Right links ends -->

        <div class="filters-block">
          <div id="filters-form">
            <!-- Form starts -->
            <form class="form-validate form-horizontal" method="POST" action="<?php echo $page_url ?>">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1 text-right">
                  <label class="control-label text-right"><b>Filter by</b></label>          
                </div>                
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="supplierCode" id="supplierCode">
                      <?php 
                        foreach($suppliers as $key=>$value): 
                          if($sel_supp_id === $key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }                          
                      ?>
                          <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">                         
                  <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
                </div>
              </div>
            </form>    
          </div>
        </div>        
        <div class="table-responsive">
          <table class="table table-striped table-hover font12">
            <thead>
              <tr>
                <th width="3%" class="text-center">Sno.</th>
                <th width="28%" class="text-center">Supplier Name</th>
                <th width="15%" class="text-center">PO No & Date</th>
                <th width="4%" class="text-center">Credit<br />Duration</th>                
                <th width="4%" class="text-center">GRN No.</th>                
                <th width="10%" class="text-center">Bill No.</th>                
                <th width="5%" class="text-center">Bill Value</th>
                <th width="5%" class="text-center">Amt. Paid</span></th>
                <th width="5%" class="text-center">Balance</th>
              </tr>
            </thead>
            <tbody>
              <?php
                if(count($records)>0) {
                    $cntr = 1;
                    $tot_bill_value = $tot_amount_paid = $tot_amount_due = 0;
                    foreach($records as $balance_details):
                      // dump($balance_details);
                      $supp_name = $balance_details['supplierName'];
                      $supplier_code = $balance_details['supplierCode'];
                      $bill_amount = $balance_details['billAmount'];
                      $po_no = $balance_details['poNo'];
                      $po_date = date("d-m-Y",strtotime($balance_details['purchaseDate']));
                      $amount_paid = $balance_details['amountPaid'];
                      $amount_due = $balance_details['amountDue'];
                      $grn_no = $balance_details['grnNo'];
                      $bill_no = $balance_details['billNo'];
                      $credit_days = $balance_details['creditDays'];
                      $tot_bill_value += $bill_amount;
                      $tot_amount_due += $amount_due;
                      $tot_amount_paid += $amount_paid;

                      // $opbal_date = date("d-m-Y",strtotime($balance_details['openDate']));
                      // $opbal_code = $balance_details['suppOpeningCode'];
                  ?>
                    <tr class="text-right font12">
                      <td class="text-right"><?php echo $cntr ?></td>
                      <td class="text-left">
                        <a href="/fin/supplier-ledger?suppCode=<?php echo $supplier_code ?>" class="hyperlink" title="View Ledger">
                          <?php echo $supp_name ?>
                        </a>
                      </td>
                      <td class="text-left"><?php echo $po_no.', '.$po_date ?></td>
                      <td class="text-left"><?php echo $credit_days.' days' ?></td>
                      <td class="text-right"><?php echo $grn_no ?></td>
                      <td class="text-left"><?php echo $bill_no ?></td>                      
                      <td class="text-right"><?php echo number_format($bill_amount,2) ?></td>
                      <td class="text-right"><?php echo number_format($amount_paid,2) ?></td>
                      <td class="text-right"><?php echo number_format($amount_due,2) ?></td>
                    </tr>
                <?php
                  $cntr++;
                  endforeach; 
                ?>
                  <tr>
                    <td colspan="6" class="text-right text-bold">Totals</td>
                    <td class="text-right text-bold font14"><?php echo number_format($tot_bill_value,2) ?></td>
                    <td class="text-right text-bold font14"><?php echo number_format($tot_amount_paid,2) ?></td>
                    <td class="text-right text-bold font14"><?php echo number_format($tot_amount_due,2) ?></td>
                  </tr>
            <?php } else { ?>
                <tr>
                  <td colspan="9" class="text-center">No oustanding is available.</td>
                </tr>
            <?php } ?>
            </tbody>
          </table>

        </div>
      </div>
    </section>
    <!-- Panel ends -->
  </div>
</div>