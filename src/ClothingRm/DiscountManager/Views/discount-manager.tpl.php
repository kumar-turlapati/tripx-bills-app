<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  
  // dump($search_params);
  // dump($location_ids);
  // exit;
  // dump($products);

  $query_params = '';
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = $default_location;
  }
  if(isset($search_params['mfg']) && $search_params['mfg'] !='') {
    $mfg = $search_params['mfg'];
    $query_params[] = 'mfg='.$mfg;
  } else {
    $mfg = '';
  }  
  if(isset($search_params['category']) && $search_params['category'] !='' ) {
    $category = $search_params['category'];
    $query_params[] = 'category='.$category;
  } else {
    $category = '';
  }
  if(isset($search_params['itemName']) && $search_params['itemName'] !='') {
    $itemName = $search_params['itemName'];
    $query_params[] = 'itemName='.$itemName;
  } else {
    $itemName = '';
  }  
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = $pagination_url = '/discount-manager';
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
  		  <div class="filters-block">
    		  <div id="filters-form">
            <form class="form-validate form-horizontal" action="<?php echo $page_url ?>" id="discountForm" method="POST">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1 labelStyle" style="text-align:right;padding-top:9px;">Filter by</div>
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
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="category" id="category">
                      <?php 
                        foreach($categories as $key=>$value):
                          if($key === $category) {
                            $selected = ' selected = "selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                         <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Brand" type="text" name="mfg" id="mfg" class="form-control brandAc" value="<?php echo $mfg ?>">
                </div>              
                <div class="col-sm-12 col-md-2 col-lg-2">
                   <input placeholder="Product name" type="text" name="itemName" id="itemName" class="form-control inameAc" value="<?php echo $itemName ?>">
                </div>                
                <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
            </div>
           </form>
			    </div>
        </div>
        <div class="table-responsive">
          <?php 
            if(count($products)>0):
              $is_editable = Utilities::is_admin() ? true: false;
          ?>
            <table class="table table-striped table-hover <?php echo $is_editable ? 'itemdiscounts' : '' ?>">
              <thead>
                <tr class="font12">
                  <th width="5%" class="text-center valign-middle">Sno</th>
                  <th width="40%" class="text-center valign-middle">Item name, Lot No. & M.R.P</th>                
                  <th width="10%" class="text-center valign-middle">Category name</th>
                  <th width="10%" class="text-center valign-middle">Brand</th>
                  <th width="8%" class="text-center valign-middle">M.R.P<br />(in Rs.)</th>                
                  <th width="8%" class="text-center valign-middle">Discount<br />(in %)</span></th>                
                  <th width="8%" class="text-center valign-middle">Discount<br />(in Rs.)</th>
                  <th width="12%" class="text-center valign-middle">End Date</th>
                  <?php if($is_editable): ?>
                    <th width="7%" class="text-center valign-middle">Options</th>
                  <?php endif; ?>
                </tr>
              </thead>
              <tbody>
                <?php 
                  $cntr = $sl_no;
                  foreach($products as $product_details):
                    $item_name = $product_details['itemName'];
                    $lot_no = $product_details['lotNo'];
                    $category_name = $product_details['categoryName'];
                    $brand = $product_details['brandName'];
                    $mrp = $product_details['mrp'];
                    $discount_percent = $product_details['discountPercent'];
                    $discount_amount = $product_details['discountAmount'];
                    $end_date = $product_details['endDate'];
                    if($end_date !== '0000-00-00' && $end_date !== '') {
                      $end_date = date("d-m-Y", strtotime($end_date));
                    } else {
                      $end_date = '';
                    }
                    $item_name_lot_no = $item_name.'____'.$lot_no.'____'.$mrp;
                ?>
                  <tr class="font11">
                    <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                    <td style="font-weight:bold;font-size:12px;color:#2E1114;" class="valign-middle"><?php echo $item_name_lot_no ?></td>                  
                    <td align="left" class="valign-middle"><?php echo $category_name ?></td>
                    <td align="left" class="valign-middle"><?php echo $brand ?></td>
                    <td align="right" class="valign-middle" id="mrp_<?php echo $lot_no ?>"><?php echo number_format($mrp,2,'.','') ?></td>
                    <td align="right" class="valign-middle" id="dp_<?php echo $lot_no ?>"><?php echo $discount_percent > 0 ? number_format($discount_percent,2,'.','') : '' ?></td>
                    <td align="right" class="valign-middle" id="da_<?php echo $lot_no ?>"><?php echo $discount_amount > 0 ? number_format($discount_amount,2,'.','') : '' ?></td>
                    <td align="right" class="valign-middle" style=""><?php echo $end_date ?></td>
                    <?php if($is_editable): ?>
                      <td align="center" class="valign-middle">
                        <?php if( Utilities::is_admin() && $discount_percent > 0): ?>
                          <div class="btn-actions-group" align="right">
                            <a class="btn btn-danger delDiscount" href="/discount-manager-delete?in=<?php echo $item_name ?>&lotNo=<?php echo $lot_no ?>&locationCode=<?php echo $locationCode ?>" title="Delete">
                              <i class="fa fa-times"></i>
                            </a>
                          </div>
                        <?php endif; ?>
                      </td>
                    <?php endif; ?>
                  </tr>
              <?php
                $cntr++;
                endforeach; 
              ?>
                <input type="hidden" name="locationCode" value="<?php echo $locationCode ?>" class="tabledit-input" />
              </tbody>
            </table>
            <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
          <?php else: ?>
            <div style="text-align:center;margin-top:10px;font-weight:bold;color:red;font-size:14px;">No data available. Please change search filters!</div>
          <?php endif; ?>
        </div> 
      </div>
    </section>
  </div>
</div>