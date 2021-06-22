<?php
  use Atawa\Utilities;
  use Atawa\Constants;
  // dump($einvoice_details);
?>

<div class="row">
  <div class="col-lg-12">
    <section class="panelBox">
      <div class="panelBody">
        <?php echo Utilities::print_flash_message() ?>
        <div class="global-links actionButtons clearfix">
          <div class="pull-right text-right">
            <a href="/einvoices/list" class="btn btn-default"><i class="fa fa-book"></i> eInvoices Register</a>
          </div>
        </div>
        <div align="center" style="margin-bottom: 8px;">
          <button class="btn btn-warning" id="printIrn" name="printIrn" value="printIrn">
            <i class="fa fa-print"></i> Print IRN
          </button>&nbsp;
          <button 
            class="btn btn-danger" 
            id="cancelIrn" 
            name="cancelIrn" 
            value="cancelIrn"
            onclick="window.location.href='/einvoice/cancel-irn/<?php echo $einvoice_details['irnNo'].'/'.$einvoice_details['qbinvoiceCode'] ?>'" 
          >
            <i class="fa fa-times"></i> Cancel IRN
          </button>          
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover font12" style="table-layout: fixed;">
            <tbody>
              <tr align="right">
                <td class="valign-middle" style="width:20%; font-size: 14px; font-weight: bold;">Document No.</td>
                <td class="valign-middle" align="left" style="width:80%; color: green; font-weight: bold; font-size: 16px;"><?php echo $einvoice_details['docNo'] ?></td>
              </tr>
              <tr align="right">
                <td class="valign-middle" style="width:20%; font-size: 14px; font-weight: bold;">Customer Name / GST No.</td>
                <td class="valign-middle" align="left" style="width: 80%; color: green; font-weight: bold; font-size: 16px;"><?php echo $einvoice_details['customerGstNo'] ?></td>
              </tr>              
              <tr align="right">
                <td class="valign-middle" style="width:20%; font-size: 14px; font-weight: bold;">Acknowledgement No.</td>
                <td class="valign-middle" align="left" style="width: 80%; color: green; font-weight: bold; font-size: 16px;"><?php echo $einvoice_details['ackNo'] ?></td>
              </tr>
              <tr align="right">
                <td class="valign-middle" style="width:20%; font-size: 14px; font-weight: bold;">Acknowledgement Date</td>
                <td class="valign-middle" align="left" style="width: 80%; color: green; font-weight: bold; font-size: 16px;">
                  <?php echo date("d-m-Y H:ia", strtotime(str_replace(['T', '.000Z'], ['', ''], $einvoice_details['ackDt']))); ?>
                </td>
              </tr>
              <tr align="right">
                <td class="valign-middle" style="width:20%; font-size: 14px; font-weight: bold;">IRN</td>
                <td class="valign-middle" align="left" style="width: 80%; color: green; font-weight: bold; font-size: 16px;"><?php echo $einvoice_details['irnNo'] ?></td>
              </tr>
              <tr align="right">
                <td class="valign-middle" style="width:20%; font-size: 14px; font-weight: bold;">Status</td>
                <td class="valign-middle" align="left" style="width: 80%; color: green; font-weight: bold; font-size: 16px;"><?php echo $einvoice_details['status'] === 'ACT' ? 'Active' : '<span style="color: red;">Cancelled</span>' ?></td>
              </tr>              
              <tr align="right">
                <td class="valign-middle" style="width:20%; font-size: 14px; font-weight: bold;">Invoice Value (in Rs.)</td>
                <td class="valign-middle" align="left" style="width: 80%; color: green; font-weight: bold; font-size: 16px;">
                  <?php echo $einvoice_details['invoiceValue'] > 0 ? number_format($einvoice_details['invoiceValue'],2,'.','') : '' ?>
                </td>
              </tr>
              <tr align="right">
                <td class="valign-middle" style="width:20%; font-size: 14px; font-weight: bold;">Location / Store name</td>
                <td class="valign-middle" align="left" style="width: 80%; color: green; font-weight: bold; font-size: 16px;">
                  <?php echo isset($location_ids[$einvoice_details['qblocationId']]) ? $location_ids[$einvoice_details['qblocationId']] : '' ?>
                </td>
              </tr>
              <tr align="right">
                <td class="valign-middle" style="width:20%; font-size: 14px; font-weight: bold;">Invoice No.</td>
                <td class="valign-middle" align="left" style="width: 80%; color: green; font-weight: bold; font-size: 16px;">
                  <?php if(strlen($einvoice_details['qbinvoiceNo']) > 0): ?>
                    <a href="/sales/view-invoice/<?php echo $einvoice_details['qbinvoiceCode'] ?>" class="hyperlink" style="font-size: 16px;" target="_blank">
                      <?php echo $einvoice_details['qbinvoiceNo'] ?>
                    </a>&nbsp;<span style="font-size:11px;"><i class="fa fa-window-restore" aria-hidden="true"></i></span>
                  <?php endif; ?>
                </td>
              </tr>              
              <tr align="right">
                <td class="valign-middle" style="width:20%; font-size: 14px; font-weight: bold;">Eway Bill No.</td>
                <td class="valign-middle" align="left" style="width: 80%; color: green; font-weight: bold; font-size: 16px;"><?php echo $einvoice_details['ewbNo'] ?></td>
              </tr>
              <tr align="right">
                <td class="valign-middle" style="width:20%; font-size: 14px; font-weight: bold;">Eway Bill Date</td>
                <td class="valign-middle" align="left" style="width: 80%; color: green; font-weight: bold; font-size: 16px;">
                  <?php echo !is_null($einvoice_details['ewbDate']) ? date("d-m-Y H:ia", strtotime(str_replace(['T', '.000Z'], ['', ''], $einvoice_details['ewbDate']))) : ''; ?>                  
                </td>
              </tr>
              <tr align="right">
                <td class="valign-middle" style="width:20%; font-size: 14px; font-weight: bold;">Cancel Date</td>
                <td class="valign-middle" align="left" style="width: 80%; color: green; font-weight: bold; font-size: 16px;">
                  <?php echo $einvoice_details['status'] === 'CAN' ? '<span style="color: red;">'.date("d-m-Y h:ia", strtotime($einvoice_details['cancelDate'])).'</span>' : ''  ?>
                </td>
              </tr>              
              <tr align="right">
                <td class="valign-middle" style="width:20%; font-size: 14px; font-weight: bold;">Cancel Remarks</td>
                <td class="valign-middle" align="left" style="width: 80%; color: green; font-weight: bold; font-size: 16px;">
                  <?php echo '<span style="color: red;">'.$einvoice_details['cancelRemarks'].'</span>' ?>
                </td>
              </tr>
              <tr align="right">
                <td class="valign-middle" style="width:20%; font-size: 14px; font-weight: bold;">Signed QR Code</td>
                <td class="valign-middle" align="left" style="word-break: break-all; font-size: 10px;"><?php echo $einvoice_details['signedQrCode'] ?></td>
              </tr>              
              <tr align="right">
                <td class="valign-middle" style="width:20%; font-size: 14px; font-weight: bold;">QR Code</td>
                <td class="valign-middle" style="width:80%;" align="center">
                  <img src="/qrcode?data=<?php echo $einvoice_details['signedQrCode'] ?>">
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>