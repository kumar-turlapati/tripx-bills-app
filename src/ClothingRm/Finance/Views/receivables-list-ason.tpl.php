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
        <!--div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/suppliers/list" class="btn btn-default">
              <i class="fa fa-book"></i> Suppliers List 
            </a> 
          </div>
        </div-->
        <!-- Right links ends -->
        
        <h2 class="hdg-reports text-center">Receivables as on date</h2>        
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th width="5%" class="text-center">Sno.</th>
                <th width="30%" class="text-center">Customer Name</th>
                <th width="15%" class="text-center">Ref. No.</th>                
                <th width="15%" class="text-center">Bill Amount<br />(in Rs.)</th>
                <th width="15%" class="text-center">Received as on Date<br />(in Rs.)</th>
                <th width="15%" class="text-center">Balance as on Date<br />(in Rs.)</th>
              </tr>
            </thead>
            <tbody>
              <?php
                if(count($receivables)>0) {
                    $cntr = 1;
                    $tot_bill_amount = $tot_amount_paid = $tot_balance = 0;
                    foreach($receivables as $rcpt_details):
                      $cust_name = $rcpt_details['customerName'];
                      $ref_number = $rcpt_details['refNo'];

                      $bill_amount = $rcpt_details['amountDue'];
                      $amount_paid = 0;
                      $due = $bill_amount+$amount_paid;

                      $tot_bill_amount += $bill_amount;
                      $tot_amount_paid += $amount_paid;
                      $tot_balance += $due;
                  ?>
                    <tr class="text-right font12">
                      <td class="text-right"><?php echo $cntr ?></td>
                      <td class="text-left"><?php echo $cust_name ?></td>
                      <td class="text-right"><?php echo $ref_number ?></td>                      
                      <td class="text-right"><?php echo number_format($bill_amount,2) ?></td>
                      <td class="text-right"><?php echo number_format($amount_paid,2) ?></td>
                      <td class="text-right"><?php echo number_format($due,2) ?></td>
                    </tr>
                <?php
                  $cntr++;
                  endforeach; 
                ?>
                  <tr>
                    <td colspan="3" class="text-right text-bold">Totals</td>
                    <td class="text-right text-bold"><?php echo number_format($tot_bill_amount,2) ?></td>
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