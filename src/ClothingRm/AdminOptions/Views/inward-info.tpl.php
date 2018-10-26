<?php
  $voc_no = isset($submitted_data['vocNo']) ? $submitted_data['vocNo'] : '';
  $supplier_code = isset($submitted_data['supplierID']) ? $submitted_data['supplierID'] : '';
  $location_code = isset($submitted_data['locationCode']) ? $submitted_data['locationCode'] : '';
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panel">
      <div class="panel-body">
        <?php echo $flash_obj->print_flash_message(); ?> 
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/inward-entry/list" class="btn btn-default">
              <i class="fa fa-compass"></i> Purchase Register
            </a>&nbsp;&nbsp;
            <a href="/grn/list" class="btn btn-default">
              <i class="fa fa-laptop"></i> GRN Register
            </a>
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="editPOAfterGRN">
          <div class="form-group">
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">PO No. (Auto increment number)</label>
              <input type="text" class="form-control" name="vocNo" id="vocNo" value="<?php echo $voc_no ?>" maxlength="20" />
              <?php if(isset($errors['vocNo'])): ?>
                <span class="error"><?php echo $errors['vocNo'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Supplier name</label>
              <div class="select-wrap">
                <select class="form-control" name="supplierID" id="supplierID">
                  <?php 
                    foreach($suppliers as $key=>$value): 
                      if($supplier_code === $key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>>
                      <?php echo $value ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if(isset($errors['supplierID'])): ?>
                <span class="error"><?php echo $errors['supplierID'] ?></span>
              <?php endif; ?>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 m-bot15">
              <label class="control-label">Store name</label>
              <div class="select-wrap">
                <select class="form-control" name="locationCode" id="locationCode">
                  <?php 
                    foreach($client_locations as $key=>$value): 
                      if($location_code === $key) {
                        $selected = 'selected="selected"';
                      } else {
                        $selected = '';
                      }
                  ?>
                    <option value="<?php echo $key ?>" <?php echo $selected ?>>
                      <?php echo $value ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if(isset($errors['locationCode'])): ?>
                <span class="error"><?php echo $errors['locationCode'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-warning" id="grnDelete"><i class="fa fa-share"></i> I know what i am doing. Continue</button>
          </div>
        </form>
      </div>
      <?php if($voc_type === 'GRN'): ?>        
        <div style="font-weight:bold;border:2px dotted #000;text-align:left;padding:5px;font-size:14px;margin-top:50px;color:red;">
          This option will delete existing GRN for a PO and allows you to modify the PO again.<br />However, modifying the PO after GRN is highly discouraged. You may loose track of some / all items if there are any Sales transactions against the PO. You may also need to add adjustment entries in case if there are any sales against the items in this PO. The PO also needs to go through APPROVAL process again.
        </div>
      <?php endif; ?>
    </section>
  </div>
</div>