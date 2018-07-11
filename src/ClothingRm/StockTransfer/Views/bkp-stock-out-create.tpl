<?php 
  
  $page_url = $pagination_url = '/stock-transfer/out';

  $query_params[] = 'fromLocation='.$from_location;
  $query_params[] = 'toLocation='.$to_location;
  if(isset($search_params['medName']) && $search_params['medName'] !='') {
    $product_name = $search_params['medName'];
    $query_params[] = 'medName='.$product_name;
  } else {
    $product_name = '';
  }
  if(isset($search_params['category']) && $search_params['category'] !='' ) {
    $sel_category = $search_params['category'];
    $query_params[] = 'category='.$sel_category;
  } else {
    $sel_category = '';
  }

  if($query_params !== '') {
    $query_params = '?'.implode('&', $query_params);
    $page_url .= $query_params;
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
            <form class="form-validate form-horizontal" action="<?php echo $page_url ?>" id="stockOutTransfer" autocomplete="off">
              <div class="form-group">
                <div class="col-sm-12 col-md-1 col-lg-1 text-right">
                  <label class="control-label text-right"><b>Filter by</b></label>          
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                 <input placeholder="Product name" type="text" name="productName" id="productName" class="form-control" value="<?php echo $product_name ?>">
                </div>
                <div class="col-sm-12 col-md-2 col-lg-2">
                  <div class="select-wrap">
                    <select class="form-control" name="category" id="category">
                      <?php 
                        foreach($categories as $key=>$value):
                          if($sel_category === $key) {
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
                <div class="col-sm-12 col-md-3 col-lg-3">                         
                  <?php include_once __DIR__."/../../../Layout/helpers/filter-buttons.helper.php" ?>
                </div>
              </div>
              <input type="hidden" name="fromLocation" value="<?php echo $from_location ?>" />
              <input type="hidden" name="toLocation" value="<?php echo $to_location ?>" />
            </form>        
          </div>
        </div>
        <?php if(count($items_list) > 0): ?>
          <div class="table-responsive">
            <table class="table table-striped table-hover" id="itemnames">
              <thead>
                <tr class="font12">
                  <th width="5%" class="text-center">Sno.</th>
                  <th width="16%" class="text-left">Sytem code</th>                
                  <th width="20%" class="text-left">Product name</th>
                  <th width="8%" class="text-center">HSN/SAC<span class="brk">Code</span></th>
                  <th width="8%" class="text-center">Barcode</th>
                  <th width="8%" class="text-center">SKU</th>
                  <th width="10%" class="text-center">LOT No.</th>                
                  <th width="7%" class="text-center">Available<span class="brk">Qty.</span></th>                
                  <th width="8%" class="text-center">Stockout<span class="brk">Qty.</span></th>
                  <th width="7%" class="text-center">MRP<span class="brk">(in Rs.)</span></th>
                </tr>
              </thead>
              <tbody>
                <?php 
                  $cntr = $sl_no;
                  foreach($items_list as $item_details):
                    $system_code = $item_details['itemCode'];
                    $product_name = $item_details['itemName'];
                    $closing_qty = $item_details['closingQty'];
                    $mrp = $item_details['mrp'];
                    $lot_no = $item_details['lotNo'];
                    $hsn_sac_code = '';
                    $barcode = '';
                    $sku = '';
                ?>
                  <tr class="text-uppercase text-right font11">
                    <td class="text-center valign-middle"><?php echo $cntr ?></td>
                    <td class="text-left valign-middle" style="font-size:9px;"><?php echo $system_code.$lot_no ?></td>                    
                    <td class="text-left med-name valign-middle">
                      <a
                        href="/products/update/<?php echo $system_code ?>"
                        class="hyperlink"
                        title="click here to Edit product details"
                        target="_blank"
                      >
                        <?php echo $product_name ?>
                      </a>
                    </td>
                    <td class="text-right valign-middle"><?php echo $hsn_sac_code ?></td>
                    <td class="text-right valign-middle"><?php echo $barcode ?></td>
                    <td class="text-right valign-middle"><?php echo $sku ?></td>
                    <td class="text-right valign-middle"><?php echo $lot_no ?></td>                  
                    <td class="text-right valign-middle" style="font-weight:bold;color:green;font-size:16px;"><?php echo $closing_qty ?></td>
                    <td class="text-bold valign-middle" title="Click here to add Out Qty." style="font-weight:bold;color:#000;font-size:16px;">&nbsp;</td>
                    <td><?php echo number_format($mrp,2) ?></td>
                  </tr>
              <?php
                $cntr++;
                endforeach; 
              ?>
              </tbody>
            </table>
            <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>          
          </div>
          <div class="text-center">
            <button class="btn btn-danger" id="stOutCancel">
              <i class="fa fa-times"></i> Cancel
            </button>
            <button class="btn btn-primary" id="Save">
              <i class="fa fa-save"></i> Save
            </button>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>
</div>