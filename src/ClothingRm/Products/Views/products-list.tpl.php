<?php 

  $pagination_url = $page_url = '/products/list';

  $query_params = [];  
  if(isset($search_params['medname']) && $search_params['medname'] !='') {
    $medname = $search_params['medname'];
    $query_params[] = 'psName='.$medname;
  } else {
    $medname = '';
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
  if(isset($search_params['bno']) && $search_params['bno'] !== '' ) {
    $bno = $search_params['bno'];
    $query_params[] = 'bno='.$bno;
  } else {
    $bno = '';
  }
  if(isset($search_params['cno']) && $search_params['cno'] !== '' ) {
    $cno = $search_params['cno'];
    $query_params[] = 'cno='.$cno;
  } else {
    $cno = '';
  }
  if(isset($search_params['itemSku']) && $search_params['itemSku'] !== '' ) {
    $item_sku = $search_params['itemSku'];
    $query_params[] = 'itemSku='.$itemSku;
  } else {
    $item_sku = '';
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
              <a href="/products/create" class="btn btn-default">
                <i class="fa fa-file-text-o"></i> New Product / Service
              </a>
          </div>
        </div>
    		<div class="filters-block">
    			<div id="filters-form">
    			 <form class="form-validate form-horizontal" method="POST" action="<?php echo $page_url ?>" id="productsList">
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
      					   <input placeholder="Product name" type="text" name="psName" id="psName" class="form-control inameAc" value="<?php echo $medname ?>">
      				  </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Batch No." type="text" name="bno" id="bno" class="form-control" value="<?php echo $bno ?>">
                </div>              
      				</div>
              <div class="form-group" style="margin-left: 77px;">
                <div class="col-sm-12 col-md-3 col-lg-3">
                   <input placeholder="Container/Case/Box No." type="text" name="cno" id="cno" class="form-control" value="<?php echo $cno ?>">
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                   <input placeholder="Item SKU" type="text" name="itemSku" id="itemSku" class="form-control" value="<?php echo $item_sku ?>">
                </div>                               
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
                </div>
              </div>
    			  </form>        
    			</div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover plRackNumbers">
            <thead>
              <tr class="font12">
                <th width="5%" class="text-center">Sno.</th>
                <th width="8%" class="text-left">Item Code</th>
                <th width="20%" class="text-left">Product / Service Name</th>
                <th width="8%" class="text-center">Rack Number</th>
                <th width="10%" class="text-center">Brand/Mfg. Name</th>               
                <th width="10%" class="text-center">Category</th>               
                <?php /*<th width="5%" class="text-center">Threshold<span class="brk">Qty.</span></th> */?>
                <th width="5%" class="text-center">Units of /<span class="brk">Measurement</span></th>
                <?php /*<th width="5%" class="text-center">M.R.P <span class="brk">(in Rs.)</span></th> */?>
                <th width="8%" class="text-center">HSN/SAC<span class="brk">Code</span></th>
                <?php /*<th width="8%" class="text-center">SKU</th>*/?>
              </tr>
            </thead>
            <tbody>
              <?php 
                $cntr = $sl_no;
                foreach($products as $product_details):
                  if($product_details['mrp']>0) {
                    $mrp = $product_details['mrp'];
                  } else {
                    $mrp = '';
                  }
                  if($product_details['brandName'] !== '') {
                    $brandName = $product_details['brandName'];
                  } else {
                    $brandName = '';
                  }
                  if($product_details['unitsPerPack'] !== '') {
                    $units_per_pack = $product_details['unitsPerPack'];
                  } else {
                    $units_per_pack = '';
                  }
                  if($product_details['hsnSacCode'] !== '') {
                    $hsnSacCode = $product_details['hsnSacCode'];
                  } else {
                    $hsnSacCode = '';
                  }
                  if($product_details['rackNo'] !== '') {
                    $rack_no = $product_details['rackNo'];
                  } else {
                    $rack_no = '';
                  }
                  if($product_details['uomName'] !== '') {
                    $uom_name = $product_details['uomName'];
                  } else {
                    $uom_name = '';
                  }                  
                  $thr_qty = $product_details['thrQty'];
                  $item_code = $product_details['itemCode'];
                  $item_sku = $product_details['itemSku'];
                  $location_id = $product_details['locationID'];
                  $category_name = $product_details['categoryName'];
                  $item_location_code = isset($location_codes[$location_id]) ? $location_codes[$location_id] : '';
              ?>
                  <tr class="text-uppercase text-right font11">
                    <td class="text-center valign-middle"><?php echo $cntr ?></td>
                    <td class="text-left valign-middle"><?php echo $item_code ?></td>
                    <td class="text-left med-name valign-middle">
                      <a 
                        href="/products/update/<?php echo $item_code ?>?lc=<?php echo $item_location_code ?>"
                        class="hyperlink"
                        title="click here to update"
                      >
                        <?php echo $product_details['itemName'] ?>
                      </a>
                    </td>
                    <td class="text-right plRackNumber valign-middle"><?php echo $rack_no ?></td>
                    <td class="text-left valign-middle"><?php echo substr($brandName,0,20) ?></td>
                    <td class="text-left valign-middle"><?php echo substr($category_name,0,20) ?></td>
                    <?php /*<td class="text-right"><?php echo $thr_qty ?></td>*/ ?>
                    <td class="text-right valign-middle"><?php echo $uom_name ?></td>
                    <?php /*<td class="text-bold"><?php echo $mrp ?></td>*/ ?>
                    <td class="text-right valign-middle"><?php echo $hsnSacCode ?></td>
                    <?php /*<td class="text-right"><?php echo $item_sku ?></td>*/ ?>                    
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