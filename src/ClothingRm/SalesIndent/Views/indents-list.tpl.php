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
  } else {
    $customer_name = '';
  }  
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = $pagination_url = '/sales-indents/list';
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
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">                  
                    <select class="form-control" name="status" id="status">
                      <?php 
                        foreach($status_a as $key => $value):
                          if((int)$key === (int)$status) {
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
                </div><br />
              </div>
              <div style="text-align: center;">
                <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
              </div>
            </form>
			    </div>
        </div>

        <div class="table-responsive">
          <?php if(count($indents)>0): ?>
           <table class="table table-striped table-hover">
            <thead>
              <tr class="font11">
                <th width="5%" class="text-center valign-middle">Sno</th>
                <th width="7%" class="text-center valign-middle">Indent <br />No.</th>
                <th width="7%" class="text-center valign-middle">Indent <br />Date</th>
                <th width="6%" class="text-center valign-middle">Indent value<br />(in Rs.)</th>
                <th width="20%" class="text-center valign-middle">Customer name</span></th>
                <th width="10%" class="text-center valign-middle">Invoice no</th>
                <th width="10%" class="text-center valign-middle">Campaign name</th>                
                <th width="10%" class="text-center valign-middle">Status</th>
                <th width="20%" class="text-center valign-middle">Actions</th>
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
                  $campaign_name = $indent_details['campaignName'];
                  $agent_name = $indent_details['agentName'];
                  $indent_status = (int)$indent_details['status'];
                  $invoice_no = (int)$indent_details['invoiceNo'];
                  $invoice_code = $indent_details['invoiceCode'];
                  if($invoice_no > 0) {
                    $status = '<span style="color:green;font-weight:bold;font-size:11px;"><i class="fa fa-check" aria-hidden="true"></i>&nbsp;Billed</span>';
                  } else {
                    if($indent_status===1) {
                      $status = '<span style="color:#225992;font-weight:bold;font-size:11px;"><i class="fa fa-gavel" aria-hidden="true"></i>&nbsp;Approved</span>';
                    } elseif($indent_status===0) {
                      $status = '<span style="color:brown;font-weight:bold;font-size:11px;"><i class="fa fa-exclamation" aria-hidden="true"></i>&nbsp;Pending</span>';
                    } elseif($indent_status===2) {
                      $status = '<span style="color:red;font-weight:bold;font-size:11px;"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;Rejected</span>';
                    } elseif($indent_status===4) {
                      $status = '<span style="color:red;font-weight:bold;font-size:11px;"><i class="fa fa-ellipsis-h" aria-hidden="true"></i>&nbsp;On Hold</span>';
                    } elseif($indent_status===5) {
                      $status = '<span style="color:red;font-weight:bold;font-size:11px;"><i class="fa fa-times" aria-hidden="true"></i>&nbsp;Cancelled</span>';                 
                    } else {
                      $status = 'Invalid';
                    }
                  }
                  $remarks2 = isset($indent_details['remarks2']) ? $indent_details['remarks2'] : '';
                  $total += $netpay;
              ?>
                <tr class="font11">
                  <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                  <td align="right" class="valign-middle"><?php echo $indent_no ?></td>
                  <td class="valign-middle"><?php echo $indent_date ?></td>
                  <td align="right" class="valign-middle text-bold"><?php echo number_format($netpay,2,'.','') ?></td>
                  <td align="left" class="valign-middle" title="<?php echo $customer_name ?>"><?php echo substr($customer_name,0,30) ?></td>                
                  <td class="valign-middle" style="text-align: right;">
                    <?php if($invoice_no > 0): ?>
                      <a href="<?php echo '/sales/view-invoice/'.$invoice_code ?>" class="hyperlink"><?php echo $invoice_no ?></a>
                    <?php else: ?>
                      &nbsp;
                    <?php endif; ?>
                  </td>
                  <td class="valign-middle"><?php echo $campaign_name ?></td>
                  <td class="valign-middle"><?php echo $status ?></td>
                  <td class="valign-middle" align="right">
                    <div class="btn-actions-group">                    
                      <a class="btn btn-danger" href="/print-indent?indentNo=<?php echo $indent_no ?>" title="Print Sales Indent With Rate" target="_blank">
                        <i class="fa fa-print"></i>
                      </a>&nbsp;
                      <a class="btn btn-primary" href="/print-indent-wor?indentNo=<?php echo $indent_no ?>" title="Print Sales Indent without Rate" target="_blank">
                        <i class="fa fa-print"></i>
                      </a>&nbsp;
                      <a class="btn btn-info" href="/sales-indent/update-status/<?php echo $indent_code ?>" title="Approve / Reject / Cancel Indent">
                        <i class="fa fa-check"></i>
                      </a>
                      <?php if($indent_status === 1 && isset($_SESSION['utype']) && (int)$_SESSION['utype'] !== 15): ?>
                        <a class="btn btn-danger" href="/sales/entry-with-barcode?ic=<?php echo $indent_code ?>" title="Create Sales Order">
                          <i class="fa fa-inr"></i>
                        </a>
                        <a class="btn btn-warning" href="/sales/entry-with-indent/<?php echo $indent_code ?>" title="Create Sales Order with Scan">
                          <i class="fa fa-barcode"></i>
                        </a>
                      <?php elseif($indent_status === 0): ?>
                        <?php /*
                        <a class="btn btn-warning" href="/sales-indent/update/<?php echo $indent_code ?>" title="Update Indent Details">
                          <i class="fa fa-pencil"></i>
                        </a>&nbsp; */?>
                      <?php endif; ?>
                    </div>
                    <?php if((int)$indent_status === 2):  ?>
                      <p style="text-align: left; padding-top: 10px; font-weight: bold;"><?php echo $remarks2 ?></p>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php
                $cntr++;
                endforeach; 
              ?>
              <tr class="text-bold">
                <td colspan="3" align="right">TOTALS</td>
                <td align="right"><?php echo number_format($total, 2, '.', '') ?></td>
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
<script>
  (function() {
    setTimeout(function() {
      window.location.reload();
    }, 60000);    
  })();
</script>

            <?php /*
            <div class="col-sm-12 col-md-2 col-lg-2">
              <div class="select-wrap">
                <select class="form-control" name="campaignCode" id="campaignCode">
                  <?php 
                    foreach($campaigns as $campaign_code => $campaign_name):
                      if($campaignCode === $campaign_code) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }  
                  ?>
                   <option value="<?php echo $campaign_code ?>" <?php echo $selected ?>>
                      <?php echo $campaign_name ?>
                    </option>
                  <?php endforeach; ?>
                </select>
               </div>
            </div>*/?>