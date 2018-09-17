<?php
  use Atawa\Utilities;
  $query_params = '';  
  if(isset($search_params['custName']) && $search_params['custName'] !='') {
    $custName = $search_params['custName'];
    $query_params[] = 'custName='.$custName;
  } else {
    $custName = '';
  }
  if(isset($search_params['stateCode']) && $search_params['stateCode'] !='') {
    $stateCode = $search_params['stateCode'];
    $query_params[] = 'stateCode='.$stateCode;
  } else {
    $stateCode = '';
  }  
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $pagination_url = $page_url = '/fin/cust-opbal/list';  
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/fin/cust-opbal/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> Create Opening Balance 
            </a> 
          </div>
        </div>
        <div class="filters-block">
          <div id="filters-form">
            <form class="form-validate form-horizontal" method="POST" action="<?php echo $page_url ?>" autocomplete="off">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1" style="padding-top:5px;"><b>Filter by</b></div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Customer Name" type="text" name="custName" id="custName" class="form-control" value="<?php echo $custName ?>">
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="stateCode" id="stateCode">
                      <?php 
                        foreach($states_a as $key=>$value):
                          if((int)$stateCode === (int)$key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }  
                      ?>
                       <option value="<?php echo $key ?>" <?php echo $selected ?>>
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
                <th width="35%" class="text-center">Customer Name</th>
                <th width="10%" class="text-center">Bill No.</th>
                <th width="10%" class="text-center">Bill Date</th>
                <th width="5%" class="text-center">Credit Days</th>
                <th width="10%" class="text-center">Amount</th>
                <th width="5%" class="text-center">Status</span></th>
                <th width="10%" class="text-center">Opening date</th>
                <th width="15%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                if(count($customers)>0) {
                    $cntr = 1;
                    $total_balance = 0;
                    foreach($customers as $balance_details):
                      $cust_name = $balance_details['customerName'];
                      $amount = $balance_details['amount'];
                      $opbal_date = date("d-m-Y",strtotime($balance_details['openingDate']));
                      $opbal_code = $balance_details['openingCode'];
                      $bill_no = $balance_details['billNo'];
                      $bill_date = date("d-m-Y", strtotime($balance_details['billDate']));
                      $credit_days = $balance_details['creditDays'];
                      if($balance_details['action']==='c') {
                        $status = 'Credit';
                        $total_balance -= $amount;
                      } else {
                        $status = 'Debit';
                        $total_balance += $amount;
                      }
                  ?>
                    <tr class="text-right font11">
                      <td class="text-right valign-middle"><?php echo $cntr ?></td>
                      <td class="text-left valign-middle"><?php echo $cust_name ?></td>
                      <td class="text-right valign-middle"><?php echo $bill_no ?></td>
                      <td class="text-right valign-middle"><?php echo $bill_date ?></td>
                      <td class="text-right valign-middle"><?php echo $credit_days ?></td>
                      <td class="text-right valign-middle"><?php echo number_format($amount,2,'.','') ?></td>
                      <td class="text-center valign-middle"><?php echo $status ?></td>
                      <td class="text-right valign-middle"><?php echo $opbal_date ?></td>
                      <td>
                        <div class="btn-actions-group valign-middle">
                          <?php if($opbal_code !== ''): ?>
                            <a class="btn btn-primary" href="/fin/cust-opbal/update/<?php echo $opbal_code ?>" title="Edit Details">
                              <i class="fa fa-pencil"></i>
                            </a>
                            <a class="btn btn-danger removeCustOpbal" href="/fin/cust-opbal/remove/<?php echo $opbal_code ?>" title="Remove Opening Balance">
                              <i class="fa fa-times"></i>
                            </a>                                                      
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
                  <td colspan="9" style="color:red;font-weight:bold;text-align:center;">No records are available.</td>
                </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>