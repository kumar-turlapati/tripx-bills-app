<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  
  $page_url = '/loyalty-member/ledger/'.$member_code;

  $store_name = isset($member_info['locationID'])  ? $location_ids[$member_info['locationID']] : '';
  $created_on = isset($member_info['createdDate']) ? date("d-m-Y", strtotime($member_info['createdDate'])) : date("d-m-Y");
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">

        <?php echo Utilities::print_flash_message() ?>

        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php endif; ?>

        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/loyalty-member/add" class="btn btn-default">
              <i class="fa fa-diamond"></i> New Loyalty Member 
            </a>
            <a href="/loyalty-members/list" class="btn btn-default">
              <i class="fa fa-book"></i> Loyalty Members Register
            </a>             
          </div>
        </div>
        <div class="table-responsive">
          <?php if(count($transactions)>0): ?>
             <table class="table table-striped table-hover" style="margin-bottom:0;">
              <thead>
                <tr class="font12">
                  <th width="25%" class="text-center valign-middle">Member name</th>
                  <th width="10%" class="text-center valign-middle">Mobile no.</th>
                  <th width="10%" class="text-center valign-middle">Card no.</span></th>
                  <th width="10%" class="text-center valign-middle">Created on</span></th>
                  <th width="20%" class="text-center valign-middle">Store name</span></th>                
                </tr>
              </thead>
              <tbody>
                <tr class="font12">
                  <td class="valign-middle" style="font-size:16px;text-align:center;"><?php echo $member_info['memberName'] ?></td>
                  <td class="valign-middle" style="font-size:16px;text-align:center;"><?php echo $member_info['mobileNo'] ?></td>
                  <td class="valign-middle" style="font-size:16px;text-align:center;"><?php echo $member_info['cardNo'] ?></td>
                  <td class="valign-middle" style="font-size:16px;text-align:center;"><?php echo $created_on ?></td>             
                  <td class="valign-middle" style="font-size:16px;text-align:center;"><?php echo $store_name ?></td>
                </tr>
              </tbody>
            </table>
            <table class="table table-striped table-hover">
              <thead>
                <tr class="font12">
                  <th width="15%" class="text-center valign-middle">Transaction no.</th>
                  <th width="15%" class="text-center valign-middle">Transaction value ( in Rs. )</th>
                  <th width="15%" class="text-center valign-middle">Referral percent</span></th>
                  <th width="15%" class="text-center valign-middle">Credits ( in Rs. )</span></th>
                  <th width="15%" class="text-center valign-middle">Debits ( in Rs. )</span></th>                
                </tr>
              </thead>
              <tbody>
                <?php 
                  foreach($transactions as $tran_details):
                    $tran_no = $tran_details['billNo'];
                    $tran_value = $tran_details['billValue'];
                    $ref_percent = $tran_details['addedPercent'];
                    if($tran_details['tranType'] === 'b') {
                      $credits = $tran_details['addedValue'];
                      $debits = 0;
                    } else {
                      $debits = $tran_details['addedValue'];
                      $credits = 0;
                    }
                ?>
                  <tr class="font12">
                    <td class="valign-middle" style="font-size:14px;text-align:center;"><?php echo $tran_no ?></td>
                    <td class="valign-middle" style="font-size:14px;text-align:right;"><?php echo $tran_value ?></td>
                    <td class="valign-middle" style="font-size:14px;text-align:right;"><?php echo $ref_percent ?></td>
                    <td class="valign-middle" style="font-size:14px;text-align:right;"><?php echo number_format($credits,2) ?></td>             
                    <td class="valign-middle" style="font-size:14px;text-align:right;"><?php echo number_format($debits,2) ?></td>
                  </tr>
                <?php endforeach; ?>
                  <tr>
                    <td class="text-right" style="font-size:14px;font-weight:bold;">LEDGER TOTALS</td>
                    <td class="text-bold text-right" style="font-size:14px;"><?php echo isset($query_totals['billValue']) ? number_format($query_totals['billValue'],2) : "0.00" ?></td>
                    <td class="text-bold text-right" style="font-size:14px;">&nbsp;</td>
                    <td class="text-bold text-right" style="font-size:14px;"><?php echo isset($query_totals['addedValue']) ? number_format($query_totals['addedValue'],2) : "0.00"  ?></td>
                    <td class="text-bold text-right" style="font-size:14px;"><?php echo isset($query_totals['consumedValue']) ? number_format($query_totals['consumedValue'],2) : "0.00" ?></td>
                  </tr>
              </tbody>
            </table>

          <?php else: ?>
            <div style="text-align:center;margin-top:10px;font-weight:bold;color:red;font-size:14px;border:1px dotted;">No transactions are available for Card No. <?php echo $member_details['cardNo'] ?></div>
          <?php endif; ?>

          <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
        </div>
      </div>
    </section>
  </div>
</div>