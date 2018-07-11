<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  
  $current_date = date("d-m-Y");

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
    $toDate = $current_date;
  }
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = '';
  }
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = '/sales-indents/list';
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
            <a href="/sales-indent/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Sales Indent 
            </a>
          </div>
        </div>

  		  <div class="filters-block">
    		  <div id="filters-form">
            <form class="form-validate form-horizontal" method="POST">
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
                <?php /*
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
                </div> */?>
                <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
            </div>
           </form>
			    </div>
        </div>

        <div class="table-responsive">
          <?php if(count($indents)>0): ?>
           <table class="table table-striped table-hover">
            <thead>
              <tr class="font12">
                <th width="5%" class="text-center valign-middle">Sno</th>
                <th width="6%" class="text-center valign-middle">Indent <br />No.</th>
                <th width="8%" class="text-center valign-middle">Indent <br />Date</th>
                <th width="8%" class="text-center valign-middle">Indent value<br />(in Rs.)</th>
                <th width="20%" class="text-center valign-middle">Customer name</span></th>
                <th width="10%" class="text-center valign-middle">Store name</span></th>                
                <th width="10%" class="text-center valign-middle">Referred by</th>
                <th width="14%" class="text-center valign-middle">Campaign name</th>                
                <th width="8%" class="text-center valign-middle">Status</th>
                <th width="10%" class="text-center valign-middle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $cntr = $sl_no;
                $total = 0;
                foreach($indents as $indent_details):
                  $indent_date = date("d-m-Y", strtotime($indent_details['indentDate']));
                  $indent_no = $indent_details['indentNo'];
                  $indent_code = $indent_details['indentCode'];
                  $customer_name = $indent_details['customerName'];
                  $gst_no = $indent_details['gstNo'];
                  $primary_mobile_no = $indent_details['primaryMobileNo'];
                  $alter_mobile_no = $indent_details['alterMobileNo'];
                  $total_amount = $indent_details['totalAmount'];
                  $round_off = $indent_details['roundOff'];
                  $netpay = $indent_details['netpay'];

                  $total += $netpay;
              ?>
                <tr class="font12">
                  <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                  <td align="right" class="valign-middle"><?php echo $indent_no ?></td>
                  <td class="valign-middle"><?php echo $indent_date ?></td>
                  <td align="right" class="valign-middle text-bold"><?php echo number_format($netpay,2,'.','') ?></td>
                  <td align="left" class="valign-middle"><?php echo $customer_name ?></td>                
                  <td class="valign-middle"><?php //echo $ref_no ?></td>
                  <td class="valign-middle"><?php //echo $ref_date ?></td>
                  <td class="valign-middle"><?php //echo $narration ?></td>
                  <td class="valign-middle">&nbsp;</td>
                  <td class="valign-middle">
                    <div class="btn-actions-group">                    
                      <a class="btn btn-danger" href="/print-indent?indentNo=<?php echo $indent_no ?>" title="Print Sales Indent With Rate" target="_blank">
                        <i class="fa fa-print"></i>
                      </a>&nbsp;
                      <a class="btn btn-primary" href="/print-indent-wor?indentNo=<?php echo $indent_no ?>" title="Print Sales Indent without Rate" target="_blank">
                        <i class="fa fa-print"></i>
                      </a>                      
                    </div>
                  </td>
                </tr>
              <?php
                $cntr++;
                endforeach; 
              ?>
              <tr class="text-bold">
                <td colspan="3" align="right">TOTALS</td>
                <td align="right"><?php echo number_format($total, 2) ?></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>                
              </tr>
            </tbody>
          </table>
          <?php endif; ?>
          <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
        </div>
      </div>
    </section>
  </div>
</div>