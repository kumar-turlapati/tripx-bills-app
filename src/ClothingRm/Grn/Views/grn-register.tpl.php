<?php
  use Atawa\Utilities;

  if(isset($template_vars) && is_array($template_vars)) {
    extract($template_vars); 
  }
  $query_params = '';  
  if(isset($search_params['fromDate']) && $search_params['fromDate'] !='') {
    $fromDate = $search_params['fromDate'];
    $query_params[] = 'fromDate='.$fromDate;
  } else {
    $fromDate = '';
  }
  if(isset($search_params['toDate']) && $search_params['toDate'] !='' ) {
    $toDate = $search_params['toDate'];
    $query_params[] = 'toDate='.$toDate;
  } else {
    $toDate = '';
  }
  if(isset($search_params['supplierID']) && $search_params['supplierID'] !='' ) {
    $supplierID = $search_params['supplierID'];
    $query_params[] = 'supplierID='.$supplierID;
  } else {
    $supplierID = '';
  }  
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $current_date = date("d-m-Y");
  $page_url = '/grn/list';
?>

<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12">
    
    <!-- Panel starts -->
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>

        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php endif; ?>

        <!-- Right links starts -->
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/inward-entry/list" class="btn btn-default">
              <i class="fa fa-book"></i> Inward Register
            </a>
            <a href="/purchase-return/register" class="btn btn-default">
              <i class="fa fa-book"></i> Purchase Return Register
            </a>            
          </div>
        </div>
        <!-- Right links ends --> 

    		<div class="panel">
          <div class="panel-body">
        	<div id="filters-form">
            <form class="form-validate form-horizontal" method="POST">
        		  <div class="form-group">
                <div class="col-sm-12 col-md-2 col-lg-1">Filter by</div>  
                <div class="col-sm-12 col-md-2 col-lg-2">
        					<div class="form-group">
        					  <div class="col-lg-12">
        						<div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
        						  <input class="span2" size="16" type="text" readonly name="fromDate" id="fromDate" value="<?php echo $fromDate ?>" />
        						  <span class="add-on"><i class="fa fa-calendar"></i></span>
        						</div>
        					  </div>
        				  </div>
        				</div>

        				<div class="col-sm-12 col-md-2 col-lg-2">
        					<div class="form-group">
        					  <div class="col-lg-12">
        						<div class="input-append date" data-date="<?php echo $current_date ?>" data-date-format="dd-mm-yyyy">
        						  <input class="span2" size="16" type="text" readonly name="toDate" id="toDate" value="<?php echo $toDate ?>" />
        						  <span class="add-on"><i class="fa fa-calendar"></i></span>
        						</div>
        					  </div>
        					</div>
        				</div>

        				<div class="col-sm-12 col-md-2 col-lg-2">
        					<div class="select-wrap">
        						<select class="form-control" name="supplierID" id="supplierID">
        						  <?php foreach($suppliers as $key=>$value): ?>
        							<option value="<?php echo $key ?>"><?php echo $value ?></option>
        						  <?php endforeach; ?>
        						</select>
        					  </div>
        				  </div>                           
                  <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
        				</div>
        			</form>        
    			  <!-- Form ends -->
    			</div>
          </div>
        </div>
        
        <?php if(count($grns)>0): ?>        
          <h2 class="hdg-reports text-center">GRN Register</h2>
          <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th width="5%" class="text-center valign-middle">Sl.No.</th>
                    <th width="10%" class="text-center valign-middle">GRN No.</th>
                    <th width="10%" class="text-center valign-middle">GRN Date</th>
                    <th width="15%" class="text-center valign-middle">Supplier Name</span></th>
                    <th width="15%" class="text-center valign-middle">PO No.&amp;Date</th>
                    <th width="15%" class="text-center valign-middle">Bill No.</th>
                    <th width="10%" class="text-center valign-middle">Bill Amount</th>
                    <th width="10%" class="text-center valign-middle">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $cntr = $sl_no;
                    foreach($grns as $grn_details):
                      $grn_no = $grn_details['grnNo'];
                      $grn_code = $grn_details['grnCode'];
                      $grn_date = date("d-m-Y", strtotime($grn_details['grnDate']));
                      $supplier_name = $grn_details['supplierName'];
                      $po_info = $grn_details['poNo'].' / '.date("d-M-Y", strtotime($grn_details['purchaseDate']));
                      $bill_no = $grn_details['billNo'];
                      $bill_amount = $grn_details['netPay'];
                  ?>
                      <tr class="text-right font12">
                        <td class="valign-middle"><?php echo $cntr ?></td>
                        <td class="text-left valign-middle"><?php echo $grn_no ?></td>
                        <td class="valign-middle"><?php echo $grn_date ?></td>
                        <td class="text-bold text-left valign-middle"><?php echo $supplier_name ?></td>
                        <td class="text-left valign-middle"><?php echo $po_info ?></td>
                        <td class="text-right valign-middle"><?php echo $bill_no ?></td>
                        <td class="text-right valign-middle"><?php echo $bill_amount ?></td>
                        <td class="valign-middle">
                          <div class="btn-actions-group">
                            <?php if($grn_code !== ''): ?>
                              <a class="btn btn-primary" href="/grn/view/<?php echo $grn_code ?>" title="View GRN Transaction">
                                <i class="fa fa-eye"></i>
                              </a>
                              <a class="btn btn-primary" href="/print-grn/<?php echo $grn_code ?>" title="Print GRN" target="_blank">
                                <i class="fa fa-print"></i>
                              </a>                              
                              <?php /*                
                              <a class="btn btn-primary" href="/grn/update/<?php echo $grn_code ?>" title="Edit GRN Information">
                                <i class="fa fa-pencil"></i>
                              </a>
                              <a class="btn btn-danger delGrn" href="javascrip:void(0)" title="Remove GRN" sid="<?php echo $grn_code ?>">
                                <i class="fa fa-times"></i>
                              </a>*/ ?>                      
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
          </div>
        <?php endif; ?>        
      </div>
    </section>
    <!-- Panel ends -->
  </div>
</div>