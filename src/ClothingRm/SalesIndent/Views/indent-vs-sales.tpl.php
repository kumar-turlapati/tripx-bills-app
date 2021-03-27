<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  
  $current_date = date("d-m-Y");

  // dump($search_params);
  // exit;

  $query_params = [];
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
  if(isset($search_params['campaignCode']) && $search_params['campaignCode'] !== '' ) {
    $locationCode = $search_params['campaignCode'];
    $query_params[] = 'campaignCode='.$campaignCode;
  } else {
    $campaignCode = '';
  }
  if(isset($search_params['agentCode']) && $search_params['agentCode'] !== '' ) {
    $agentCode = $search_params['agentCode'];
    $query_params[] = 'agentCode='.$campaignCode;
  } else {
    $agentCode = '';
  }
  if(isset($search_params['status']) && $search_params['status'] !== '') {
    $status = $search_params['status'];
    $query_params[] = 'status='.$status;
  } else {
    $status = 99;
  }
  if(isset($search_params['custName']) && $search_params['custName'] !== '' ) {
    $customer_name = $search_params['custName'];
    $query_params[] = 'custName='.$search_params['custName'];    
  } else {
    $customer_name = '';
  }
  if(isset($search_params['qtyType']) && $search_params['qtyType'] !== '' ) {
    $qty_type = $search_params['qtyType'];
    $query_params[] = 'qtyType='.$search_params['qtyType'];    
  } else {
    $qty_type = 'all';
  }
  if(isset($search_params['executiveCode']) && $search_params['executiveCode'] !== '' ) {
    $exe_code = $search_params['executiveCode'];
    $query_params[] = 'executiveCode='.$search_params['executiveCode'];    
  } else {
    $exe_code = '';
  }
  if(isset($search_params['brandName']) && $search_params['brandName'] !== '' ) {
    $brand_name = $search_params['brandName'];
    $query_params[] = 'brandName='.$search_params['brandName'];    
  } else {
    $brand_name = '';
  }
  if(isset($search_params['campaignCode']) && $search_params['campaignCode'] !== '' ) {
    $campaign_code = $search_params['campaignCode'];
    $query_params[] = 'campaignCode='.$campaignCode;
  } else {
    $campaign_code = '';
  }  
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = $pagination_url = '/indent-vs-sales';
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
            <a href="/indent-vs-sales-by-item" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> Indent vs Sales (Itemwise)
            </a>
          </div>
        </div>

  		  <div class="filters-block">
    		  <div id="filters-form">
            <form class="form-validate form-horizontal" method="POST" action="<?php echo $page_url ?>">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1 labelStyle" style="padding-top:9px;">Filter by</div>

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
                  <input 
                    placeholder="Customer name" 
                    type="text" 
                    name="custName" 
                    id="custName" 
                    class="form-control cnameAc" 
                    value="<?php echo $customer_name ?>"
                  />
                </div>

                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="qtyType" id="qtyType">
                      <?php 
                        foreach($qty_types as $key => $value):
                          if($key === $qty_type) {
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

                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="agentCode" id="agentCode">
                      <?php 
                        foreach($agents as $agent_code => $agent_name):
                          if($agentCode === $agent_code) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }  
                      ?>
                       <option value="<?php echo $agent_code ?>" <?php echo $selected ?>>
                          <?php echo $agent_name ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>

                <div class="col-sm-12 col-md-2 col-lg-2" style="margin-left: 91px; margin-top: 5px;">
                  <div class="select-wrap">
                    <select class="form-control" name="executiveCode" id="executiveCode">
                      <?php 
                        foreach($sa_executives as $key => $exe_name):
                          if($key === $exe_code) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }  
                      ?>
                       <option value="<?php echo $key ?>" <?php echo $selected ?>>
                          <?php echo $exe_name ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>

                <div class="col-sm-12 col-md-2 col-lg-2" style="margin-top: 5px;">
                  <input 
                    placeholder="Brand name" 
                    type="text" 
                    name="brandName" 
                    id="brandName" 
                    class="form-control" 
                    value="<?php echo $brand_name ?>"
                  />
                </div>

                <div class="col-sm-12 col-md-2 col-lg-2" style="margin-top: 5px;">
                  <div class="select-wrap">
                    <select class="form-control" name="campaignCode" id="campaignCode">
                      <?php 
                        foreach($campaigns as $campaign_key => $campaign_name):
                          if($campaign_key === $campaignCode) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }  
                      ?>
                       <option value="<?php echo $campaign_key ?>" <?php echo $selected ?>>
                          <?php echo $campaign_name ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>                               
                
                <div align="center"><br /><br />
                  <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
                </div>
              </div>
            </form>
			    </div>
        </div>

        <div class="table-responsive">
          <?php if(count($sales)>0): ?>
           <table class="table table-striped table-hover">
            <thead>
              <tr class="font11">
                <th width="5%" class="text-center valign-middle">Sno</th>
                <th width="6%" class="text-center valign-middle">Indent <br />No.</th>
                <th width="10%" class="text-center valign-middle">Indent <br />Date</th>
                <th width="20%" class="text-center valign-middle">Item name</th>
                <th width="16%" class="text-center valign-middle">Brand name</th>
                <th width="16%" class="text-center valign-middle">Customer name</span></th>
                <th width="7%" class="text-center valign-middle">Indent<br />qty.</th>
                <th width="7%" class="text-center valign-middle">Indent<br />value (Rs.)</th>                
                <th width="7%" class="text-center valign-middle">Dispatched<br />qty.</th>
                <th width="7%" class="text-center valign-middle">Dispatched<br />value (Rs.)</th>
                <th width="7%" class="text-center valign-middle">Pending<br />qty.</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $cntr = $sl_no;
                $tot_indent_qty = $tot_disp_qty = $tot_indent_value = $tot_disp_value = 0;
                $tot_pending_qty = 0;
                foreach($sales as $indent_details):
                  // $campaign_name = $indent_details['campaignName'];
                  // $agent_name = $indent_details['agentName'];
                  $customer_name = $indent_details['customerName'];

                  $indent_no = $indent_details['indentNo'];
                  $indent_date = date("d-m-Y", strtotime($indent_details['indentDate']));
                  // $indent_code = $indent_details['indentCode'];
                  $item_name = $indent_details['itemName'];
                  $brand_name = $indent_details['brandName'];

                  $indent_qty = $indent_details['indentQty'];
                  $dispatched_qty = $indent_details['dispatchedQty'];
                  $indent_value = $indent_details['indentValue'];
                  $dispatched_value = $indent_details['dispatchedValue'];
                  $pending_qty = $indent_qty-$dispatched_qty;

                  $tot_indent_qty += $indent_qty;
                  $tot_disp_qty += $dispatched_qty;
                  $tot_indent_value += $indent_value;
                  $tot_disp_value += $dispatched_value;
                  $tot_pending_qty += $pending_qty;
              ?>
                <tr class="font11">
                  <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                  <td align="right" class="valign-middle"><?php echo $indent_no ?></td>
                  <td class="valign-middle"><?php echo $indent_date ?></td>
                  <td align="left" class="valign-middle text-bold" title="<?php echo $item_name ?>"><?php echo $item_name ?></td>
                  <td align="left" class="valign-middle" title="<?php echo $brand_name ?>"><?php echo $brand_name ?></td>                
                  <td class="valign-middle" title="<?php echo $customer_name ?>"><?php echo $customer_name ?></td>
                  <td class="valign-middle" align="right"><?php echo number_format($indent_details['indentQty'],2,'.','') ?></td>
                  <td class="valign-middle" align="right"><?php echo number_format($indent_details['indentValue'],2,'.','') ?></td>
                  <td class="valign-middle" align="right"><?php echo number_format($indent_details['dispatchedQty'],2,'.','') ?></td>
                  <td class="valign-middle" align="right"><?php echo number_format($indent_details['dispatchedValue'],2,'.','') ?></td>
                  <td class="valign-middle" align="right"><?php echo number_format($indent_qty-$dispatched_qty,2,'.','') ?></td>
                </tr>
              <?php
                $cntr++;
                endforeach; 
              ?>
              <tr class="text-bold" style="font-size: 14px;">
                <td colspan="6" align="right">PAGE TOTALS</td>
                <td align="right"><?php echo number_format($tot_indent_qty, 2, '.', '') ?></td>
                <td align="right"><?php echo number_format($tot_indent_value, 2, '.', '') ?></td>
                <td align="right"><?php echo number_format($tot_disp_qty, 2, '.', '') ?></td>
                <td align="right"><?php echo number_format($tot_disp_value, 2, '.', '') ?></td>
                <td align="right"><?php echo number_format($tot_pending_qty, 2, '.', '') ?></td>
              </tr>
              <tr class="text-bold" style="font-size: 14px;">
                <td colspan="6" align="right">QUERY TOTALS</td>
                <td align="right"><?php echo number_format($query_totals['totalIndentQty'], 2, '.', '') ?></td>
                <td align="right"><?php echo number_format($query_totals['totalIndentValue'], 2, '.', '') ?></td>
                <td align="right"><?php echo number_format($query_totals['totalDispatchedQty'], 2, '.', '') ?></td>
                <td align="right"><?php echo number_format($query_totals['totalDispatchedValue'], 2, '.', '') ?></td>
                <td align="right"><?php echo number_format($query_totals['totalIndentQty']-$query_totals['totalDispatchedQty'] , 2, '.', '') ?></td>
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