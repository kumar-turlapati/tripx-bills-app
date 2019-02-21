<?php
  use Atawa\Utilities;
  $hsnsac_code = isset($search_params['hsnSacCode']) ? $search_params['hsnSacCode'] : '';
  $hsnsac_desc = isset($search_params['hsnSacCodeDesc']) ? $search_params['hsnSacCodeDesc'] : '';
  
  $page_url = $pagination_url = '/hsnsac/list';  
?>
<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/hsnsac/add" class="btn btn-default">
              <i class="fa fa-file-text-o"></i> New HSN/SAC Code
            </a> 
          </div>
        </div>
        <div class="panel" style="margin-bottom:0px;">
          <div class="panel-body">
           <div id="filters-form">
              <form class="form-validate form-horizontal" method="POST">
                <div class="form-group">
                  <div class="col-sm-12 col-md-2 col-lg-1">Filter by</div>
                  <div class="col-sm-12 col-md-2 col-lg-2">
                    <input type="text" placeholder="HSN/SAC Code" name="hsnSacCode" id="hsnSacCode" class="form-control" value="<?php echo $hsnsac_code ?>">
                  </div>
                  <div class="col-sm-12 col-md-2 col-lg-2">
                    <input type="text" placeholder="HSN/SAC Description" name="hsnSacCodeDesc" id="hsnSacCodeDesc" class="form-control" value="<?php echo $hsnsac_desc ?>">
                  </div>
                  <div class="col-sm-12 col-md-2 col-lg-3">
                    <div class="col-sm-12"> 
                      <button class="btn btn-success"><i class="fa fa-file-text"></i> Filter</button>
                      <button type="reset" class="btn btn-warning" onclick="javascript:resetFilter('/hsnsac/list')"><i class="fa fa-refresh"></i> Reset </button>
                    </div>
                  </div>
                </div>
              </form>        
           </div>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="font14">
              <tr>
                <th width="5%" class="text-center">Sno.</th>
                <th width="10%" class="text-center">HSN / SAC<br />Code</th>
                <th width="42%" class="text-center">Description</th>
                <th width="23%" class="text-center">Description Short</th>
                <th width="10%" class="text-center">Status</th>
                <th width="5%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php if(count($codes)>0) { ?>
              <?php 
                $cntr = $sl_no;
                foreach($codes as $code_details):
                  $hsn_sac_code = $code_details['hsnSacCode'];
                  $hsn_sac_desc = $code_details['hsnSacCodeDesc'];
                  $hsn_sac_desc_short = $code_details['hsnSacCodeDescShort'];
                  $unique_code = $code_details['hsnSacUniqueCode'];
                  if((int)$code_details['status'] === 1) {
                    $status = 'Active';
                  } else {
                    $status = 'Inactive';
                  }
              ?>
                <tr class="text-right font11">
                  <td class="text-right valign-middle"><?php echo $cntr ?></td>
                  <td class="text-left valign-middle"><?php echo $hsn_sac_code ?></td>
                  <td class="text-left valign-middle"><?php echo $hsn_sac_desc ?></td>
                  <td class="text-left valign-middle"><?php echo $hsn_sac_desc_short ?></td>
                  <td class="text-left valign-middle"><?php echo $status ?></td>
                  <td class="valign-middle" align="center">
                    <div class="btn-actions-group">
                      <?php if($unique_code !== ''): ?>
                        <a class="btn btn-primary" href="/hsnsac/update/<?php echo $unique_code ?>" title="Edit HSN/SAC Code">
                          <i class="fa fa-pencil"></i>
                        </a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
            <?php
              $cntr++;
              endforeach; 
            ?>
            <?php } else { ?>
              <tr>
                <td colspan="4" align="center"><b>No records are available.</b></td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
        <?php include_once __DIR__."/../../../src/Layout/helpers/pagination.helper.php" ?>        
      </div>
    </section>
  </div>
</div>