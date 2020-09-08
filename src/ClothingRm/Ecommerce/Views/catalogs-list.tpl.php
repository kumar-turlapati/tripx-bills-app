<?php 
  use Atawa\Config\Config;
  $catalog_domain = Config::get_catalog_domain();

  $pagination_url = $page_url = '/catalogs/list';
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
            <a href="/catalog/create" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New Catalog
            </a>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover" id="itemnames">
            <thead>
              <tr class="font12">
                <th width="5%" class="text-center">Sno.</th>
                <th width="10%" class="text-left">Catalog name</th>
                <th width="20%" class="text-center">Catalog short desc.</th>               
                <th width="20%" class="text-center">Catalog long desc.</th>               
                <th width="5%" class="text-center">Status</th>               
                <th width="5%" class="text-center">Is default?</th>
                <th width="15%" class="text-center">Options</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $cntr = $sl_no;
                foreach($catalogs as $catalog_details):
                  $catalog_name = $catalog_details['catalogName'];
                  $catalog_short_desc = $catalog_details['catalogDescShort'];
                  $catalog_desc = $catalog_details['catalogDesc'];
                  $catalog_status = (int)$catalog_details['status'] === 0 ? 'Inactive' : 'Active';
                  $is_default = (int)$catalog_details['isDefault'] === 0 ? 'No' : 'Yes';
                  $catalog_code = $catalog_details['catalogCode'];
                  $catalog_url = $catalog_domain.$_SESSION['ccode'].$catalog_details['catalogCode'];
              ?>
                <tr class="text-uppercase text-right font11">
                  <td class="text-right valign-middle"><?php echo $cntr ?></td>
                  <td class="text-left valign-middle"><?php echo $catalog_name ?></td>
                  <td class="text-left valign-middle"><?php echo $catalog_short_desc ?></td>
                  <td class="text-left valign-middle"><?php echo $catalog_desc ?></td>
                  <td class="text-right valign-middle"><?php echo $catalog_status ?></td>
                  <td class="text-right valign-middle"><?php echo $is_default ?></td>
                  <td class="text-right valign-middle">
                    <div class="btn-actions-group">
                      <a class="btn btn-info" href="<?php echo $catalog_url ?>" title="Public Url" target="_blank">
                        <i class="fa fa-share-alt" aria-hidden="true"></i>
                      </a>
                      <a class="btn btn-success" href="/catalog/update/<?php echo $catalog_code ?>" title="Edit Catalog">
                        <i class="fa fa-pencil" aria-hidden="true"></i>
                      </a>
                      <a class="btn btn-warning" href="/catalog/items/<?php echo $catalog_code ?>" title="Add / Remove items to Catalog">
                        <i class="fa fa-info" aria-hidden="true"></i>
                      </a>
                      <a class="btn btn-primary" href="/catalog/view/<?php echo $catalog_code ?>" title="View Catalog">
                        <i class="fa fa-eye" aria-hidden="true"></i>
                      </a>
                      <a class="btn btn-danger delCatalog" href="/catalog/delete/<?php echo $catalog_code ?>" title="Delete Catalog">
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
          <?php include_once __DIR__."/../../../Layout/helpers/pagination.helper.php" ?>          
        </div>
      </div>
    </section>
  </div>
</div>