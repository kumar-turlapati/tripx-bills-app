<?php
  use Atawa\Utilities;

  $page_url = $pagination_url = '/fin/supp-opbal/list';  
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/fin/supp-opbal/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> Create Supplier Opening Balance 
            </a> 
          </div>
        </div>

        <div class="filters-block">
          <div id="filters-form">
            <form class="form-validate form-horizontal" method="POST">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1">Filter by</div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="locationCode" id="locationCode">
                      <?php 
                        foreach($client_locations as $location_key=>$value):
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
                <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
              </div>
           </form>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped table-hover font12">
            <thead>
              <tr>
                <th width="5%" class="text-center">Sno.</th>
                <th width="30%" class="text-center">Supplier name</th>
                <th width="8%" class="text-center">Bill no.</th>
                <th width="8%" class="text-center">Bill date</th>
                <th width="5%" class="text-center">Credit days</th>                
                <th width="8%" class="text-center">Amount</th>
                <th width="5%" class="text-center">Status</span></th>
                <th width="10%">Opening date</th>
                <th width="10%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                if(count($balances)>0) {
                    $cntr = 1;
                    $total_balance = 0;
                    foreach($balances as $balance_details):
                      $supp_name = $balance_details['supplierName'];
                      $amount = $balance_details['amount'];
                      $opbal_date = date("d-m-Y",strtotime($balance_details['openDate']));
                      $opbal_code = $balance_details['suppOpeningCode'];
                      $bill_no = $balance_details['billNo'];
                      $bill_date = date("d-m-Y", strtotime($balance_details['billDate']));
                      $credit_days = $balance_details['creditDays'];                      
                      $bill_no = $balance_details['billNo'];
                      $bill_date = date("d-m-Y", strtotime($balance_details['billDate']));
                      $credit_days = $balance_details['creditDays'];                      
                      if((int)$balance_details['action'] === 1) {
                        $status = 'Credit';
                        $total_balance += $amount;
                      } else {
                        $status = 'Debit';
                        $total_balance -= $amount;
                      }
                  ?>
                    <tr class="text-right font11">
                      <td class="text-right valign-middle"><?php echo $cntr ?></td>
                      <td class="text-left valign-middle"><?php echo $supp_name ?></td>
                      <td class="text-right valign-middle"><?php echo $bill_no ?></td>
                      <td class="text-right valign-middle"><?php echo $bill_date ?></td>
                      <td class="text-right valign-middle"><?php echo $credit_days ?></td>                      
                      <td class="text-right valign-middle"><?php echo number_format($amount,2,'.','') ?></td>
                      <td class="text-center valign-middle"><?php echo $status ?></td>
                      <td class="text-right valign-middle"><?php echo $opbal_date ?></td>
                      <td align="center">
                        <div class="btn-actions-group">
                          <?php if($opbal_code !== ''): ?>
                            <a class="btn btn-primary" href="/fin/supp-opbal/update/<?php echo $opbal_code ?>" title="Edit Details">
                              <i class="fa fa-pencil"></i>
                            </a>
                            <?php /*
                            <a class="btn btn-danger" href="#" title="Remove Opening Balance">
                              <i class="fa fa-times"></i>
                            </a>*/?>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                <?php
                  $cntr++;
                  endforeach; 
                ?>
                  <tr>
                    <td colspan="5" class="text-right text-bold">Totals</td>
                    <td class="text-right text-bold"><?php echo number_format($total_balance,2,'.','') ?></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>                                        
                  </tr>
            <?php } else { ?>
                <tr>
                  <td colspan="9" align="center">No data available.</td>
                </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>