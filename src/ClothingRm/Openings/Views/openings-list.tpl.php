<?php
  use Atawa\Utilities;

  $query_params = [];
  if(isset($search_params['itemName']) && $search_params['itemName'] !== '') {
    $itemName = $search_params['itemName'];
    $query_params[] = 'itemName='.$itemName;
  } else {
    $itemName = '';
  }
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = '';
  }  
  if(count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  } else {
    $query_params = '';
  }

  $page_url = $pagination_url = '/opbal/list';
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/opbal/add" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> Add New Opening Balance
            </a> 
          </div>
        </div>
        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php endif; ?>
		    <div class="panel" style="margin-bottom:0px;">
          <div class="panel-body">
			     <div id="filters-form">
      			  <form class="form-validate form-horizontal" method="POST" action="<?php echo $page_url ?>">
        				<div class="form-group">
                  <div class="col-sm-12 col-md-2 col-lg-1">Filter by</div>
        				  <div class="col-sm-12 col-md-2 col-lg-2">
          					<input type="text" placeholder="Item name" name="itemName" id="itemName" class="form-control inameAc" value="<?php echo $itemName ?>">
        				  </div>
                  <div class="col-sm-12 col-md-2 col-lg-2">
                    <div class="select-wrap">
                      <select class="form-control" name="locationCode" id="locationCode">
                        <?php 
                          foreach($location_codes as $key=>$value):
                            if($locationCode === $key) {
                              $selected = 'selected="selected"';
                            } else {
                              $selected = '';
                            }
                        ?>
                          <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
        				  <div class="col-sm-12 col-md-2 col-lg-3">
          					<div class="col-sm-12"> 
          					  <button class="btn btn-success"><i class="fa fa-file-text"></i> Filter</button>
          					  <button type="reset" class="btn btn-warning" onclick="javascript:resetFilter('/opbal/list')"><i class="fa fa-refresh"></i> Reset </button>
          					</div>
        				  </div>
        				</div>
      			  </form>        
			     </div>
          </div>
        </div>
        <?php if(count($openings)>0): ?>
          <div class="table-responsive">
            <table class="table table-striped table-hover font12">
              <thead>
                <tr>
                  <th width="5%" class="text-center valign-middle">Sno.</th>
                  <th width="20%" class="text-center valign-middle">Item name</th>
                  <th width="10%" class="text-center valign-middle">Category</th>
                  <th width="10%" class="text-center valign-middle">Brand name</th>
                  <th width="10%" class="text-center valign-middle">Case/Box<br />No.</th>
                  <th width="10%" class="text-center valign-middle">Lot No.</th>                                                
                  <th width="5%" class="text-center valign-middle">Opening<br />qty.</th>
                  <th width="5%" class="text-center valign-middle">Packed/<br />qty.</th>
                  <?php /*<th width="5%" class="text-center valign-middle">Total qty.</th> */ ?>
                  <th width="8%" class="text-center valign-middle">Opening rate<br />(in Rs.)</th>
                  <th width="8%" class="text-center valign-middle">Opening value<br />(in Rs.)</th>
                  <th width="8%" class="text-center valign-middle">Purchase rate<br />(in Rs.)</th>                
                  <th width="5%" class="text-center valign-middle">Tax<br />(%)</th>
                  <th width="10%" class="text-center valign-middle">Options</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                  $cntr = $sl_no;
                  $tot_opening_stock_value = 0;
                  foreach($openings as $opening_details):
                    $item_name = $opening_details['itemName'];
                    $category_name = $opening_details['categoryName'];
                    $opening_rate = $opening_details['openingRate']; 
                    $opening_qty = $opening_details['openingQty'];
                    $purchase_rate = $opening_details['purchaseRate'];
                    $tax_percent = $opening_details['taxPercent'];
                    $opening_code = $opening_details['openingCode'];
                    $packed_qty = $opening_details['packedQty'];
                    $total_qty = round($opening_qty, 2);
                    $brand_name = $opening_details['mfgName'];
                    $container_no = $opening_details['cno'];
                    $lot_no = $opening_details['lotNo'];
                    $opening_value = round($opening_qty*$opening_rate,2);

                    $tot_opening_stock_value += round($opening_qty*$purchase_rate, 2);
                ?>
                    <tr class="text-right font11">
                      <td class="valign-middle"><?php echo $cntr ?></td>
                      <td class="text-left med-name valign-middle"><?php echo $item_name ?></td>
                      <td class="text-left med-name valign-middle"><?php echo $category_name ?></td>                    
                      <td class="text-left valign-middle"><?php echo $brand_name ?></td>                    
                      <td class="text-left valign-middle"><?php echo $container_no ?></td>                    
                      <td class="text-left valign-middle"><?php echo $lot_no ?></td>                    
                      <td class="valign-middle"><?php echo number_format($opening_qty,2,'.','') ?></td>
                      <td class="valign-middle"><?php echo number_format($packed_qty,2,'.','') ?></td>
                      <?php /*<td class="valign-middle"><?php echo number_format($total_qty,2,'.','') ?></td> */ ?>
                      <td class="valign-middle"><?php echo number_format($opening_rate,2,'.','') ?></td>
                      <td class="text-bold valign-middle"><?php echo number_format($opening_value,2,'.','') ?></td>
                      <td class="text-bold valign-middle"><?php echo number_format($purchase_rate,2,'.','') ?></td>                    
                      <td class="text-right valign-middle"><?php echo number_format($tax_percent,2,'.','') ?></td>
                      <td>
                        <?php if($opening_code !== ''): ?>
                          <div class="btn-actions-group">
                            <a class="btn btn-primary" href="/opbal/update/<?php echo $opening_code ?>" title="Edit Opening Balance">
                              <i class="fa fa-pencil"></i>
                            </a>    
                          </div>
                        <?php endif; ?>
                      </td>
                    </tr>
              <?php
                $cntr++;
                endforeach; 
              ?>
                <tr>
                  <td colspan="9" align="right">PAGE TOTALS (PURCHASE VALUE)</td>
                  <td align="right" style="font-weight:bold;font-size:14px;"><?php echo number_format($tot_opening_stock_value,2,'.','') ?></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>                                
                </tr>
              </tbody>
            </table>
            <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>
</div>