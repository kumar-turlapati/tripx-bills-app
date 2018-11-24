<?php
  use Atawa\Utilities;

  $query_params = '';  
  if(isset($search_params['medName']) && $search_params['medName'] !== '') {
    $medName = $search_params['medName'];
    $query_params[] = 'medName='.$medName;
  } else {
    $medName = '';
  }
  if(isset($search_params['brandName']) && $search_params['brandName'] !== '') {
    $brandName = $search_params['brandName'];
    $query_params[] = 'brandName='.$brandName;
  } else {
    $brandName = '';
  }
  if(isset($search_params['category']) && $search_params['category'] !== '') {
    $categoryCode = $search_params['category'];
    $query_params[] = 'category='.$categoryCode;
  } else {
    $brandName = '';
  }
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '') {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = $default_location;
  }
  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }
  $pagination_url = $page_url = '/inventory/available-qty';
  // dump($items);
  // exit;
?>

<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="filters-block">
          <div id="filters-form">
            <form class="form-validate form-horizontal" method="POST" action="<?php echo $page_url ?>">
              <div class="form-group">
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <div class="select-wrap">
                    <select class="form-control" name="locationCode" id="locationCode">
                      <?php 
                        foreach($client_locations as $location_key => $value):
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
                  <?php if(isset($form_errors['locationCode'])): ?>
                    <span class="error"><?php echo $form_errors['locationCode'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <input placeholder="Item name" type="text" name="medName" id="medName" class="form-control inameAc" value="<?php echo $medName ?>">
                  <?php if(isset($form_errors['itemName'])): ?>
                    <span class="error"><?php echo $form_errors['itemName'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="category" id="category">
                      <?php 
                        foreach($categories as $category_key => $category_name):
                          if($categoryCode === $category_key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }  
                      ?>
                       <option value="<?php echo $category_key ?>" <?php echo $selected ?>>
                          <?php echo $category_name ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                   </div>
                </div>                
                <div class="col-sm-12 col-md-1 col-lg-1">
                  <input placeholder="Brand" type="text" name="brandName" id="brandName" class="form-control brandAc" value="<?php echo $brandName ?>">
                  <?php if(isset($form_errors['brandName'])): ?>
                    <span class="error"><?php echo $form_errors['brandName'] ?></span>
                  <?php endif; ?>
                </div>           
                <div class="col-sm-12 col-md-3 col-lg-3">
                  <div class="container-fluid">
                    <button class="btn btn-success">
                      <i class="fa fa-file-text"></i> Filter
                    </button>
                    <button type="reset" class="btn btn-warning" onclick="javascript:resetFilter(<?php echo (isset($page_url) && $page_url != '' ? "'".$page_url."'" : '#') ?>)">
                      <i class="fa fa-refresh"></i> Reset
                    </button>
                  </div>
                </div>
              </div>
            </form>        
          </div>
        </div>
        <?php if(count($items) > 0): ?>
          <div class="table-responsive">
            <table class="table table-striped table-hover font12">
              <thead>
                <tr>
                  <th width="5%" class="text-center">Sno</th>
                  <th width="25%" class="text-left">Item name</th>
                  <th width="15%" class="text-left">Category name</th>
                  <th width="15%" class="text-left">Brand name</th>
                  <th width="15%" class="text-left">Store name</th>
                  <th width="8%" class="text-center">M.R.P<br />( in Rs. )</th>
                  <th width="8%" class="text-center">Available<br />Qty.</th>
                  <th width="10%" class="text-center">Value<br />( in Rs. )</th>                
                </tr>
              </thead>
              <tbody>
                <?php 
                  $cntr = $sl_no;
                  $total_value = $total_qty = 0;
                  foreach($items as $item_details):
                    $item_name = $item_details['itemName'];
                    $category_name = $item_details['categoryName'];
                    $brand_name = $item_details['brandName'];
                    $item_code = $item_details['itemCode'];
                    $ava_qty = $item_details['closingQty'];
                    $item_rate = $item_details['mrp'];
                    $item_value = $ava_qty * $item_rate;
                    $location_id = $item_details['locationID'];
                    $location_name = isset($location_ids[$location_id]) ?  $location_ids[$location_id] : 'Invalid';

                    $total_value += $item_value;
                    $total_qty += $ava_qty;
                ?>
                  <tr class="text-right font11">
                    <td><?php echo $cntr ?></td>
                    <td class="text-left">
                      <?php echo $item_name ?>
                    </td>
                    <td class="text-left"><?php echo $category_name ?></td>                 
                    <td class="text-left"><?php echo $brand_name ?></td>                 
                    <td class="text-left"><?php echo $location_name ?></td>
                    <td class="text-right"><?php echo number_format($item_rate,2,'.','') ?></td>
                    <td class="text-right"><?php echo number_format($ava_qty,2,'.','') ?></td>
                    <td class="text-right"><?php echo number_format($item_value,2,'.','') ?></td>                    
                  </tr>
              <?php
                $cntr++;
                endforeach;
              ?>
                <tr>
                  <td colspan="6" style="font-size:14px;font-weight:bold;text-align:right;">PAGE TOTALS</td>
                  <td style="font-size:16px;font-weight:bold;text-align:right;"><?php echo number_format($total_qty, 2, '.', '') ?></td>
                  <td style="font-size:16px;font-weight:bold;text-align:right;"><?php echo number_format($total_value, 2, '.', '') ?></td>
                </tr>
              </tbody>
            </table>
            <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>
          </div>
        <?php else: ?>
          <div style="text-align:center;margin-top:10px;font-weight:bold;color:red;font-size:14px;">No data available. Please change search filters!</div>
        <?php endif; ?>
      </div>
    </section>
  </div>
</div>