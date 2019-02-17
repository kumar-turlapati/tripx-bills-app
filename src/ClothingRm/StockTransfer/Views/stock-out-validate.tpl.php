<?php
  $current_date = isset($form_data['transferDate']) && $form_data['transferDate']!=='' ? date("d-m-Y", strtotime($form_data['transferDate'])) : date("d-m-Y");
  $from_location_name = $location_ids[$form_data['fromLocationID']];
  $to_location_name = $location_ids[$form_data['toLocationID']];
  $from_location_code = $location_codes[$form_data['fromLocationID']];
  $to_location_code = $location_codes[$form_data['toLocationID']];

  $bill_amount = $form_data['billAmount'];
  $round_off = $form_data['roundOff'];
  $netpay = $form_data['netpay'];
  $total_qty = $form_data['totalQty'];

  // dump($_SESSION);
?>

<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo $flash_obj->print_flash_message(); ?>
        <div class="global-links actionButtons clearfix"> 
          <div class="pull-right text-right">
            <a href="/stock-transfer/register" class="btn btn-default"><i class="fa fa-book"></i> Stock Transfer Register</a>
            <a href="/stock-transfer/out" class="btn btn-default"><i class="fa fa-file-text-o"></i> New Stock Transfer</a> 
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="stockOutForm">
          <div class="panel" style="margin-bottom:0px;">
            <input type="hidden" name="locationCode" id="locationCode" value="<?php echo $to_location_code ?>" />
            <input type="hidden" name="transferCode" id="transferCode" value="<?php echo $transfer_code ?>" />
            <div class= "panel-body">
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Transferred from / బదిలీ చేయబడిన స్టోర్ పేరు</label>
                  <p style="font-size:16px;font-weight:bold;color:#225992;"><?php echo $from_location_name ?></p>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Transferred to / బదిలీ చేరవలిసిన స్టోర్ పేరు</label>
                  <p style="font-size:16px;font-weight:bold;color:#225992;"><?php echo $to_location_name ?></p>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label">Transfer date (dd-mm-yyyy) / బదిలీ చేసిన తేదీ</label>
                  <p style="font-size:16px;font-weight:bold;color:#225992;"><?php echo $current_date ?></p>
                </div>                
              </div>
              <div class="form-group">
                <div class="col-sm-12 col-md-12 col-lg-12">
                  <label class="control-label">Scan the barcode / బార్ కోడ్ ని స్కాన్ చేయండి</label>
                  <input
                    type="text"
                    id="stBarcode"
                    class="stBarcode"
                    style="font-size:16px;font-weight:bold;border:1px dashed #225992;padding-left:5px;font-weight:bold;width:70%"
                    maxlength="13"
                  />
                </div>
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-striped table-hover font12">
              <thead>
                <tr>
                  <th width="33%" class="text-center">Transferred Qty. / బదిలీ చేయబడిన స్టాక్</th>                  
                  <th width="33%" class="text-center">Scanned Qty. / స్కాన్ చేయబడిన స్టాక్</th>
                  <th width="33%" class="text-center">Difference / బదిలీ మరియు స్కాన్ మధ్య ఉన్న వ్యత్యాసం</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td id="trTransQty" align="center" style="font-size:60px;color:#225992;vertical-align:middle"><?php echo $total_qty ?></td>
                  <td id="trScannedQty" align="center" style="font-size:60px;vertical-align:middle;color:#5CDB95">0.00</td>
                  <td id="trDiff" align="center" style="font-size:60px;vertical-align:middle;color:#FC4445"><?php echo $total_qty ?></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="text-center">
            <button class="btn btn-primary cancelOp" id="Save" name="op">
              <i class="fa fa-save"></i> Save / ట్రాన్స్ఫర్ ని అంగీకరించండి
            </button>
            <button class="btn btn-danger cancelButton" id="stForm">
              <i class="fa fa-times"></i> Cancel / ఈ స్క్రీన్ ను రద్దు చెయ్యండి
            </button>
          </div>          
        </form>
      </div>
    </section>
  </div>
</div>