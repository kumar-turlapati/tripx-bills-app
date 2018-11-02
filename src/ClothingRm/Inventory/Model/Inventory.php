<?php

namespace ClothingRm\Inventory\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Inventory
{
	private $api_caller;

	public function __construct() {
		$this->api_caller = new ApiCaller();
	}
	
	public function get_available_qtys($search_params=array()) {

		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inventory/qty-available/'.$client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$search_params);

		// dump($search_params);
		// exit;

		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'items' => $response['response']['batchqtys']['results'], 
				'total_pages' => $response['response']['batchqtys']['total_pages'],
				'total_records' => $response['response']['batchqtys']['total_records'],
				'record_count' =>  $response['response']['batchqtys']['this_page']
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	public function get_inventory_item_details($search_params=array()) {

		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inventory/item-details/'.$client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$search_params);

		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'item_details' => $response['response'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}		
	}

	public function get_stock_report($params=[]) {
		$request_uri = 'reports/stock-report';
		// call api.
		$response = $this->api_caller->sendRequest('get',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'results' => $response['response'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}	
	}

/*	public function get_stock_report_new($params) {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'reports/stock-report-new/'.$client_id;
		
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$params);

		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'results' => $response['response'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}	
	}
*/
	public function get_expiry_report($params=array(),$page_no=1) {

		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'reports/expiry-report/'.$client_id;

		$params['pageNo'] = $page_no;


		
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$params);
		$status = $response['status'];

		// dump($params);
		// echo '<pre>';
		// print_r($response);
		// echo '</pre>';
		// exit;

		if ($status === 'success') {
			return array(
				'status' => true, 
				'items' => $response['response']['results'], 
				'total_pages' => $response['response']['total_pages'],
				'total_records' => $response['response']['total_records'],
				'record_count' =>  $response['response']['this_page']				
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}	
	}	

	public function trash_expired_items($params=array()) {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inventory/trash-expired-items/'.$client_id;
		
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'results' => $response['response']['processed'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}		
	}

	public function get_stock_adj_report($params=array(),$page_no=1) {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'reports/stock-adj-report/'.$client_id;
		
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'results' => $response['response'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}		
	}

	public function get_material_movement($params=array()) {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'reports/material-movement/'.$client_id;
		
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'results' => $response['response'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}		
	}

	/**
	 * track item movement.
	**/
	public function track_item($params = array()) {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inventory/track-item/'.$client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$params);

		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,
				'items' => $response['response'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false,
				'apierror' => $response['reason']
			);
		}
	}

	public function io_analysis($params=array()) {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'reports/io-analysis/'.$client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$params);

		// collect response
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,
				'response' => $response['response'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false,
				'apierror' => $response['reason']
			);
		}		
	}

	public function item_master_with_pp($params=array()) {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = '/reports/inventory/items-list/'.$client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$params);

		// collect response
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,
				'response' => $response['response'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false,
				'apierror' => $response['reason']
			);
		}		
	}	


	/********************************** Threshold Items Qtys.**********************************
	*******************************************************************************************/
	
	public function add_threshold_qty($params=array()) {

		$valid_result = $this->_validate_th_formdata($params);
		if($valid_result['status'] === false) {
			return $valid_result;
		}

		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inventory/threshold-invqty/'.$client_id;
		
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'thrCode' => $response['response']['thrCode'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	public function update_threshold_qty($params=array(),$thr_code='') {

		$valid_result = $this->_validate_th_formdata($params);
		if($valid_result['status'] === false) {
			return $valid_result;
		}

		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inventory/threshold-invqty/'.$client_id.'/'.$thr_code;
		
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}	

	public function list_threshold_qtys($params=array()) {

		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inventory/threshold-invqty/'.$client_id;
		
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'results' => $response['response'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	public function get_threshold_itemqty_details($thr_code) {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inventory/threshold-invqty/'.$client_id.'/'.$thr_code;
		
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,array());
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'thrDetails' => $response['response']['thrDetails'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}		
	}

	public function get_item_thrlevel($search_params=array()) {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'reports/item-thrlevel/'.$client_id;
		
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'response' => $response['response'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}		
	}

	public function update_batch_qtys($params=array()) {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inventory/update-batch-qtys/'.$client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put',$request_uri,$params);
		$status = $response['status'];
		// dump($response);
		// exit;
		
		if($status === 'success') {
			return array(
				'status' => true,  
				'results' => $response['response']['processed'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	/** inventroy profitability **/
	public function inventory_profitability($search_params = array()) {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'reports/inventory-profitability/'.$client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$search_params);
		$status = $response['status'];		
		if($status === 'success') {
			return array(
				'status' => true,  
				'results' => $response['response'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	# stock adjustments
	public function add_stock_adjustment($params=array()) {

		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inventory/stock-adjustment/'.$client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'results' => $response['response']['response'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	# update stock adjustment
	public function update_stock_adjustment($params=array(), $adj_code='') {

		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inventory/stock-adjustment';

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'results' => $response['response'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	# get adjustment entry reasons
	public function get_inventory_adj_reasons($params=array()) {

		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inventory/stock-adjustment-reasons';
		
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'results' => $response['response'],
			);				
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}		
	}

	# get inventory adjustment entries.
	public function get_inventory_adj_entries($params=array()) {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'inventory/stock-adjustments-list/'.$client_id;
		
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,
				'results' => $response['response'],
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	# delete inventory adjustment
	public function delete_stock_adjustment($adj_code='') {
		$client_id = Utilities::get_current_client_id();
		$end_point = '/inventory/stock-adjustment/'.$client_id.'/'.$adj_code;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('delete',$end_point,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function update_cat_brand($form_data=[]) {
		$end_point = '/inventory/update-cat-brand';
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$end_point,$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}

}