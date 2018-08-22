<?php
  use Atawa\Utilities;

  $current_date = date("d-m-Y");
  $pagination_url = '/sales-return/list';
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
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '') {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = '';
  }

  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
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
            <a href="/sales/entry-with-barcode" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Sale 
            </a>&nbsp;&nbsp; 
            <a href="/sales/list" class="btn btn-default">
              <i class="fa fa-inr"></i> Sales Register
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
                <th width="5%"  class="text-center valign-middle">Sno.</th>
                <th width="10%" class="text-center valign-middle">MRN No.</th>
                <th width="10%" class="text-center valign-middle">Return date</th>
                <th width="10%" class="text-center valign-middle">Store name</th>                
                <th width="15%" class="text-center valign-middle">Bill No.&Date</th>
                <th width="8%" class="text-center valign-middle">Sale value<br />(in Rs.)</th>
                <th width="8%" class="text-center valign-middle">Return value<br />(in Rs.)</th>
                <th width="8%" class="text-center valign-middle">Credit notes<br />raised (in Rs.)</th>
                <th width="13%" class="text-center valign-middle">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $cntr = $sl_no;
                foreach($sales_returns as $sales_return_details):
                  $return_date = date("d-m-Y", strtotime($sales_return_details['returnDate']));
                  $mrn_no = $sales_return_details['mrnNo'];
                  $bill_no = $sales_return_details['billNo'];
                  $invoice_date = date("d-m-Y", strtotime($sales_return_details['invoiceDate']));
                  $sale_value = $sales_return_details['netPay'];
                  $return_value = $sales_return_details['returnAmount'];
                  $return_status = $sales_return_details['returnStatus'];
                  $sales_code = $sales_return_details['invoiceCode'];
                  $return_code = $sales_return_details['returnCode'];
                  $location_id = $sales_return_details['locationID'];
                  $location_name = isset($location_ids[$location_id]) ?  $location_ids[$location_id] : 'Invalid';
                  $cn_value = $sales_return_details['cnValue'];
              ?>
                  <tr class="text-uppercase text-right font11">
                    <td class="text-center valign-middle"><?php echo $cntr ?></td>
                    <td class="text-left valign-middle"><?php echo $mrn_no ?></td>
                    <td class="text-left valign-middle"><?php echo $return_date ?></td>       
                    <td class="text-left valign-middle"><?php echo $location_name ?></td>                    
                    <td class="valign-middle"><?php echo $bill_no.' / '.$invoice_date ?></td>
                    <td class="text-right valign-middle"><?php echo number_format($sale_value,2) ?></td>
                    <td class="text-right valign-middle"><?php echo number_format($return_value,2) ?></td>
                    <td class="text-right valign-middle"><?php echo number_format($cn_value,2) ?></td>
                    <td class="valign-middle">
                      <div class="btn-actions-group">
                        <?php if($return_code !== ''): ?>
                          <a class="btn btn-primary" href="/sales-return/view/<?php echo $sales_code.'/'.$return_code ?>" title="View Sales Return Transaction">
                            <i class="fa fa-eye"></i>
                          </a>&nbsp;
                          <a class="btn btn-primary" href="javascript: printSalesReturnBill('<?php echo $return_code ?>')" title="Print Sales Return Bill">
                            <i class="fa fa-print"></i>
                          </a>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
            <?php
              $cntr++;
              endforeach; 
            ?>
            </tbody>
          </table>
          <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
        </div>
      </div>
    </section>
  </div>
</div>