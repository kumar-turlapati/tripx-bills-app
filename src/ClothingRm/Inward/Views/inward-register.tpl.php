<?php
  use Atawa\Utilities;

  $query_params = '';
  $current_date = date("d-m-Y");
  if(isset($search_params['fromDate']) && $search_params['fromDate'] !='') {
    $fromDate = $search_params['fromDate'];
    $query_params[] = 'fromDate='.$fromDate;
  } else {
    $fromDate = '01-'.date('m').'-'.date("Y");
  }
  if(isset($search_params['toDate']) && $search_params['toDate'] !='' ) {
    $toDate = $search_params['toDate'];
    $query_params[] = 'toDate='.$toDate;
  } else {
    $toDate = $current_date;
  }
  if(isset($search_params['supplierID']) && $search_params['supplierID'] !='' ) {
    $supplierID = $search_params['supplierID'];
    $query_params[] = 'supplierID='.$supplierID;
  } else {
    $supplierID = '';
  }
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;    
  } else {
    $locationCode = '';
  }  
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&',$query_params);
  }
  $pagination_url = $page_url = '/inward-entry/list';
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
            <a href="/inward-entry" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Purchase Entry 
            </a>
            <a href="/grn/list" class="btn btn-default">
              <i class="fa fa-book"></i> GRN Register
            </a>
            <a href="/purchase-return/register" class="btn btn-default">
              <i class="fa fa-book"></i> Purchase Return Register
            </a>            
          </div>
        </div>
        <div class="filters-block">
  	       <div id="filters-form">
  	         <form class="form-validate form-horizontal" method="POST" action="<?php echo $pagination_url ?>">
      				<div class="form-group">
                <div class="col-sm-12 col-md-2 col-lg-1">Filter by</div>
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
      						<select class="form-control" name="supplierID" id="supplierID">
      						  <?php
                      foreach($suppliers as $key=>$value): 
                        if($key===$supplierID) {
                          $selected = 'selected';
                        } else {
                          $selected = '';
                        }
                    ?>
      							<option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
      						  <?php endforeach; ?>
      						</select>
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
                <th width="5%" class="text-center">Sno.</th>
                <th width="25%" class="text-center">Supplier Name</th>
                <th width="10%" class="text-center">PO No.</span></th>
                <th width="8%" class="text-center">PO Date</th>
                <th width="10%" class="text-center">Amount</th>
                <th width="13%" class="text-center">GRN No. / Date</th>
                <th width="8%" class="text-center">Status</th>                
                <th width="20%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $cntr = $sl_no;
                $total_amount = 0;
                foreach($purchases as $purchase_details):
                  $purchase_code = $purchase_details['purchaseCode'];
                  $dop = date("d-m-Y",strtotime($purchase_details['purchaseDate']));
                  if((int)$purchase_details['status'] === 0) {
                    $status_text = '<span style="color:brown;font-weight:bold;font-size:12px;">Pending</span>';
                  } elseif((int)$purchase_details['status'] === 1) {
                    $status_text = '<span style="color:green;font-weight:bold;font-size:12px;">Approved</span>';
                  } elseif((int)$purchase_details['status'] === 2) {
                    $status_text = '<span style="color:red;font-weight:bold;font-size:12px;">Rejected</span>';
                  } else {
                    $status_text = 'Invalid';
                  }
                  $po_amount = $purchase_details['netPay'];
                  $total_amount += $po_amount;
                  if($purchase_details['grnNo'] !== '' && $purchase_details['grnDate'] !== '') {
                    $grn_info = true;
                    $grn_text = $purchase_details['grnNo'].' / '.date("d-m-Y",strtotime($purchase_details['grnDate']));
                  } else {
                    $grn_info = false;
                    $grn_text = 'Not Generated';
                  }
              ?>
                  <tr class="text-uppercase text-right font11">
                    <td class="valign-middle"><?php echo $cntr ?></td>
                    <td class="text-left med-name valign-middle"><?php echo $purchase_details['supplierName'] ?></td>
                    <td class="text-bold text-left valign-middle"><?php echo $purchase_details['poNo'] ?></td>
                    <td class="text-right valign-middle"><?php echo $dop ?></td>
                    <td class="text-right text-bold valign-middle"><?php echo number_format($po_amount,2,'.','') ?></td>
                    <td class="text-right valign-middle"><?php echo $grn_text ?></td>
                    <td class="text-right text-bold valign-middle"><?php echo $status_text ?></td>                    
                    <td class="valign-middle">
                      <div class="btn-actions-group">
                        
                        <?php if((int)$purchase_details['status'] === 0 || Utilities::is_admin()): ?>
                          <a class="btn btn-primary" href="/inward-entry/update/<?php echo $purchase_code ?>" title="Edit this purchase order">
                            <i class="fa fa-pencil"></i>
                          </a>
                        <?php endif; ?>

                        <?php if($grn_info === false): ?>
                          <?php if((int)$purchase_details['status'] === 1) : ?>                      
                            <a class="btn btn-warning" href="/grn/create?poNo=<?php echo $purchase_details['poNo'] ?>&poCode=<?php echo $purchase_code ?>" title="Create GRN for this PO">
                              <i class="fa fa-list-ol"></i>
                            </a>
                          <?php endif; ?>
                          <?php if((int)$purchase_details['status'] === 0): ?>
                            <a class="btn btn-danger" href="/inward-entry/update-status/<?php echo $purchase_code ?>" title="Approve / Reject PO">
                              <i class="fa fa-check"></i>
                            </a>
                          <?php endif; ?>
                        <?php else: ?>
                          <a class="btn btn-danger" href="/barcode/generate/<?php echo $purchase_code ?>" title="Generate Barcodes">
                            <i class="fa fa-bars"></i>
                          </a>                        
                          <a class="btn btn-primary" href="/inward-entry/view/<?php echo $purchase_code ?>" title="View purchase order">
                            <i class="fa fa-eye"></i>
                          </a>
                          <a class="btn btn-warning" href="/purchase-return/entry?pc=<?php echo $purchase_code ?>" title="Purchase Return and Auto Debit Note">
                            <i class="fa fa-undo"></i>
                          </a>
                          <a class="btn btn-success" href="/fin/debit-note/create?pc=<?php echo $purchase_code ?>" title="Raise a Debit Note">
                            <i class="fa fa-minus-square"></i>
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
                <td colspan="4">&nbsp;</td>
                <td class="text-right text-bold"><?php echo number_format($total_amount,2,'.','') ?></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>                
              </tr>
            </tbody>
          </table>
          <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
        </div>
      </div>
    </section>
  </div>
</div>