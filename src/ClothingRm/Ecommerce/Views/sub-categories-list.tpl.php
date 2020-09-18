<?php 
  use Atawa\Config\Config;
  $catalog_domain = Config::get_catalog_domain();

  $pagination_url = $page_url = '/ecom/categories/list';
  $query_params = [];
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
            <a href="/ecom/category/create?mod=subcategory" class="btn btn-default">
              <i class="fa fa-plus"></i> New Subcategory
            </a>&nbsp;
            <a href="/ecom/categories/list" class="btn btn-default">
              <i class="fa fa-files-o"></i> Categories
            </a>
          </div>
        </div>
        <div class="table-responsive">
          <?php if(count($categories) > 0): ?>
            <table class="table table-striped table-hover">
              <thead>
                <tr class="font12">
                  <th width="5%" class="text-center">Sno.</th>
                  <th width="10%" class="text-center">Subcategory name</th>
                  <th width="25%" class="text-center">Subcategory short desc.</th>
                  <th width="25%" class="text-center">Subcategory long desc.</th>
                  <th width="5%" class="text-center">Status</th>
                  <th width="10%" class="text-center">Options</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                  $cntr = 1;
                  foreach($categories as $category_details):
                    $category_name = $category_details['categoryName'];
                    $category_short_desc = $category_details['categoryDescShort'];
                    $category_long_desc = $category_details['categoryDescLong'];
                    $category_status = (int)$category_details['status'] === 0 ? 'Inactive' : 'Active';
                    $category_url = '/ecom/category/update/'.$category_details['categoryID'].'?mod=subcategory';
                ?>
                  <tr class="text-uppercase text-right font11">
                    <td class="text-right valign-middle"><?php echo $cntr ?></td>
                    <td class="text-left valign-middle"><?php echo $category_name ?></td>
                    <td class="text-left valign-middle"><?php echo $category_short_desc ?></td>
                    <td class="text-left valign-middle"><?php echo $category_long_desc ?></td>
                    <td class="text-right valign-middle"><?php echo $category_status ?></td>
                    <td class="text-right valign-middle">
                      <div class="btn-actions-group">
                        <a class="btn btn-info" href="<?php echo $category_url ?>" title="Edit Subcategory">
                          <i class="fa fa-pencil" aria-hidden="true"></i>
                        </a>
                        <a class="btn btn-danger" href="/ecom/category/delete/<?php echo $category_details['categoryID'] ?>" title="Delete Category">
                          <i class="fa fa-times" aria-hidden="true"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
              <?php
                $cntr++;
                endforeach; 
              ?>
              </tbody>
            </table>
          <?php else: ?>
            <div style="text-align:center;margin-top:10px;font-weight:bold;color:red;font-size:14px;">No data available.</div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </div>
</div>