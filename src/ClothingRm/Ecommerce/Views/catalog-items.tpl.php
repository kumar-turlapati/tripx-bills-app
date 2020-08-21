<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  
  $query_params = [];
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
  if(isset($search_params['listType']) && $search_params['listType'] !='') {
    $listType = $search_params['listType'];
    $query_params[] = 'listType='.$listType;
  } else {
    $listType = '';
  }   
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $page_url = $pagination_url = '/catalog/items/'.$catalog_code;
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/catalog/list" class="btn btn-default">
              <i class="fa fa-briefcase"></i> Catalogs
            </a>&nbsp;&nbsp;
            <a href="/catalog/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Catalog
            </a>            
          </div>
        </div>        
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
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="listType" id="listType">
                      <?php 
                        foreach($list_types as $key=>$value):
                          if($key === $listType) {
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
              </div>
              <div class="form-group" style="text-align: center;">
                <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
              </div>
           </form>
			    </div>
        </div>
        <div class="table-responsive">
          <?php 
            if(count($products)>0):
          ?>
            <table class="table table-striped table-hover">
              <thead>
                <tr class="font12">
                  <th width="5%" class="text-center valign-middle">Sno</th>
                  <th width="25%" class="text-center valign-middle">Item name</th>                
                  <th width="10%" class="text-center valign-middle">Category name</th>
                  <th width="10%" class="text-center valign-middle">Brand</th>
                  <th width="10%" class="text-center valign-middle">HSN/SAC</th>
                  <th width="10%" class="text-center valign-middle">Store</th>
                  <th width="7%" class="text-center valign-middle">&nbsp;</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                  $cntr = $sl_no;
                  foreach($products as $product_details):
                    $item_name = $product_details['itemName'];
                    $item_code = $product_details['itemCode'];
                    $category_name = $product_details['categoryName'];
                    $brand = $product_details['brandName'];
                    $hsn_sac_code = $product_details['hsnSacCode'];
                    $store_name = isset($location_ids[$product_details['locationID']]) ? $location_ids[$product_details['locationID']] : '';
                    $store_code = isset($location_codes[$product_details['locationID']]) ? $location_codes[$product_details['locationID']] : '';
                    $catalog_item_code = $product_details['catalogItemCode'];
                ?>
                  <tr class="font11">
                    <td align="right" class="valign-middle"><?php echo $cntr ?></td>
                    <td style="font-weight:bold;font-size:12px;color:#2E1114;" class="valign-middle"><?php echo $item_name ?></td>                  
                    <td align="left" class="valign-middle"><?php echo $category_name ?></td>
                    <td align="left" class="valign-middle"><?php echo $brand ?></td>
                    <td align="left" class="valign-middle"><?php echo $hsn_sac_code ?></td>
                    <td align="left" class="valign-middle"><?php echo $store_name ?></td>
                    <td align="center">
                      <div class="btn-actions-group">
                        <?php if($catalog_item_code !== ''): ?>
                          <a class="btn btn-danger removeItemFromCatalog" href="<?php echo $catalog_item_code ?>" title="Remove item from catalog">
                            <i class="fa fa-minus" aria-hidden="true"></i>
                          </a>
                        <?php else: ?>
                          <a class="btn btn-success addItemToCatalog" href="<?php echo $store_code.'/'.$catalog_code.'/'.$item_code ?>" title="Add item to catalog">
                            <i class="fa fa-plus" aria-hidden="true"></i>
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
          <?php else: ?>
            <div style="text-align:center;margin-top:10px;font-weight:bold;color:red;font-size:14px;">No data available. Please change search filters!</div>
          <?php endif; ?>
        </div> 
      </div>
    </section>
  </div>
</div>