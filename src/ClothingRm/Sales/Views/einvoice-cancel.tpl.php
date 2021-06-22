<?php
  use Atawa\Utilities;

  $cancel_reason = isset($form_data['cancelReason']) ? $form_data['cancelReason'] : 0; 
  $cancel_remarks = isset($form_data['cancelRemarks']) ? $form_data['cancelRemarks'] : '';
?>
<div class="row">
  <div class="col-lg-12"> 
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/sales/list" class="btn btn-default">
              <i class="fa fa-book"></i> Sales Register
            </a>&nbsp;
            <a href="/einvoices/list" class="btn btn-default">
              <i class="fa fa-book"></i> eInvoices Register
            </a>            
          </div>
        </div>
        <form class="form-validate form-horizontal" method="POST" autocomplete="off" id="irnCancelForm">
          <?php if(count($invoice_details) > 0): ?>
            <div class="table-responsive">
              <table class="table table-striped table-hover font12" style="margin-bottom: 0px;">
                <thead>
                  <tr>
                    <th width="15%"  class="text-center">Invoice no.</th>                  
                    <th width="12%" class="text-center">Invoice date</th>
                    <th width="10%" class="text-center">Invoice value<br />( in Rs. )</th>
                    <th width="33%"  class="text-center">Customer name</th>
                    <th width="10%" class="text-center">GST no.</th>
                    <th width="20%" class="text-center">Location / Store name</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="font11" align="center">
                    <td style="font-size:16px; color: #D83A56; font-weight: bold;"><?php echo $invoice_details['billNo'] ?></td>
                    <td style="font-size:16px; color: #D83A56; font-weight: bold;"><?php echo date("d-m-Y", strtotime($invoice_details['invoiceDate'])) ?></td>
                    <td style="font-size:16px; color: #D83A56; font-weight: bold;"><?php echo number_format($invoice_details['netPay'], 2,'.','') ?></td>
                    <td style="font-size:16px; color: #D83A56; font-weight: bold;"><?php echo $invoice_details['customerName'] ?></td>
                    <td style="font-size:16px; color: #D83A56; font-weight: bold;"><?php echo $invoice_details['customerGstNo'] ?></td>
                    <td style="font-size:16px; color: #D83A56; font-weight: bold;"><?php echo $invoice_details['locationName'] ?></td>
                  </tr>
                  <tr style="background: rgba(52, 73, 94, 0.90); color: #ecf0f1; font-weight: bold; text-align: center;">
                    <td>Our Pincode</td>
                    <td>Buyer Pincode</td>
                    <td colspan="4">IRN</td>
                  </tr>
                  <tr>
                    <td style="font-weight: bold; text-align: center; color: #D83A56; font-size: 16px;"><?php echo $invoice_details['locPincode'] ?></td>
                    <td style="font-weight: bold; text-align: center; color: #D83A56; font-size: 16px;"><?php echo $invoice_details['pincode'] ?></td>
                    <td style="font-weight: bold; text-align: center; color: #D83A56; font-size: 16px;" colspan="4" align="center"><?php echo $irn ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
          <br />
          <div class="panel" style="margin-bottom:30px;">
            <div class="panel-body">
              <div class="form-group">
                <div class="col-sm-12 col-md-4 col-lg-4">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">Cancel reason</label>
                  <div class="select-wrap">
                    <select class="form-control" name="cancelReason" id="cancelReason">
                      <?php 
                        foreach($cancel_reasons as $key=>$value):
                          if((int)$cancel_reason === (int)$key) {
                            $selected = 'selected="selected"';
                          } else {
                            $selected = '';
                          }
                      ?>
                        <option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $value ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <?php if(isset($form_errors['cancelReason'])): ?>
                    <span class="error"><?php echo $form_errors['cancelReason'] ?></span>
                  <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-8 col-lg-8">
                  <label class="control-label" style="font-size:14px;color:#2E1114;font-weight:bold;">Cancel remarks</label>
                  <input 
                    type="text" 
                    class="form-control noEnterKey" 
                    name="cancelRemarks" 
                    id="cancelRemarks" 
                    value="<?php echo $cancel_remarks ?>"
                    maxlength="100"
                  />
                  <?php if(isset($form_errors['cancelRemarks'])): ?>
                    <span class="error"><?php echo $form_errors['cancelRemarks'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-success" id="cancelIrn">
              <i class="fa fa-save"></i> Cancel IRN
            </button>
            <button class="btn btn-danger cancelButton" id="cancelIrnCancel">
              <i class="fa fa-times"></i> Reset
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>