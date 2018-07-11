<?php
  use Atawa\Utilities;

  if(isset($template_vars) && is_array($template_vars)) {
    extract($template_vars); 
  }

  $pagination_url = '/inventory/item-threshold-list';  
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

        <!-- Right links starts -->
        <div class="global-links actionButtons clearfix"> 
          <!-- Button style -->
          <div class="pull-right text-right">
            <a href="/inventory/item-threshold-add" class="btn btn-default"><i class="fa fa-file-text-o"></i> Add Item Threshold Qty.</a> 
          </div>
          <!-- Button style --> 
        </div>
        
        <!-- Right links ends -->         

        <div class="panel">
          <h2 class="hdg-reports text-center">Threshold Item Quantities List</h2>
          <div class="table-responsive">
            <table class="table table-striped table-hover font12">
              <thead>
                <tr>
                  <th width="5%" class="text-center">Sno.</th>
                  <th width="35%" class="text-left">Item name</th>
                  <th width="10%" class="text-center">Threshold<br />qty.</th>
                  <th width="35%" class="text-center">Supplier<br />name</th>
                  <th width="10%" class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                  $cntr = $sl_no;
                  foreach($items as $item_details):
                    $item_name = $item_details['itemName'];
                    $thr_qty = $item_details['thrQty'];
                    $supplier_name = $item_details['supplierName'];
                    $thr_code = $item_details['thrCode'];
                ?>
                    <tr class="text-right font12">
                      <td><?php echo $cntr ?></td>
                      <td class="text-left"><?php echo $item_name ?></td>
                      <td class="text-bold"><?php echo $thr_qty ?></td>
                      <td class="text-right"><?php echo $supplier_name ?></td>
                      <td>
                        <div class="btn-actions-group">
                          <a class="btn btn-primary" href="/inventory/item-threshold-update/<?php echo $thr_code ?>" title="Edit threshold qty.">
                            <i class="fa fa-pencil"></i>
                          </a>
                          <?php /*
                          <a class="btn btn-danger" href="javascrip:void(0)" title="Remove threshold qty.">
                            <i class="fa fa-times"></i>
                          </a>*/ ?>
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
        </div>
      </div>
    </section>
    <!-- Panel ends -->
  </div>
</div>