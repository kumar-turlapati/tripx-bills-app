<?php
  use Atawa\Utilities;

  if(isset($template_vars) && is_array($template_vars)) {
    extract($template_vars); 
  }
  
  if(isset($search_params['itemName']) && $search_params['itemName'] !='') {
    $medName = $search_params['itemName'];
  } else {
    $medName = '';
  } 
?>

<!-- Basic form starts -->
<div class="row">
  <div class="col-lg-12">
    
    <!-- Panel starts -->
    <section class="panel">
      <div class="panel-body">
        <?php echo Utilities::print_flash_message() ?>
        <div id="filters-form">
          <form class="form-validate form-horizontal" method="POST">
            <div class="form-group">
              <div class="col-sm-12 col-md-6 col-lg-6">
                <label class="control-label">Type product name</label>
                <input type="text" name="itemName" id="itemName" class="form-control inameAc" value="<?php echo $medName ?>">
              </div>
            <div class="col-sm-12 col-md-3 col-lg-3">
               <label class="control-label">&nbsp;</label>
                <button class="btn btn-success"><i class="fa fa-search"></i> Search</button>
                <button type="reset" class="btn btn-warning" onclick="javascript:resetFilter('/inventory/search-products')"><i class="fa fa-refresh"></i> Reset </button>
            </div>
            </div>
          </form>
        </div>
        <?php
          if(count($item_details)>0):
            $item_name = $item_details['itemDetails']['itemName'];
            $mrp = $item_details['batches'][0]['itemRate'];
            $ava_qty = $item_details['batches'][0]['availableQty'];
            $status = $item_details['itemDetails']['itemStatus'];
            $category = (!is_null($item_details['itemDetails']['catName'])?$item_details['itemDetails']['catName']:'');
            if((int)$item_details['itemDetails']['itemStatus']===1) {
              $status = 'Active';
            } else {
              $status = 'Inactive';
            }
        ?>
          <br /><h2 class="hdg-reports text-center">Product Details</h2>
          <div class="table-responsive">
            <table class="table table-bordered table-hover font12">
              <thead>
                <tr>
                  <th width="30%" class="text-center">Item name</th>
                  <th width="10%" class="text-center">Item rate (Rs.)</th>
                  <th width="10%" class="text-center">Available qty.</th>
                  <th width="10%" class="text-center">Category</th>
                  <th width="10%" class="text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><?php echo $item_name ?></td>
                  <td class="text-right"><?php echo number_format($mrp,2) ?></td>
                  <td class="text-right"><?php echo number_format($ava_qty,2)?></td>
                  <td><?php echo $category ?></td>
                  <td class="text-right"><?php echo $status ?></td>
                </tr>
              </tbody>
            </table>            
          </div>
        <?php endif; ?>
      </div>
    </section>
    <!-- Panel ends -->
  </div>
</div>