<?php
  use Atawa\Utilities;
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
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th width="5%" class="text-center">Sno.</th>
                <th width="35%" class="text-center">Supplier Name</th>
                <th width="12%" class="text-center">Opening Balance <br />(in Rs.)</th>
                <th width="12%" class="text-center">Purchases<br />as on Date<br />(in Rs.)</th>
                <th width="12%" class="text-center">Total Due<br />as on Date<br />(in Rs.)</th>
                <th width="12%" class="text-center">Amount Paid<br />as on Date<br />(in Rs.)</span></th>
                <th width="12%" class="text-center">Balance<br />(in Rs.)</th>
              </tr>
            </thead>
            <tbody>
              <?php
                if(count($records)>0) {
                    $cntr = 1;
                    $tot_opbal_amount = $tot_purch_amount = $tot_amount_paid = 
                    $tot_due = $tot_balance = 0;
                    foreach($records as $balance_details):
                      $supp_name = $balance_details['supplierName'];
                      $supplier_code = $balance_details['supplierCode'];

                      $opbal_amount = $balance_details['openingBalance'];
                      $purch_amount = $balance_details['purchaseAmount'];
                      $due = $opbal_amount+$purch_amount;
                      $amount_paid = $balance_details['amountPaid'];
                      $balance = $due-$amount_paid;

                      $tot_opbal_amount += $opbal_amount;
                      $tot_purch_amount += $purch_amount;
                      $tot_due += $due;

                      $tot_amount_paid += $amount_paid;
                      $tot_balance += $balance;
                  ?>
                    <tr class="text-right font12">
                      <td class="text-right"><?php echo $cntr ?></td>
                      <td class="text-left">
                        <a href="/fin/supplier-ledger?suppCode=<?php echo $supplier_code ?>" class="hyperlink" title="View Ledger">
                          <?php echo $supp_name ?>
                        </a>
                      </td>
                      <td class="text-right"><?php echo number_format($opbal_amount,2) ?></td>
                      <td class="text-right"><?php echo number_format($purch_amount,2) ?></td>
                      <td class="text-right"><?php echo number_format($due,2) ?></td>
                      <td class="text-right"><?php echo number_format($amount_paid,2) ?></td>
                      <td class="text-right"><?php echo number_format($balance,2) ?></td>                      
                    </tr>
                <?php
                  $cntr++;
                  endforeach; 
                ?>
                  <tr>
                    <td colspan="2" class="text-right text-bold">Totals</td>
                    <td class="text-right text-bold"><?php echo number_format($tot_opbal_amount,2) ?></td>
                    <td class="text-right text-bold"><?php echo number_format($tot_purch_amount,2) ?></td>
                    <td class="text-right text-bold"><?php echo number_format($tot_due,2) ?></td>
                    <td class="text-right text-bold"><?php echo number_format($tot_amount_paid,2) ?></td>
                    <td class="text-right text-bold"><?php echo number_format($tot_balance,2) ?></td>
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