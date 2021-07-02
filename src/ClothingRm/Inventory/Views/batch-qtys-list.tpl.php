<?php
  use Atawa\Utilities;

  $query_params = [];  
  if(isset($search_params['psName']) && $search_params['psName'] !== '') {
    $psName = $search_params['psName'];
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
  if(isset($search_params['size']) && $search_params['size'] !== '') {
    $size = $search_params['size'];
    $query_params[] = 'size='.$size;
  } else {
    $size = '';
  }
  if(isset($search_params['barcode']) && $search_params['barcode'] !== '') {
    $barcode = $search_params['barcode'];
    $query_params[] = 'barcode='.$barcode;
  } else {
    $barcode = '';
  }
  if(isset($search_params['cno']) && $search_params['cno'] !== '') {
    $cno = $search_params['cno'];
    $query_params[] = 'cno='.$cno;
  } else {
    $cno = '';
  }  
  if(isset($search_params['category']) && $search_params['category'] !== '') {
    $categoryCode = $search_params['category'];
    $query_params[] = 'category='.$categoryCode;
  } else {
    $categoryCode = '';
  }
  if(isset($search_params['locationCode']) && $search_params['locationCode'] !== '') {
    $locationCode = $search_params['locationCode'];
    $query_params[] = 'locationCode='.$locationCode;
  } else {
    $locationCode = $default_location;
  }
  if(isset($search_params['lotNo']) && $search_params['lotNo'] !== '') {
    $lot_no = $search_params['lotNo'];
    $query_params[] = 'lotNo='.$lot_no;
  } else {
    $lot_no = '';
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
                <div class="col-sm-12 col-md-2 col-lg-2">
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
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Product / Service Name" type="text" name="psName" id="psName" class="form-control inameAc" value="<?php echo $psName ?>">
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
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Brand" type="text" name="brandName" id="brandName" class="form-control brandAc" value="<?php echo $brandName ?>">
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Size" type="text" name="size" id="size" class="form-control" value="<?php echo $size ?>">
                </div>                
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Barcode" type="text" name="barcode" id="barcode" class="form-control" value="<?php echo $barcode ?>">
                </div>
                <div style="height:40px;"></div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Case / Box No." type="text" name="cno" id="cno" class="form-control" value="<?php echo $cno ?>">
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <input placeholder="Lot No." type="text" name="lotNo" id="lotNo" class="form-control" value="<?php echo $lot_no ?>">
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
                  <th width="5%"  class="text-center">Sno</th>
                  <th width="23%" class="text-center">Item name</th>
                  <?php /*<th width="10%" class="text-center">Category</th> */?>
                  <th width="12%" class="text-center">Brand</th>
                  <?php /*<th width="8%"  class="text-center">Style code</th>*/?>
                  <th width="6%"  class="text-center">Size</th>
                  <?php /*<th width="8%"  class="text-center">Color</th>*/ ?>
                  <th width="10%" class="text-center">Lot no.</th>
                  <th width="8%" class="text-center">CASE / Box no.</th>
                  <th width="8%"  class="text-center">M.R.P<br />( in Rs. )</th>
                  <th width="8%"  class="text-center">Wholesale<br />price<br />( in Rs. )</th>
                  <th width="8%"  class="text-center">Exmill<br />price<br />( in Rs. )</th>
                  <th width="8%"  class="text-center">Online<br />price<br />( in Rs. )</th>
                  <th width="8%"  class="text-center">Available<br />qty.</th>
                  <th width="8%"  class="text-center">Barcode</th>
                  <?php /*<th width="10%" class="text-center">Value<br />(in Rs.)</th>*/?>
                </tr>
              </thead>
              <tbody>
                <?php 
                  $cntr = $sl_no;
                  $total_value = $total_qty = 0;
                  foreach($items as $item_details):
                    // dump($item_details);
                    // exit;
                    $item_name = $item_details['itemName'];
                    $category_name = $item_details['categoryName'];
                    $brand_name = $item_details['brandName'];
                    $item_code = $item_details['itemCode'];
                    $ava_qty = $item_details['closingQty'];
                    $lot_no = $item_details['lotNo'];
                    $cno = $item_details['cno'];
                    $location_id = $item_details['locationID'];
                    $location_name = isset($location_ids[$location_id]) ?  $location_ids[$location_id] : 'Invalid';
                    $style_code = $item_details['itemStyleCode'];
                    $size = $item_details['itemSize'];
                    $color = $item_details['itemColor'];

                    $mrp = $item_details['mrp'];
                    $online_price = $item_details['onlinePrice'];
                    $wholesale_price = $item_details['wholesalePrice'];
                    $exmill_price = $item_details['exMill'];
                    $item_value = $ava_qty * $mrp;

                    $total_value += $item_value;
                    $total_qty += $ava_qty;
                ?>
                  <tr class="text-right font11">
                    <td class="valign-middle"><?php echo $cntr ?></td>
                    <td class="text-left valign-middle">
                      <?php echo $item_name ?>
                    </td>
                    <?php /*<td class="text-left valign-middle" title="<?php echo $category_name ?>"><?php echo substr($category_name,0,10) ?></td>*/ ?>    
                    <td class="text-left valign-middle" title="<?php echo $brand_name ?>"><?php echo substr($brand_name,0,15) ?></td>                 
                    <?php /*<td class="text-left valign-middle"><?php echo $style_code ?></td>*/?>
                    <td class="text-right valign-middle"><?php echo $size ?></td>
                    <?php /*<td class="text-left valign-middle"><?php echo $color ?></td> */?>
                    <td class="text-left valign-middle"><?php echo $lot_no ?></td>
                    <td class="text-left valign-middle"><?php echo $cno ?></td>                                        
                    <td class="text-right valign-middle" style="color: #000; font-weight: bold;"><?php echo number_format($mrp,2,'.','') ?></td>
                    <td class="text-right valign-middle" style="color: #225992; font-weight: bold;">
                      <?php echo $wholesale_price > 0 ? number_format($wholesale_price,2,'.','') : '' ?>
                    </td>
                    <td class="text-right valign-middle" style="color: #f0ad4e; font-weight: bold;">
                      <?php echo $exmill_price > 0 ? number_format($exmill_price,2,'.','') : '' ?>
                    </td>
                    <td class="text-right valign-middle" style="color: orangered; font-weight: bold;">
                      <?php echo $online_price > 0 ? number_format($online_price,2,'.','') : '' ?>
                    </td>
                    <td class="text-right valign-middle" style="font-size: 14px; font-weight: bold;color: green;">
                      <?php echo number_format($ava_qty,2,'.','') ?>
                    </td>
                    <td class="text-right valign-middle"><?php echo $item_details['barcode'] ?></td>
                    <?php /*<td class="text-right valign-middle"><?php echo number_format($item_value,2,'.','') ?></td>*/?>
                  </tr>
              <?php
                $cntr++;
                endforeach;
              ?>
                <tr>
                  <td colspan="10" style="font-size:14px;font-weight:bold;text-align:right;">PAGE TOTALS (MRP * Available Qty.)</td>
                  <td style="font-size:16px;font-weight:bold;text-align:right;"><?php echo number_format($total_qty, 2, '.', '') ?></td>
                  <td style="font-size:16px;font-weight:bold;text-align:right;"><?php echo number_format($total_value, 2, '.', '') ?></td>
                </tr>
                <tr>
                  <td colspan="10" style="font-size:14px;font-weight:bold;text-align:right;">STORE TOTALS (MRP * Available Qty.)</td>
                  <td style="font-size:16px;font-weight:bold;text-align:right;"><?php echo number_format($store_totals['totalQty'], 2, '.', '') ?></td>
                  <td style="font-size:16px;font-weight:bold;text-align:right;"><?php echo number_format($store_totals['totalValue'], 2, '.', '') ?></td>
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
