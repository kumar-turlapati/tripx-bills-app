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
  if(isset($search_params['custName']) && $search_params['custName'] !== '' ) {
    $customer_name = $search_params['custName'];
    $query_params[] = 'custName='.$search_params['custName'];    
  } else {
    $customer_name = '';
  }
  if(isset($search_params['executiveCode']) && $search_params['executiveCode'] !== '' ) {
    $exe_code = $search_params['executiveCode'];
    $query_params[] = 'executiveCode='.$search_params['executiveCode'];    
  } else {
    $exe_code = '';
  }  
  if(isset($search_params['itemName']) && $search_params['itemName'] !== '') {
    $psName = $search_params['itemName'];
    $query_params[] = 'psName='.$psName;
  } else {
    $psName = '';
  }
  if(isset($search_params['brandName']) && $search_params['brandName'] !== '') {
    $brandName = $search_params['brandName'];
    $query_params[] = 'brandName='.$brandName;
  } else {
    $brandName = '';
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
  $page_url = $pagination_url = '/release-indent-items';

  $location_ids = [];

  // dump($client_locations);
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
            <a href="/indent-vs-sales" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> Indent Register
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
                  <div class="select-wrap">
                    <select class="form-control" name="locationCode" id="locationCode">
                      <?php 
                        foreach($client_locations as $location_key=>$value):
                          $location_key_a = explode('`', $location_key);
                          $location_ids[$location_key_a[1]] = $location_key_a[0];
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
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input 
                    placeholder="Brand" 
                    type="text" 
                    name="brandName" 
                    id="brandName" 
                    class="form-control brandAc" 
                    value="<?php echo $brandName ?>"
                    title="Choose Location to get brand names"
                  />
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Product Name" type="text" name="psName" id="psName" class="form-control" value="<?php echo $psName ?>">
                </div>
              </div>
              <div style="margin-top: 10px;" align="center">
                <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
              </div>          
            </form>
			    </div>
        </div>

        <div class="table-responsive">
          <?php if(count($unused_items)>0): ?>
            <form id="unusedItemsForm" method="POST">
              <table class="table table-striped table-hover">
                <thead>
                  <tr class="font12">
                    <th width="5%" class="text-center valign-middle">Sno.</th>
                    <th width="5%" class="text-center valign-middle">
                      <input 
                        type="checkbox"
                        id="checkAllOpBarcodes"
                        style="visibility:visible;text-align:center;margin:0px;position:relative;vertical-align:middle;margin-top:10px;"
                        title="Select all items in this page"
                      />
                    </th>
                    <th width="20%" class="text-center valign-middle">Item name</th>
                    <th width="15%" class="text-center valign-middle">Brand name</th>
                    <th width="7%" class="text-center valign-middle">Lot no.</th>
                    <th width="10%" class="text-center valign-middle">Indent no.</span></th>
                    <th width="10%" class="text-center valign-middle">Indent date</span></th>
                    <th width="10%" class="text-center valign-middle">Indent qty.</span></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $cntr = $sl_no;
                    $tot_indent_qty = 0;
                    foreach($unused_items as $indent_details):
                      $item_name = $indent_details['itemName'];
                      $item_code = $indent_details['itemCode'];
                      $brand_name = $indent_details['mfgName'];
                      $indent_no = $indent_details['sindentNo'];
                      $lot_no = $indent_details['lotNo'];
                      $uom_name = $indent_details['uomName'];
                      $indent_date = date("d-m-Y", strtotime($indent_details['sindentDate']));
                      $indent_qty = $indent_details['itemQty'];
                      $indent_item_id = $indent_details['indentItemID'];
                      $location_id = $indent_details['locationID'];
                      $location_code = isset($location_ids[$location_id]) ? $location_ids[$location_id] : '';
                      $item_key = $indent_item_id;
                      $tot_indent_qty += $indent_qty;
                  ?>
                    <tr class="font12">
                      <td align="right"  class="valign-middle"><?php echo $cntr ?></td>
                      <td class="valign-middle" align="center">
                        <input
                          type="checkbox"
                          id="requestedItem_<?php echo $indent_item_id ?>"
                          name="requestedItems[]"
                          value="<?php echo $indent_item_id ?>"
                          style="visibility:visible;text-align:center;margin:0px;position:relative;vertical-align:middle;margin-top:0px;"
                          class="requestedItem"
                        />
                      </td>
                      <td align="left"   class="valign-middle text-bold" title="<?php echo $item_name ?>">
                        <?php if($location_code !== ''): ?>
                          <a class="hyperlink" href="/products/update/<?php echo $item_code ?>?lc=<?php echo $location_code ?>" target="_blank">
                            <i class="fa fa-external-link" aria-hidden="true"></i>&nbsp;<?php echo $item_name ?>
                          </a>
                        <?php else: ?>
                          <?php echo 'Invalid Item' ?>
                        <?php endif; ?>
                      </td>
                      <td align="left"   class="valign-middle" title="<?php echo $brand_name ?>"><?php echo $brand_name ?></td>                
                      <td align="left"   class="valign-middle"><?php echo $lot_no ?></td>
                      <td align="center" class="valign-middle">
                        <a class="hyperlink" href="/print-indent?indentNo=<?php echo $indent_no ?>" target="_blank">
                          <i class="fa fa-external-link" aria-hidden="true"></i>&nbsp;<?php echo $indent_no ?>
                        </a>
                      </td>
                      <td align="center" class="valign-middle"><?php echo $indent_date ?></td>
                      <td align="right"  class="valign-middle" style="font-size: 14px; color: red; font-weight: bold;"><?php echo number_format($indent_qty,2,'.','').' '.strtolower($uom_name) ?></td>
                    </tr>
                  <?php
                    $cntr++;
                    endforeach; 
                  ?>
                  <tr class="text-bold" style="font-size: 14px;">
                    <td colspan="7" align="right">PAGE TOTALS</td>
                    <td align="right" style="font-weight: bold; font-size: 16px; color: red;"><?php echo number_format($tot_indent_qty, 2, '.', '') ?></td>
                  </tr>
                </tbody>
              </table><br />
              <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
              <div class="text-center">
                <button class="btn btn-danger" id="op" name="op" value="deleteItems">
                  <i class="fa fa-times"></i> Delete selected items
                </button>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </div>
</div>