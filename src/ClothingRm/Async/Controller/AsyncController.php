<?php 

namespace ClothingRm\Async\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\ApiCaller;

class AsyncController
{
    public function asyncRequestAction(Request $request) {

        $api_caller = new ApiCaller();
        $api_string = Utilities::clean_string($request->get('apiString'));
        $client_id = Utilities::get_current_client_id();
        $params = array();  

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
                if($by==='mobile') {
                    $params['searchBy'] = 'mobile';
                }
                $end_point = $this->_get_api_end_point($api_string).'/'.$client_id;
                $response = $api_caller->sendRequest('get',$end_point,$params,false,true);
                Utilities::print_json_response($response,false);
            }
        } elseif($api_string==='itemsAc') {
            $params['q'] = $request->get('a');
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
        } elseif($api_string==='add-thr-qty') {
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
            }
        } elseif($api_string==='day-sales') {
            $params['saleDate'] = date("d-m-Y");
            if(isset($_SESSION['utype']) && (int)$_SESSION['utype'] === 3) {
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
            echo $response;
        } elseif($api_string==='monthly-sales') {
            $params['month'] = Utilities::clean_string($request->get('saleMonth'));
            $params['year'] = Utilities::clean_string($request->get('saleYear'));
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
            $total_qty =  Utilities::clean_string($request->get('reqQty'));
            $response = Utilities::get_applicable_tax_percent($taxable_value, $total_qty);
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
            $barcode =  Utilities::clean_string($request->get('bc'));
            $locationCode = Utilities::clean_string($request->get('locationCode'));
            $skipLocation = !is_null($request->get('sl')) ? Utilities::clean_string($request->get('sl')) : false;
            $ind = !is_null($request->get('ind')) ? Utilities::clean_string($request->get('ind')) : false;            
            $api_url = 'barcode/'.$barcode;
            $params = ['locationCode' => $locationCode, 'skipLocation' => $skipLocation, 'ind' => $ind];
            $response = $api_caller->sendRequest('get',$api_url,$params,false);
            header("Content-type: application/json");
            if(is_array($response)) {
              echo json_encode($response);
            } else {
              echo $response;
            } 
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