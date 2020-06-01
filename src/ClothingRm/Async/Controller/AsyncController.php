<?php 

namespace ClothingRm\Async\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\ApiCaller;

class AsyncController {
    
  public function asyncRequestAction(Request $request) {
    $api_caller = new ApiCaller();
    $api_string = Utilities::clean_string($request->get('apiString'));
    $client_id = Utilities::get_current_client_id();
    $params = [];

    if($api_string === 'getAvailableQty') {
      $params['itemName'] = Utilities::clean_string($request->get('itemname'));
      $params['locationCode'] = Utilities::clean_string($request->get('locationCode'));
      $end_point = 'inventory/available-qty';
      $response = $api_caller->sendRequest('get',$end_point,$params);
      header("Content-type: application/json");
      echo json_encode($response);
    } elseif($api_string==='getPatientDetails') {
      $ref_no = $request->get('refNo');
      $by = $request->get('by');
      $patient_details = array();
      if($ref_no !== '') {
        $params['regNo'] = $ref_no;
        if($by === 'mobile') {
          $params['searchBy'] = 'mobile';
        }
        $end_point = $this->_get_api_end_point($api_string).'/'.$client_id;
        $response = $api_caller->sendRequest('get',$end_point,$params,false,true);
        Utilities::print_json_response($response,false);
      }
    } elseif($api_string === 'itemsAc') {
      $params['q'] = Utilities::clean_string($request->get('a'));
      $params['locationCode'] = !is_null($request->get('locationCode')) ? Utilities::clean_string($request->get('locationCode')) : '';
      $response = $api_caller->sendRequest('get','products/ac',$params,false);
      if(count($response)>0 && is_array($response)) {
        echo implode($response,"\n");
      }
    } elseif($api_string==='poDetails') {
      $params['poNo'] = $request->get('poNo');
      $params['clientID'] = $client_id;
      $response = $api_caller->sendRequest('get','purchases',$params,false);
      if(is_array($response)){
        echo json_encode($response);
      } else {
        echo $response;
      }
/*    } elseif($api_string==='add-thr-qty') {
      $params['itemName'] = $request->get('mCode');
      $params['thrQty'] = $request->get('thQty');
      $params['byCode'] = true;
      $params['clientID'] = $client_id;
      if($params['thrQty']>=0) {
        $api_url = 'inventory/threshold-invqty/'.$client_id;
        $response = $api_caller->sendRequest('post',$api_url,$params,false);
        if(is_array($response)){
          echo json_encode($response);
        } else {
          echo $response;
        }
      }*/
    } elseif($api_string==='updateRackNo') {
      $params['itemCode'] = $request->get('itemCode');
      $params['rno'] = $request->get('rackNumber');
      $api_url = 'inventory/update-rack-no';
      $response = $api_caller->sendRequest('post',$api_url,$params,false);
      if(is_array($response)){
        echo json_encode($response);
      } else {
        echo $response;
      }
    } elseif($api_string==='day-sales') {
      $params['saleDate'] = date("d-m-Y");
      if(isset($_SESSION['utype']) && (int)$_SESSION['utype'] === 3 || (int)$_SESSION['utype'] === 9) {
        $params['locationCode'] = '';
      } elseif(isset($_SESSION['lc']) && $_SESSION['lc'] !== '') {
        $params['locationCode'] = $_SESSION['lc'];
      } else {
        header("Content-type: application/json");
        echo json_encode(['response' => false]);
        exit;
      }
      $api_url = 'reports/daily-sales/'.$client_id;
      $response = $api_caller->sendRequest('get',$api_url,$params,false);
      header("Content-type: application/json");
      if(is_array($response)) {
        echo json_encode($response);
      } else {
        echo $response;
      }
    } elseif($api_string==='monthly-sales') {
      $sale_month = Utilities::clean_string($request->get('saleMonth'));
      $sale_year = Utilities::clean_string($request->get('saleYear'));
      $no_of_days = Utilities::get_number_of_days_in_month($sale_month, $sale_year);

      $params['fromDate'] = '01-'.$sale_month.'-'.$sale_year;
      $params['toDate'] = $no_of_days.'-'.$sale_month.'-'.$sale_year;

      $api_url = 'reports/sales-abs-mon/'.$client_id;
      $response = $api_caller->sendRequest('get',$api_url,$params,false);
      header("Content-type: application/json");
      if(is_array($response)) {
        echo json_encode($response);
      } else {
        echo $response;
      }
    } elseif($api_string==='get-supplier-details' && ctype_alnum($request->get('c'))) {
      $params=[];
      $params['clientID'] = $client_id;
      $supplier_code = Utilities::clean_string($request->get('c'));
      $api_url = 'suppliers/details/'.$supplier_code;
      $response = $api_caller->sendRequest('get',$api_url,$params,false);
      header("Content-type: application/json");            
      if(is_array($response)) {
        echo json_encode($response);
      } else {
        echo $response;
      }                    
    } elseif($api_string === 'get-tax-percent') {
      $taxable_value = Utilities::clean_string($request->get('taxableValue'));
      $total_qty = Utilities::clean_string($request->get('reqQty'));
      $hsn_sac_code = Utilities::clean_string($request->get('hsn'));
      $domain = Utilities::clean_string($request->get('dm'));
      $response = Utilities::get_applicable_tax_percent($taxable_value, $total_qty, $hsn_sac_code, $domain);
      header("Content-type: application/json");
      echo json_encode($response);
    } elseif($api_string === 'get-ref-details') {
      $ref_code =  Utilities::clean_string($request->get('refCode'));
      $api_url = 'loyalty/member/details/'.$ref_code;
      $params['q'] = 'by_ref_code';
      $response = $api_caller->sendRequest('get',$api_url,$params,false);
      header("Content-type: application/json");
      if(is_array($response)) {
        echo json_encode($response);
      } else {
        echo $response;
      }
    } elseif($api_string === 'getItemDetailsByCode') {
      $barcode =  !is_null($request->get('bc')) ? Utilities::clean_string($request->get('bc')) : '';
      $locationCode = !is_null($request->get('locationCode')) ? Utilities::clean_string($request->get('locationCode')) : '';
      $skipLocation = !is_null($request->get('sl')) ? Utilities::clean_string($request->get('sl')) : false;
      $ind = !is_null($request->get('ind')) ? Utilities::clean_string($request->get('ind')) : false;
      $bch = !is_null($request->get('bch')) ? Utilities::clean_string($request->get('bch')) : false;
      $qty_zero = !is_null($request->get('qtyZero')) ? Utilities::clean_string($request->get('qtyZero')) : false;

      if(!is_null($request->get('sc'))) {
        $barcode = Utilities::clean_string($request->get('sc'));
        $sc = true;
      } else {
        $sc = false;
      }
      $api_url = 'barcode/'.$barcode;
      $params = ['locationCode' => $locationCode, 'skipLocation' => $skipLocation, 'ind' => $ind, 'bch' => $bch, 'qtyZero' => $qty_zero, 'sc' => $sc];
      $response = $api_caller->sendRequest('get',$api_url,$params,false);
      header("Content-type: application/json");
      if(is_array($response)) {
        echo json_encode($response);
      } else {
        echo $response;
      }
    } elseif($api_string === 'getItemBatchesByCode') {
      $item_name =  Utilities::clean_string($request->get('itemName'));
      $api_url = 'barcode/ac/get-batches';
      $params = ['itemName' => $item_name];
      $response = $api_caller->sendRequest('get',$api_url,$params,false);
      header("Content-type: application/json");
      if(is_array($response)) {
        echo json_encode($response);
      } else {
        echo $response;
      }
/*    } elseif($api_string === 'bcAc' && !is_null($request->get('a'))) {
      $params['q'] = Utilities::clean_string($request->get('a'));
      $response = $api_caller->sendRequest('get','barcode/ac',$params,false);
      if(count($response)>0 && is_array($response)) {
        echo implode($response,"\n");
      }*/
    } elseif($api_string === 'custAc' && !is_null($request->get('a'))) {
      $params['q'] = Utilities::clean_string($request->get('a'));
      $response = $api_caller->sendRequest('get','customers/ac/get-names',$params,false);
      if(is_array($response) && count($response)>0) {
        echo implode($response,"\n");
      }
    } elseif($api_string === 'itd' && !is_null($request->get('pn'))) {
      $params['pn'] = Utilities::clean_string($request->get('pn'));
      $params['locationCode'] = !is_null($request->get('locationCode')) ? Utilities::clean_string($request->get('locationCode')) : $_SESSION['lc'];
      $response = $api_caller->sendRequest('get','products/details-with-name',$params,false);
      header("Content-type: application/json");      
      if(is_array($response)) {
        echo json_encode($response);
      } else {
        echo $response;
      }
    } elseif($api_string === 'suppAc' && !is_null($request->get('a'))) {
      $params['q'] = Utilities::clean_string($request->get('a'));
      $response = $api_caller->sendRequest('get','suppliers/ac/get-names',$params,false);
      if(is_array($response) && count($response)>0) {
        echo implode($response,"\n");
      }
    } elseif($api_string === 'brandAc' && !is_null($request->get('a'))) {
      $params['q'] = Utilities::clean_string($request->get('a'));
      $params['locationCode'] = !is_null($request->get('locationCode')) ? Utilities::clean_string($request->get('locationCode')) : $_SESSION['lc'];
      $response = $api_caller->sendRequest('get','mfg/ac/get-names',$params,false);
      if(is_array($response) && count($response)>0) {
        echo implode($response,"\n");
      }
    } elseif($api_string === 'catAc' && !is_null($request->get('a'))) {
      $params['q'] = Utilities::clean_string($request->get('a'));
      $params['locationCode'] = !is_null($request->get('locationCode')) ? Utilities::clean_string($request->get('locationCode')) : $_SESSION['lc'];
      $response = $api_caller->sendRequest('get','categories/ac/get-names',$params,false);
      if(is_array($response) && count($response)>0) {
        echo implode($response,"\n");
      }
    } elseif($api_string === 'uomAc' && !is_null($request->get('a'))) {
      $params['q'] = Utilities::clean_string($request->get('a'));
      $params['locationCode'] = !is_null($request->get('locationCode')) ? Utilities::clean_string($request->get('locationCode')) : $_SESSION['lc'];
      $response = $api_caller->sendRequest('get','products/uoms/ac',$params,false);
      if(is_array($response) && count($response)>0) {
        echo implode($response,"\n");
      }
    } elseif($api_string === 'getBillNos' && !is_null($request->get('custName'))) {
      $params['customerName'] = urlencode(Utilities::clean_string($request->get('custName')));
      $response = $api_caller->sendRequest('get','fin/receipts-get-billnos',$params,false);
      header("Content-type: application/json");      
      if(is_array($response)) {
        echo json_encode($response);
      } else {
        echo $response;
      }
    } elseif($api_string === 'getSuppBillNos' && !is_null($request->get('suppCode'))) {
      $params['supplierCode'] = Utilities::clean_string($request->get('suppCode'));
      $response = $api_caller->sendRequest('get','fin/payments-get-billnos',$params,false);
      header("Content-type: application/json");      
      if(is_array($response)) {
        echo json_encode($response);
      } else {
        echo $response;
      }
    } elseif($api_string === 'finyDefault') {
      $response = $api_caller->sendRequest('get','finy/default',[],false);
      // var_dump($response);
      // exit;
      if(!is_array($response)) {
        $response = json_decode($response, true);
        Utilities::_set_fin_start_end_dates($response['response']);
      }
      echo 'QwikBills V.1.0';
    } elseif($api_string === 'auditQty') {
      $params = [];
      $params['itemCode'] = Utilities::clean_string($request->get('itemName'));
      $params['phyQty'] = Utilities::clean_string($request->get('phyQty'));
      $params['locationCode'] =  Utilities::clean_string($request->get('locationCode'));
      $params['auditCode'] = Utilities::clean_string($request->get('aC'));
      $api_url = 'stockaudit/upsert-item';
      $response = $api_caller->sendRequest('post',$api_url,$params,false);
      header("Content-type: application/json");
      if(is_array($response)) {
        echo json_encode($response);
      } else {
        echo $response;
      }
    } elseif($api_string === 'change-mrp') {
      $params['newMrp'] = Utilities::clean_string($request->get('newMrp'));
      $params['lotAndItem'] =  Utilities::clean_string($request->get('lotAndItem'));
      $api_url = 'inventory/change-mrp';
      $response = $api_caller->sendRequest('post',$api_url,$params,false);
      header("Content-type: application/json");
      if(is_array($response)) {
        echo json_encode($response);
      } else {
        echo $response;
      }
    } elseif($api_string === 'getComboItemDetails') {
      $form_data = $_POST;
      if(is_array($form_data) && count($form_data)>0) {
        /* hit api */
        $api_url  = 'sales-combo/process-sales-form';
        $response = $api_caller->sendRequest('post',$api_url,$form_data,false);
        header("Content-type: application/json");
        if(is_array($response)) {
          echo json_encode($response);
        } else {
          echo $response;
        }
      }
    } elseif($api_string === 'sdiscount') {
      $params = [];
      $in_lotno = isset($_POST['inLotNo']) ? Utilities::clean_string($_POST['inLotNo']) : '';
      $dp = isset($_POST['discountPercent']) && is_numeric($_POST['discountPercent']) ? Utilities::clean_string($_POST['discountPercent']) : 0; 
      $da = isset($_POST['discountAmount']) && is_numeric($_POST['discountAmount']) ? Utilities::clean_string($_POST['discountAmount']) : 0;
      $end_date = isset($_POST['endDate']) ? Utilities::clean_string($_POST['endDate']) : '';
      $location_code = isset($_POST['locationCode']) ? Utilities::clean_string($_POST['locationCode']) : '';
      if($in_lotno !== '' && $location_code !== '') {
        $in_lotno_a = explode('____', $in_lotno);
        if(is_array($in_lotno_a) && count($in_lotno_a) === 3) {
          $params['itemName'] = $in_lotno_a[0];
          $params['lotNo'] = $in_lotno_a[1];
          $params['mrp'] = $in_lotno_a[2];
          $params['locationCode'] = $location_code;
          if($dp > 0) {
            $params['discountAmount'] = round($params['mrp']*$dp/100, 2);
            $params['discountPercent'] = round($dp, 2);
          } elseif($da > 0) {
            $params['discountPercent'] = round($da/$params['mrp']*100, 2);
            $params['discountAmount'] = round($da, 2);
          }
          if($end_date !== '' && Utilities::validate_date($end_date)) {
            $params['endDate'] = $end_date;
          }
          // hit api
          $api_url = 'discount-rules';
          $response = $api_caller->sendRequest('post',$api_url,$params,false);
          header("Content-type: application/json");
          if(is_array($response)) {
            echo json_encode($response);
          } else {
            echo $response;
          }
        }
      }

    } elseif($api_string === 'getTrDetailsByCode') {
      $location_code =  Utilities::clean_string($request->get('locationCode'));
      $barcode = Utilities::clean_string($request->get('barcode'));
      $transfer_code = Utilities::clean_string($request->get('transferCode'));
      $api_url = 'stock-out/validate/'.$barcode.'/'.$location_code.'/'.$transfer_code;
      $response = $api_caller->sendRequest('post',$api_url,[],false);
      if(!is_array($response)) {
        $response = json_decode($response, true);
      }
     
      $scanned_qty = 0;

      // validate response.
      if($response['status'] === 'success') {
        // store the session id.
        $session_key = $response['response']['itemCode'].'__'.$response['response']['lotNo'];
        if( isset($_SESSION[$transfer_code][$session_key]['scanned']) ) {
          // prevent same barcode scanned infinite times to reach the goal.
          $allowed_qty_item = $_SESSION[$transfer_code][$session_key]['actual'];
          $scanned_qty_item = $_SESSION[$transfer_code][$session_key]['scanned'];
          $total_scanned = $_SESSION[$transfer_code]['total_scanned'];

          // echo $session_key;
          // echo json_encode($_SESSION[$transfer_code]);
          // exit;

          // var_dump($allowed_qty_item, $scanned_qty_item, $total_scanned);

          if($scanned_qty_item < $allowed_qty_item) {

            // this will control when the transfer qty is less than 
            // mOQ. If it is less than mOQ allowed qty. is taken.
            if($allowed_qty_item < $response['response']['mOq']) {
              $scanned_qty_item += $allowed_qty_item;
            } else {
              $scanned_qty_item += $response['response']['mOq'];
            }

            $_SESSION[$transfer_code][$session_key]['scanned'] = $scanned_qty_item;
            $total_scanned +=  $allowed_qty_item < $response['response']['mOq'] ? $allowed_qty_item : $response['response']['mOq'];

            $_SESSION[$transfer_code]['total_scanned'] = $total_scanned;

            $output = ['status' => 'success', 'qty' => $total_scanned];
          } else {
            $output = ['status' => 'failed', 'error' => 'Transferred Qty for this Barcode already reached. Further scanning is not allowed. / ఈ బార్ కోడ్  యొక్క స్కానింగ్ ముగిసింది. మరల ఈ బార్ కోడ్ స్కాన్ చేయబడదు.']; 
          }
        }
      } else {
        $output = ['status' => 'failed', 'error' => 'Barcode does not exists in this transfer./ ఈ బార్ కోడ్ ట్రాన్స్ఫర్ ఎంట్రీ లో లేదు.']; 
      }

      header("Content-type: application/json");
      echo json_encode($output);
    }
    exit;
  }

  protected function _get_api_end_point($resource=null) {
    switch($resource) {
      case 'getBatchNos':
        return 'inventory/batchnos';
        break;
      case 'getPatientDetails':
        return 'customers/ip-op-details';
        break;
      case '':
        return 'products/ac';
        break;
    }
  }
}
