<?php
	use Atawa\Utilities;
	use Atawa\Constants;

	// echo '<pre>';
	// var_dump($sale_details);
	// var_dump($sale_item_details);
	// echo '</pre>';
	// exit;

  $bill_date      =  date('d-M-Y',strtotime($sale_details['invoiceDate']));
  $bill_time      =  date('h:ia',strtotime($sale_details['createdTime']));
  $bill_no        =  $sale_details['billNo'];
  $pay_method     =  Constants::$PAYMENT_METHODS_RC_SHORT[$sale_details['paymentMethod']];
  $bill_amount    =  $sale_details['billAmount'];
  $bill_discount  =  $sale_details['discountAmount'];
  $total_amount   =  $sale_details['totalAmount'];
  $total_amt_r    =  $sale_details['roundOff'];
  $net_pay        =  $sale_details['netPay'];
  $customer_name  =  $sale_details['customerName'] !== '' ? substr(strtoupper($sale_details['customerName']),0,20) : '';
  $regn_code      =  $sale_details['extRegnCode'];
  $tax_amount     =  $sale_details['taxAmount'];
  $payment_method =  (int)$sale_details['paymentMethod'];
  $tmp_cust_name  =  $sale_details['tmpCustName'];

	$business_name 	=		isset($sale_details['locationNameShort']) && $sale_details['locationNameShort'] !== '' ? $sale_details['locationNameShort'] : $sale_details['locationName'];
	$business_add1	=		$sale_details['locAddress1'];
	$business_add2	=		$sale_details['locAddress2'];
  $city_name      =   $sale_details['locCityName'];
  $state_name     =   Utilities::get_location_state_name($sale_details['locStateID']);
  $phones         =   $sale_details['locPhones'];
  $pincode        =   $sale_details['locPincode'];
  $business_add3  =   $city_name.', '.$state_name.' - '.$pincode;
  $gst_no         =   $sale_details['locGstNo'];

  $card_no        =   $sale_details['cardNo'] > 0 ? '* ****'.$sale_details['cardNo'] : '';
  $auth_code      =   $sale_details['authCode'] > 0 ? $sale_details['authCode'] : '****';

  $wallet_id      =   $sale_details['walletID'];
  $wallet_ref_no  =   $sale_details['walletRefNo'];
  $wallets        =   Constants::$WALLETS;
  if($wallet_id > 0) {
    $wallet_name  =   $wallets[$wallet_id];
  } else {
    $wallet_name  =   '';
  }

  $cn_no          =   $sale_details['cnNo'];
  $referral_no    =   $sale_details['refCardNo'];
  $promo_code     =   $sale_details['promoCode'];
  $exe_name       =   $sale_details['executiveName'] !== '' ? substr($sale_details['executiveName'],0,20) : '';

  if($customer_name === '') {
    $customer_name = $tmp_cust_name;
  }
  $tandc_a        =   preg_split('/\r\n|[\r\n]/', $sale_details['tacB2C']);
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<style>
      #printDiv {
        font-size: 12px;
      }
			@media print {
			  a {
			   	display:none;
			  }
			}		
		</style>
	</head>
	<body>
		<div id="printDiv">
			<div style="font-family: monospace; width:290px;">
	  		<h3 style="margin:0 0 0 0px;text-align:center;font-size:20px;"><?php echo $business_name ?></h3>
	  		<h6 style="margin:0 0 0 0px;text-align:center;font-size:10px;"><?php echo $business_add1 ?></h6>
	  		<?php if($business_add2 !== ''): ?>
	  			<h6 style="margin:0 0 0 0px; text-align:center;font-size:11px;"><?php echo $business_add2 ?></h6>
	  		<?php endif; ?>
        <?php if($business_add3 !== ''): ?>
          <h6 style="margin:0 0 0 0px; text-align:center;font-size:11px;"><?php echo $business_add3 ?></h6>
        <?php endif; ?>
        <?php if($phones !== ''): ?>
          <h6 style="margin:0 0 0 0px; text-align:center;font-size:11px;"><?php echo 'Phone(s): '.$phones ?></h6>
        <?php endif; ?>        
    		<?php if($gst_no !== ''): ?>
          <h3 style="margin: 0px 0 0px 0px;text-align:center;border-top: 1px dotted #000;font-size:18px;">Tax Invoice</h3>
          <h3 style="margin: 0px 0 0px 0px;text-align:center;font-size:12px;">GSTIN: <?php echo $gst_no ?></h3>
        <?php else: ?>
          <h3 style="margin: 0px 0 0px 0px;text-align:center;border-top: 1px dotted #000;font-size:18px;">Bill of Sale</h3>
        <?php endif; ?>
	  		<h3 style="margin: 0px 0 0px 0px;text-align:center;border-top: 1px dotted #000;font-size:14px;">Bill No : <?php echo $bill_no ?></h3>
	  		<h5 style="text-align:center;margin:0 0 0 0px;font-size:12px;">Bill date &amp; time: <?php echo $bill_date.', '.$bill_time ?></h5>
        <?php if($exe_name !== ''): ?>
          <h5 style="text-align:center;margin:0 0 0 0px;font-size:11px;">Executive Name: <?php echo $exe_name ?></h5>
        <?php endif; ?>
        <?php if($customer_name !== ''): ?>
	  		  <h5 style="text-align:center;margin:0 0 0 0px;font-size:12px;">Customer name: <?php echo $customer_name ?></h5>
        <?php endif; ?>
        <?php if((int)$referral_no > 0): ?>
          <h5 style="text-align:center;margin:0 0 0 0px;font-size:14px;">REFERRAL code: <?php echo $referral_no ?></h5>
        <?php endif; ?>
        <?php if($promo_code !== ''): ?>
          <h5 style="text-align:center;margin:0 0 0 0px;font-size:14px;">PROMO CODE: <?php echo $promo_code ?></h5>
        <?php endif; ?>        
			  <table style="width: 100%;" cellpadding=0 cellspacing=0>
			    <thead>
			      <tr>
			        <th style="border-top:1px dotted #000;font-size:14px;text-align:left;font-weight:bold;" colspan="4">Item Name</th>
			      </tr>
						<tr>
			        <th style="text-align:left;border-bottom: 1px dotted #000;font-size:14px;font-weight:bold;">Qty.</th>
			        <th style="text-align:left;border-bottom: 1px dotted #000;font-size:14px;font-weight:bold;">Rate</th>
			        <th style="text-align:center;border-bottom: 1px dotted #000;font-size:14px;font-weight:bold;">Amount</th>
			        <th style="text-align:right;border-bottom: 1px dotted #000;font-size:14px;font-weight:bold;">Disc.</th>			        
						</tr>			      
			    </thead>
			    <tbody>
				    <?php
				    	$slno = 0;
				    	$tax_amounts = [];
              $taxable_values = $taxable_gst_value = [];
              $total_qty = 0;
					    foreach($sale_item_details as $item_details) {
					      $slno++;
					      $mrp = $item_details['mrp'];
					      $amount = $item_details['itemQty'] * $item_details['mrp'];
					      $base_price = $item_details['itemQty'] * $item_details['itemRate'];
					      $discount = $item_details['discountAmount'];
					      $tax_percent = $item_details['taxPercent'];
					      $tax_amount = ($item_details['cgstAmount'] + $item_details['sgstAmount']);
                $total_qty += $item_details['itemQty'];

                if(isset($taxable_values[$tax_percent])) {
                  $taxable = $taxable_values[$tax_percent] + ($base_price);
                  $gst_value = $taxable_gst_value[$tax_percent] + $tax_amount;

                  $taxable_values[$tax_percent] = $taxable;
                  $taxable_gst_value[$tax_percent] = $gst_value;
                } else {
                  $taxable_values[$tax_percent] = ($base_price);
                  $taxable_gst_value[$tax_percent] = $tax_amount;
                }
					  ?>
				      <tr><td colspan="4" style="font-size:14px;font-weight:bold;"><?php echo $item_details['itemName'] ?></td></tr>
				      <tr style="font-weight:bold;">
				        <td style="text-align:left;font-size:12px;"><?php echo $item_details['itemQty'] ?></td>
				        <td style="text-align:left;font-size:12px;"><?php echo $mrp ?></td>
				        <td style="text-align:right;font-size:12px;"><?php echo number_format($amount,2,'.','') ?></td>
				        <td style="text-align:right;font-size:12px;"><?php echo $discount ?></td>				        
				      </tr>
				    <?php } ?>
            <tr>
              <td style="text-align:left;border-top:1px dotted #000;font-size:14px;font-weight:bold;" colspan="2">Total Items:</td>
              <td style="text-align:left;border-top:1px dotted #000;font-size:18px;font-weight:bold;" colspan="2"><?php echo $total_qty ?></td>
              <td style="border-top:1px dotted #000;font-weight:bold;">&nbsp;</td>
              <td style="border-top:1px dotted #000;font-weight:bold;">&nbsp;</td>
            </tr>
            <tr>
              <td style="text-align:left;border-top:1px dotted #000;font-size:12px;font-weight:bold;">Total</td>
              <td style="text-align:left;border-top:1px dotted #000;font-size:12px;text-align:right;font-weight:bold;">Disc.</td>
              <td style="border-top:1px dotted #000;text-align:right;font-weight:bold;">GrandTot.</td>
              <td style="border-top:1px dotted #000;text-align:right;font-weight:bold;">R.Off</td>
            </tr>
            <tr>
              <td style="text-align:left;border-top:1px dotted #000;font-size:12px;font-weight:bold;"><?php echo number_format($bill_amount,2,'.','') ?></td>
              <td style="text-align:left;border-top:1px dotted #000;font-size:12px;font-weight:bold;text-align:right;"><?php echo $bill_discount ?></td>
              <td style="border-top:1px dotted #000;text-align:right;font-weight:bold;"><?php echo number_format($bill_amount - $bill_discount,2,'.','') ?></td>
              <td style="border-top:1px dotted #000;text-align:right;font-weight:bold;"><?php echo $total_amt_r ?></td>
            </tr>
				    <tr>
				    	<td colspan="3" style="text-align:right;border-top:1px dotted #000;border-bottom:1px dotted #000;font-size:14px;font-weight:bold;">Net pay:</td>
				    	<td style="text-align:right;border-bottom:1px dotted #000;border-top:1px dotted #000;font-size:17px;font-weight:bold;"><?php echo $net_pay ?></td>
				    </tr>
            <tr>
              <td colspan="4" style="text-align:center;border-bottom:1px dotted #000;border-top:1px dotted #000;font-size:14px;font-weight:bold;">[ <?php echo Utilities::get_indian_currency($net_pay) ?> ]</td>
            </tr>
			   	</tbody>
			  </table>
        <?php if($gst_no !== ''): ?>
          <table style="width:100%;font-size:12px;" cellpadding="0" cellspacing="0">
            <tr>
              <td style="text-align:center;border-bottom:1px dotted #000;font-size:14px;font-weight:bold;">GST Summary</td>
            </tr>
          </table>
          <table style="width:100%;font-size:12px;" cellpadding="0" cellspacing="0">
           <thead>
              <th style="text-align:left;font-size:11px;font-weight:bold;">GST %</th>
              <th style="text-align:left;font-size:11px;font-weight:bold;">Taxable (Rs.)</th>
              <th style="text-align:left;font-size:11px;font-weight:bold;">IGST</th>
              <th style="text-align:left;font-size:11px;font-weight:bold;">CGST</th>
              <th style="text-align:left;font-size:11px;font-weight:bold;">SGST</th>
           </thead>
           <tbody>
              <?php
              	$taxes = array_keys($taxable_values);
                $tot_taxable_value = $tot_igst_amount = $tot_cgst_amount = $tot_sgst_amount = 0;                        
                foreach($taxes as $tax_code => $tax_percent):
                  if( isset($taxable_values[$tax_percent]) ) {
                    $taxable_value = $taxable_values[$tax_percent];
                    $tot_taxable_value += $taxable_value;
                  } else {
                    $taxable_value = 0;
                  }
                  if(isset($taxable_gst_value[$tax_percent])) {
                    $cgst_amount = $sgst_amount = round($taxable_gst_value[$tax_percent]/2,2);
                    $igst_amount = 0;
                  }                          
              ?>
                <tr>
                    <td class="font11" style="text-align:left;font-size:11px;font-weight:bold;"><?php echo number_format($tax_percent, 2).'%' ?></td>
                    <td class="font11" style="text-align:left;font-size:11px;font-weight:bold;" id="taxable_<?php echo $tax_code ?>_amount"><?php echo number_format($taxable_value,2,'.','') ?></td>
                    <td class="font11" style="text-align:left;font-size:11px;font-weight:bold;" id="taxable_<?php echo $tax_code ?>_igst_value"><?php echo number_format($igst_amount,2,'.','')  ?></td>
                    <td class="font11" style="text-align:left;font-size:11px;font-weight:bold;" id="taxable_<?php echo $tax_code ?>_cgst_value"><?php echo number_format($cgst_amount,2,'.','') ?></td>
                    <td class="font11" style="text-align:left;font-size:11px;font-weight:bold;" id="taxable_<?php echo $tax_code ?>_sgst_value"><?php echo number_format($sgst_amount,2,'.','') ?></td>
                </tr>
              <?php endforeach; ?>
           </tbody>
          </table>
        <?php endif; ?>
        <table style="width:100%;font-size:12px;" cellpadding="0" cellspacing="0">
          <tr>
            <td colspan="5" style="text-align:center;border-top:1px dotted #000;font-size:14px;font-weight:bold;border-left:1px dotted #000;border-right:1px dotted #000;">Payment Details</td>
          </tr>
        </table>
        <table style="width:100%;font-size:10px;" cellpadding="0" cellspacing="0">
         <thead>
            <th style="text-align:center;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-top:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">Paid through</th>
            <th style="text-align:center;font-size:11px;width:40%;border-right:1px dotted #000;border-top:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">Details</th>
            <th style="text-align:center;font-size:11px;width:10%;border-right:1px dotted #000;border-top:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">Amount(Rs.)</th>
         </thead>
         <tbody>
          <?php if($payment_method === 0): ?>
            <tr>
              <td style="text-align:left;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">By CASH</td>
              <td style="text-align:left;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">&nbsp;</td>
              <td style="text-align:right;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;"><?php echo number_format($sale_details['netPay'], 2, '.', '') ?></td>
            </tr>
            <tr>
              <td style="text-align:left;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">CARD</td>
              <td style="text-align:left;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">&nbsp;</td>
              <td style="text-align:center;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">-----</td>
            </tr>
            <tr>
              <td style="text-align:left;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">CREDITVOC</td>
              <td style="text-align:left;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">&nbsp;</td>
              <td style="text-align:center;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">-----</td>                
            </tr>
            <tr>
              <td style="text-align:left;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">eWallet/UPI/EMIC</td>
              <td style="text-align:left;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">&nbsp;</td>
              <td style="text-align:center;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">-----</td>                
            </tr>            
          <?php endif; ?>
          <?php if($payment_method === 1): ?>
            <tr>
              <td style="text-align:left;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">CASH</td>
              <td style="text-align:left;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">&nbsp;</td>
              <td style="text-align:center;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">-----</td>                
            </tr>
            <tr>
              <td style="text-align:left;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">CARD</td>
              <td style="text-align:left;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;"><?php echo 'Card: '.$card_no .' <br /> Appr.Code: '.$auth_code ?></td>
              <td style="text-align:right;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;"><?php echo number_format($sale_details['netPay'], 2, '.', '') ?></td>
            </tr>
            <tr>
              <td style="text-align:left;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">CREDITVOC</td>
              <td style="text-align:left;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">&nbsp;</td>
              <td style="text-align:center;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">-----</td>                
            </tr>
            <tr>
              <td style="text-align:left;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">eWallet/UPI/EMIC</td>
              <td style="text-align:left;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">&nbsp;</td>
              <td style="text-align:center;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">-----</td>                
            </tr>            
          <?php endif; ?>
          <?php if($payment_method === 2): ?>
            <tr>
              <td style="text-align:left;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">CASH</td>
              <td style="text-align:left;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">&nbsp;</td>
              <td style="text-align:right;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;"><?php echo $sale_details['netPayCash'] > 0 ? number_format($sale_details['netPayCash'], 2, '.', ''): '' ?></td>  
            </tr>
            <tr>
              <td style="text-align:left;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">CARD</td>
              <?php if($sale_details['netPayCard'] > 0): ?>
                <td style="text-align:left;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;"><?php echo 'Card: '.$card_no .' <br /> Appr.Code: '.$auth_code ?></td>
                <td style="text-align:right;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;"><?php echo number_format($sale_details['netPayCard'], 2, '.', '') ?></td>  
              <?php else: ?>
                <td style="text-align:center;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">&nbsp;</td>
                <td style="text-align:center;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">-----</td>
              <?php endif; ?>
            </tr>
            <tr>
              <td style="text-align:left;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">CREDITVOC</td>
              <?php if($sale_details['netPayCn'] > 0): ?>
                <td style="text-align:left;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;"><?php echo 'CNN:'.$cn_no ?></td>
                <td style="text-align:right;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;"><?php echo number_format($sale_details['netPayCn'], 2, '.', '') ?></td>  
              <?php else: ?>
                <td style="text-align:center;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">&nbsp;</td>
                <td style="text-align:center;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">-----</td>
              <?php endif; ?>   
            </tr>
            <tr>
              <td style="text-align:left;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">eWallet/UPI/EMIC</td>
              <?php if($sale_details['netPayWallet'] > 0): ?>
                <td style="text-align:left;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;"><?php echo $wallet_name ?></td>
                <td style="text-align:right;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;"><?php echo number_format($sale_details['netPayWallet'], 2, '.', '') ?></td>
              <?php else: ?>
                <td style="text-align:center;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">&nbsp;</td>
                <td style="text-align:center;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">-----</td>
              <?php endif; ?>              
            </tr>            
          <?php endif; ?>
          <?php if($payment_method === 3): ?>
            <tr>
              <td style="text-align:center;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;" colspan="3"><?php echo '** CREDIT SALE **' ?></td>
            </tr>          
          <?php endif; ?>          
          <?php if($payment_method === 4): ?>
            <tr>
              <td style="text-align:left;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">CASH</td>
              <td style="text-align:left;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">&nbsp;</td>
              <td style="text-align:center;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">-----</td>                
            </tr>
            <tr>
              <td style="text-align:left;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">CARD</td>
              <td style="text-align:left;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">&nbsp;</td>
              <td style="text-align:center;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">-----</td>
            </tr>
            <tr>
              <td style="text-align:left;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">CREDITVOC</td>
              <td style="text-align:left;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">&nbsp;</td>
              <td style="text-align:center;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">-----</td>
            </tr>
            <tr>
              <td style="text-align:left;font-size:11px;width:40%;border-left:1px dotted #000;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;">eWallet/UPI/EMIC</td>
              <td style="text-align:left;font-size:11px;width:40%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;"><?php echo $wallet_name ?></td>
              <td style="text-align:center;font-size:11px;width:20%;border-right:1px dotted #000;border-bottom:1px dotted #000;font-weight:bold;"><?php echo number_format($sale_details['netPay'], 2, '.', '') ?></td>
            </tr>
          <?php endif; ?>

          <?php if(count($tandc_a) > 0) { ?>
            <tr>
              <td colspan="3" style="font-size:14px;font-weight:bold;padding-top:10px;text-decoration: underline;">Terms &amp; Conditions</td>
            </tr>
            <tr>
              <td colspan="3" style="font-size:12px;font-weight:bold;padding-top:5px;">
                <?php
                  foreach($tandc_a as $key => $tandc) {
                    echo '<span>'.$tandc.'</span><br />';
                  }
                ?>
              </td>
            </tr>
          <?php } ?>
          <tr><td colspan="3">&nbsp;</td></tr>  
         </tbody>
        </table>
	  	<div>
	  </div>
	  <h6 style="margin-top:5px;text-align:center;padding:0;font-size:9px;border-top:1px solid #000">Powered by QwikBills.com - Cloud based Billing &amp; Inventory Solution.</h6>
	  <br />
	  <a href="javascript: window.print();window.close();">Print</a>
	  <a href="javascript: window.close();" style="padding-left:150px;">(x) Close</a>	  
	</body>
</html>

<?php exit; ?>