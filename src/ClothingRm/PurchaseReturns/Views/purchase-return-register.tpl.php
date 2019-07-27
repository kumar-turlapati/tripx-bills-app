<?php
  use Atawa\Utilities;

  $query_params = [];
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
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&',$query_params);
  }
  $pagination_url = $page_url = '/purchase-return/register';
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/inward-entry/list" class="btn btn-default">
              <i class="fa fa-book"></i> Purchase Register 
            </a>            
            <a href="/inward-entry" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Purchase Entry 
            </a>
            <a href="/grn/list" class="btn btn-default">
              <i class="fa fa-book"></i> GRN Register
            </a>            
          </div>
        </div>
  		  <div class="panel">
  		    <div class="panel-body">
  			     <div id="filters-form">
  			       <form class="form-validate form-horizontal" method="POST" action="<?php echo $pagination_url ?>">
  				        <div class="form-group">
                    <div class="col-sm-12 col-md-2 col-lg-1">Filter by</div>
          				  <div class="col-sm-12 col-md-2 col-lg-2">
            					<div class="form-group">
            					  <div class="col-lg-12">
            						<div class="input-append date" data-date="<?php echo $fromDate ?>" data-date-format="dd-mm-yyyy">
            						  <input class="span2" size="16" type="text" readonly name="fromDate" id="fromDate" value="<?php echo $fromDate ?>" />
            						  <span class="add-on"><i class="fa fa-calendar"></i></span>
            						</div>
            					  </div>
            					</div>
          				  </div>
  				          <div class="col-sm-12 col-md-2 col-lg-2">
            					<div class="form-group">
            					  <div class="col-lg-12">
            						<div class="input-append date" data-date="<?php echo $toDate ?>" data-date-format="dd-mm-yyyy">
            						  <input class="span2" size="16" type="text" readonly name="toDate" id="toDate" value="<?php echo $toDate ?>" />
            						  <span class="add-on"><i class="fa fa-calendar"></i></span>
            						</div>
            					  </div>
            					</div>
  				          </div>
  				          <div class="col-sm-12 col-md-2 col-lg-2">
            					<div class="select-wrap">
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
            				  </div>                           
                      <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
  				          </div>
  			       </form>        
  			     </div>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font11">
            <thead>
              <tr>
                <th width="5%"  class="text-center">Sno.</th>
                <th width="30%" class="text-center">Supplier name</th>
                <th width="8%"  class="text-center">Return No.</span></th>
                <th width="8%"  class="text-center">Return Date</th>
                <th width="10%" class="text-center">Return Amount</th>
                <th width="12%" class="text-center">GRN No. / Date</th>
                <th width="12%" class="text-center">PO No. / Date</th>
                <th width="10%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $cntr = $sl_no;
                $total_amount = 0;
                foreach($returns as $return_details):
                  $supplier_name = $return_details['supplierName'];
                  $po_no = $return_details['poNo'];
                  $po_date = date("d-m-Y", strtotime($return_details['purchaseDate']));
                  $grn_no = $return_details['grnNo'];
                  $grn_date = date("d-m-Y", strtotime($return_details['grnDate']));
                  $grn_info = $grn_no.' / '.$grn_date;
                  $po_info = $po_no.' / '.$po_date;
                  $return_no = $return_details['mrnNo'];
                  $return_date = date("d-m-Y", strtotime($return_details['returnDate']));
                  $return_amount = $return_details['netpay'];
                  $total_amount += $return_amount;

                  $purchase_code = $return_details['purchaseCode'];
                  $return_code = $return_details['returnCode'];
                  $grn_code = $return_details['grnCode'];
              ?>
                  <tr class="text-uppercase text-right font11">
                    <td class="valign-middle"><?php echo $cntr ?></td>
                    <td class="text-left med-name valign-middle"><?php echo $supplier_name ?></td>
                    <td class="text-bold text-right valign-middle"><?php echo $return_no ?></td>
                    <td class="text-right valign-middle"><?php echo $return_date ?></td>
                    <td class="text-right valign-middle" style="font-size:14px;font-weight:bold;"><?php echo number_format($return_amount, 2, '.', '') ?></td>                    
                    <td class="text-right valign-middle">
                      <a href="/grn/view/<?php echo $grn_code ?>" class="hyperlink" target="_blank"><?php echo $grn_info ?></a>
                    </td>
                    <td class="text-right valign-middle">
                      <a href="/inward-entry/view/<?php echo $purchase_code ?>" class="hyperlink" title="View Purchase Order" target="_blank"><?php echo $po_info ?></a>
                    </td>                    
                    <td class="valign-middle">
                      <div class="btn-actions-group">
                        <a class="btn btn-primary" href="/purchase-return/view/<?php echo $return_code ?>" title="View return entry">
                          <i class="fa fa-eye"></i>
                        </a>                        
                        <a class="btn btn-danger" href="/purchase-return/delete/<?php echo $return_code ?>" title="Delete return entry">
                          <i class="fa fa-times"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
            <?php
              $cntr++;
              endforeach;
            ?>
              <tr class="font12">
                <td colspan="4">&nbsp;</td>
                <td class="text-right text-bold" style="font-size:16px;font-weight:bold;"><?php echo number_format($total_amount,2,'.','') ?></td>
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