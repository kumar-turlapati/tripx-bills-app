<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  
  $current_date = date("01-m-Y");

  $query_params = '';
  if(isset($search_params['fromDate']) && $search_params['fromDate'] !='') {
    $fromDate = $search_params['fromDate'];
    $query_params[] = 'fromDate='.$fromDate;
  } else {
    $fromDate = $current_date;
  }
  if(isset($search_params['toDate']) && $search_params['toDate'] !='' ) {
    $toDate = $search_params['toDate'];
    $query_params[] = 'toDate='.$toDate;
  } else {
    $days_in_month = Utilities::get_number_of_days_in_month(date("m"));
    $toDate = date($days_in_month."-m-Y");
  }
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = $default_location;
  }
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = $pagination_url = '/fin/sales2cb/register';
  $cash_posting_sales_dates = array_column($cash_postings, 'salesDate');

  // dump($cash_postings);
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
            <a href="/fin/cash-voucher/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Cash Voucher 
            </a>
            <a href="/fin/cash-vouchers" class="btn btn-default">
              <i class="fa fa-inr"></i> Cash Register
            </a>            
          </div>
        </div>

  		  <div class="filters-block">
    		  <div id="filters-form">
            <form class="form-validate form-horizontal" autocomplete="Off" action="<?php echo $page_url ?>" method="POST">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1">Filter by</div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" size="16" type="text" readonly name="fromDate" id="fromDate" value="<?php echo $fromDate ?>" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
                    <input class="span2" size="16" type="text" readonly name="toDate" id="toDate" value="<?php echo $toDate ?>" />
                    <span class="add-on"><i class="fa fa-calendar"></i></span>
                  </div>
                </div>
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
           <table class="table table-striped table-hover">
            <thead>
              <tr class="font12">
                <th width="5%" class="text-center valign-middle">Sno</th>
                <th width="10%" class="text-center valign-middle">Date</th>
                <th width="10%" class="text-center valign-middle">Cash from<br />Sales (in Rs.)</th>
                <th width="10%" class="text-center valign-middle">Action</th>
                <th width="10%" class="text-center valign-middle">Posting<br />Status</th>
                <th width="10%" class="text-center valign-middle">Voucher Date</th>
                <th width="10%" class="text-center valign-middle">Voucher No</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                if(count($day_sales)>0):
                  $cntr = 0;
                  $tot_cash_sales = 0;
                  foreach($day_sales as $tran_date => $amount):
                    $cntr++;
                    $tran_date_actual = $tran_date;
                    $tran_ts = strtotime($tran_date);
                    $tran_date = date("d-m-Y", $tran_ts);
                    $tot_cash_sales += $amount;
                    $posting_key = array_search($tran_date_actual, $cash_posting_sales_dates);
                    if($amount <= 0) {
                      $amount = '<i class="fa fa-ban" aria-hidden="true" style="color:red;" title="No Cash Sales on this day."></i>';
                      $post_option = false;
                    } else {
                      $amount = number_format($amount, 2, '.', '');
                      $post_option = true;
                    }
                    if($posting_key !== false) {
                      $voc_date = date("d-m-Y", strtotime($cash_postings[$posting_key]['vocDate']));
                      $voc_no = $cash_postings[$posting_key]['vocNo'];
                    } else {
                      $voc_date = '';
                      $voc_no = '';
                    }
                ?>
                  <tr class="font11">
                    <td align="right" class="valign-middle"><?php echo $cntr; ?></td>
                    <td class="valign-middle" align="center"><?php echo $tran_date ?></td>
                    <td align="right" class="valign-middle labelStyle"><?php echo $amount ?></td>
                    <?php if($post_option && $posting_key === false): ?>
                      <td class="valign-middle" align="center">
                        <button class="btn btn-warning postSc2CB" name="op" id="btn_<?php echo $tran_ts ?>" title="Click here to add this day's Sales Cash of Rs.<?php echo $amount ?> to Cash Book">
                          <i class="fa fa-inr"></i> Post to Cash Book
                        </button>
                        <input type="hidden" value="<?php echo $tran_date ?>" id="dt_<?php echo $tran_ts ?>" />
                        <input type="hidden" value="<?php echo $amount ?>" id="amt_<?php echo $tran_ts ?>" />
                      </td>
                      <td class="valign-middle" id="ps_<?php echo $tran_ts ?>" align="center">&nbsp;</td>
                      <td class="valign-middle" id="vd_<?php echo $tran_ts ?>" align="center">&nbsp;</td>
                      <td class="valign-middle" id="vn_<?php echo $tran_ts ?>" align="center">&nbsp;</td>
                    <?php elseif($voc_no > 0): ?>
                      <td class="valign-middle" align="center"><i class="fa fa-ban" aria-hidden="true" style="color:red;"></i></td>
                      <td class="valign-middle" align="center">
                        <span style="color:green;font-weight:bold;font-size:14px;">Posted</span>
                      </td>
                      <td class="valign-middle" align="center">
                        <span style="color:green;font-weight:bold;font-size:14px;"><?php echo $voc_date ?></span>
                      </td>
                      <td class="valign-middle" align="center">
                        <span style="color:green;font-weight:bold;font-size:14px;"><?php echo $voc_no ?></span>
                      </td>
                    <?php else: ?>
                      <td class="valign-middle" align="center"><i class="fa fa-ban" aria-hidden="true" style="color:red;"></i></td>
                      <td class="valign-middle" align="center"><i class="fa fa-ban" aria-hidden="true" style="color:red;"></i></td>
                      <td class="valign-middle" align="center"><i class="fa fa-ban" aria-hidden="true" style="color:red;"></i></td>
                      <td class="valign-middle" align="center"><i class="fa fa-ban" aria-hidden="true" style="color:red;"></i></td>
                    <?php endif; ?>
                  </tr>
                <?php endforeach; ?>
                  <tr>
                    <td colspan="2" align="right" style="font-weight:bold;font-size:16px;color:#000;">Total Cash Sales</td>
                    <td style="font-size:16px;font-weight:bold;color:#000;text-align:right;">
                      <?php echo number_format($tot_cash_sales, 2, '.', '') ?>
                    </td>
                    <td class="valign-middle" colspan="4">&nbsp;</td>
                  </tr>
              <?php else: ?>
                  <tr>
                    <td colspan="7" align="center" style="font-size:30px;vertical-align:middle;color:red;">
                      <i class="fa fa-frown-o" aria-hidden="true"></i>
                    </td>
                  </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>