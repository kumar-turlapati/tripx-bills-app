<?php 

  $pagination_url = $page_url = '/galleries/list';

  $query_params = [];  
  if(isset($search_params['itemName']) && $search_params['itemName'] !='') {
    $itemName = $search_params['itemName'];
    $query_params[] = 'itemName='.$itemName;
  } else {
    $itemName = '';
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
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '' ) {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = $default_location;
  }  
  if($query_params != '') {
    $query_params = '?'.implode('&', $query_params);
  }
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $flash_obj->print_flash_message(); ?>
        <?php if($page_error !== ''): ?>
          <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> <?php echo $page_error ?> 
          </div>
        <?php endif; ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
              <a href="/gallery/create" class="btn btn-default">
                <i class="fa fa-file-text-o"></i> New Gallery
              </a>
          </div>
        </div>
    		<div class="filters-block">
    			<div id="filters-form">
    			 <form class="form-validate form-horizontal" method="POST" action="<?php echo $page_url ?>">
    				<div class="form-group">
              <div class="col-sm-12 col-md-1 col-lg-1 text-right">
    					  <label class="control-label text-right"><b>Filter by</b></label>          
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
              <div class="col-sm-12 col-md-3 col-lg-3">
                <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
              </div>
    				</div>
    			  </form>        
    			</div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover" id="itemnames">
            <thead>
              <tr class="font12">
                <th width="5%" class="text-center">Sno.</th>
                <th width="20%" class="text-left">Product name</th>
                <th width="10%" class="text-center">Brand name</th>               
                <th width="10%" class="text-center">Category</th>               
                <th width="10%" class="text-center">Style code</th>               
                <th width="10%" class="text-center">Color</th>               
                <th width="20%" class="text-center">Description</th>               
                <th width="8%" class="text-center">Images<br />Uploaded</th>                                                
              </tr>
            </thead>
            <tbody>
              <?php 
                $cntr = $sl_no;
                foreach($products as $product_details):
                  $item_name = $product_details['itemName'];
                  $brand_name = $product_details['brandName'] !== '' ? $product_details['brandName'] : '';
                  $category_name = $product_details['categoryName'];
                  $style_code = $product_details['itemStylecode'];
                  $item_color = $product_details['itemColor'];
                  $item_description = $product_details['itemDescription'];
                  $item_code = $product_details['itemCode'];
                  $location_id = $product_details['locationID'];
                  $gallery_code = $product_details['galleryCode'];
                  $item_location_code = isset($location_codes[$location_id]) ? $location_codes[$location_id] : '';
                  $no_of_images = $product_details['totalImages'];
              ?>
                  <tr class="text-uppercase text-right font11">
                    <td class="text-right valign-middle"><?php echo $cntr ?></td>
                    <td class="text-left valign-middle">
                      <a 
                        href="/gallery/update/<?php echo $item_location_code ?>/<?php echo $gallery_code ?>"
                        class="hyperlink"
                        title="click here to update the Gallery"
                      >
                        <?php echo $item_name ?>
                      </a>
                    </td>
                    <td class="text-left valign-middle" title="<?php echo $brand_name ?>"><?php echo substr($brand_name,0,20) ?></td>
                    <td class="text-left valign-middle" title="<?php echo $category_name ?>"><?php echo substr($category_name,0,20) ?></td>
                    <td class="text-left valign-middle"><?php echo $style_code ?></td>                
                    <td class="text-left valign-middle"><?php echo $item_color ?></td>                
                    <td class="text-left valign-middle"><?php echo $item_description ?></td>                
                    <td class="text-right valign-middle"><?php echo $no_of_images ?></td>                
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