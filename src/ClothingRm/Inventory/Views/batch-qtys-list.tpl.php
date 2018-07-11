<?php
  use Atawa\Utilities;
  
  $query_params = '';  
  if(isset($search_params['medName']) && $search_params['medName'] !='') {
    $medName = $search_params['medName'];
    $query_params[] = 'medName='.$medName;
  } else {
    $medName = '';
  }

  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '') {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = '';
  }

  if(is_array($query_params) && count($query_params)>0) {
    $query_params = '?'.implode('&', $query_params);
  }

  $pagination_url = '/inventory/available-qty';  
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

        <div class="panel">
          <div class="panel-body">
          <div id="filters-form">
            <!-- Form starts -->
            <form class="form-validate form-horizontal" method="POST">
              <div class="form-group">
                <div class="col-sm-12 col-md-2 col-lg-1">Filter by</div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Product name" type="text" name="medName" id="medName" class="form-control" value="<?php echo $medName ?>">
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
                <div class="col-sm-12 col-md-3 col-lg-3">
                    <button class="btn btn-success"><i class="fa fa-file-text"></i> Filter</button>
                    <button type="reset" class="btn btn-warning" onclick="javascript:resetFilter('/inventory/available-qty')"><i class="fa fa-refresh"></i> Reset </button>
                </div>
              </div>
            </form>        
            <!-- Form ends -->
          </div>
        </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font12">
            <thead>
              <tr>
                <th width="5%" class="text-center">Sno</th>
                <th width="25%" class="text-left">Item name</th>
                <th width="10%" class="text-left">Category name</th>
                <th width="10%" class="text-left">Store name</th>
                <th width="10%" class="text-center">Available<br />Qty.</th>
                <th width="10%" class="text-center">M.R.P<br />( in Rs. )</th>
                <th width="10%" class="text-center">Value<br />( in Rs. )</th>                
              </tr>
            </thead>
            <tbody>
              <?php 
                $cntr = $sl_no;
                $total_value = 0;
                foreach($items as $item_details):
                  $item_name = $item_details['itemName'];
                  $category_name = $item_details['categoryName'];
                  $item_code = $item_details['itemCode'];
                  $ava_qty = $item_details['closingQty'];
                  $item_rate = $item_details['mrp'];
                  $item_value = $ava_qty * $item_rate;
                  $location_id = $item_details['locationID'];
                  $location_name = isset($location_ids[$location_id]) ?  $location_ids[$location_id] : 'Invalid';
                  $total_value += $item_value;
              ?>
                  <tr class="text-right font12">
                    <td><?php echo $cntr ?></td>
                    <td class="text-left"><?php echo $item_name ?></td>
                    <td class="text-left"><?php echo $category_name ?></td>                    
                    <td class="text-left"><?php echo $location_name ?></td>
                    <td class="text-right"><?php echo number_format($ava_qty,2) ?></td>
                    <td class="text-right"><?php echo number_format($item_rate,2) ?></td>
                    <td class="text-right"><?php echo number_format($item_value,2) ?></td>                    
                  </tr>
            <?php
              $cntr++;
              endforeach; 
            ?>
              <tr>
                <td colspan="6" style="font-size:14px;font-weight:bold;text-align:right;">TOTALS</td>
                <td style="font-size:14px;font-weight:bold;text-align:right;"><?php echo number_format($total_value, 2) ?></td>
              </tr>
            </tbody>
          </table>

          <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>

        </div>
      </div>
    </section>
    <!-- Panel ends -->
  </div>
</div>