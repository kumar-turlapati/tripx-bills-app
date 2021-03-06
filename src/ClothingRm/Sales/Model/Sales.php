<?php

namespace ClothingRm\Sales\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Sales {

	// create a sales transaction
	public function create_sale($params = []) {
		$params['clientID'] = Utilities::get_current_client_id();
		
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','sales-entry',$params);

		$status = $response['status'];
		if($status === 'success') {
			return array('status' => true,'invoiceCode' => $response['response']['invoiceCode'], 'billNo' => $response['response']['billNo'] );
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	// update a sales transaction
	public function update_sale($params = [], $invoice_code='') {
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put', 'sales-entry/'.$invoice_code, $params);
		$status = $response['status'];
		if($status === 'success') {
			return array('status' => true,'invoiceCode' => $response['response']['invoiceCode'], 'billNo' => $response['response']['billNo'] );
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}	

	// day sales register
	public function get_sales($page_no=1, $per_page=200, $search_params=[]) {
		$client_id = Utilities::get_current_client_id();

		$search_params['pageNo'] = $page_no;
		$search_params['perPage'] = $per_page;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','sales-register/'.$client_id,$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,
				'sales' => $response['response']['sales'], 
				'total_pages' => $response['response']['total_pages'],
				'total_records' => $response['response']['total_records'],
				'record_count' =>  $response['response']['this_page'],
				'query_totals' => $response['response']['query_totals'],
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	// get sales transaction details
	public function get_sales_details($invoice_code='', $by_bill_no=false, $location_code='') {
		// fetch client id
		$client_id = Utilities::get_current_client_id();
		if($by_bill_no) {
			$params['billNo'] = $invoice_code;
			$params['locationCode'] = $location_code;
		} else {
			$params['invoiceCode'] = $invoice_code;
		}

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','sales-entry/'.$client_id,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'saleDetails' => $response['response']['saleDetails'],
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	// get monthwise sales summary by tax rate
	public function get_sales_summary_bymon_tax_report($params=[]) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','tax-reports/sales-abs-mon/tax-rate-wise',$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'summary' => $response['response'],
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	// get monthwise sales summary by hsn/sac code
	public function get_sales_summary_by_hsnsac_code($params=[]) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','tax-reports/sales-abs-mon/hsn-code-wise',$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'summary' => $response['response'],
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}

	// get billwise itemwise sales register
	public function get_billwise_itemwise_sales($params=[]) {
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','reports/sales-billwise-itemwise',$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'summary' => $response['response'],
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}		

	// get monthwise sales summary
	public function get_sales_summary_bymon($params=[]) {
		$api_caller = new ApiCaller();
		$client_id = Utilities::get_current_client_id();		
		$response = $api_caller->sendRequest('get','reports/sales-abs-mon/'.$client_id,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,  
				'summary' => $response['response'],
			);
		} elseif($status === 'failed') {
			return array(
				'status' => false, 
				'apierror' => $response['reason']
			);
		}
	}	

	// get sales summary by day
	public function get_sales_summary_byday($search_params=[]) {
		$client_id = Utilities::get_current_client_id();
		$end_point = 'reports/daily-sales/'.$client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,
				'summary' => $response['response']['daySales'],
				'stock_balance' => $response['response']['stockBalance'],
				'stock_balance_mtd' => $response['response']['stockBalanceMtd'],
			);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	// get itemwise sales report
	public function get_itemwise_sales_report($search_params=[]) {
		$client_id = Utilities::get_current_client_id();
		$end_point = 'reports/daily-item-sales/'.$client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,
				'summary' => $response['response']
			);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	// search sales bills.
	public function search_sale_bills($search_params = []) {
		$client_id = Utilities::get_current_client_id();
		$end_point = 'sales-entry/search/'.$client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,
				'bills' => $response['response']['bills']
			);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	// Remove Sales Transaction.
	public function remove_sales_transaction($params=[]) {
		$client_id = Utilities::get_current_client_id();
		$end_point = 'sales-entry/'.$client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('delete',$end_point,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	// updating shipping
	public function update_shipping_info($cleaned_params=[], $sales_code='') {
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post', 'sales-entry/shipping-info/'.$sales_code, $cleaned_params);
		$status = $response['status'];
		if($status === 'success') {
			return array(
				'status' => true,
				'shippingStatusInfo' => $response['response']['shippingStatusInfo'], 
				'smsStatus' => isset($response['response']['smsStatus']) ? $response['response']['smsStatus'] : null,
				'smsFlag' => $response['response']['smsFlag'],
			);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	// sales dispatch register
	public function get_sales_dispatch_register($search_params=[]) {
		$end_point = 'reports/sales-dispatch-register';

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array(
				'status' => true,
				'dispatches' => $response['response']
			);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	// sales upi payments register
	public function get_sales_upi_payments_register($search_params=[]) {
		$end_point = 'reports/sales-upi-payments-register';
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,$search_params);
		$status = $response['status'];

		if ($status === 'success') {
			return array(
				'status' => true,
				'walletPayments' => $response['response']
			);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	// generate gatepass
	public function generate_gatepass($search_params=[]) {
		$end_point = 'gatepass';
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$end_point,$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true, 'gatePassNo' => $response['response']['gatePassNo']];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}
	}

	// cancel gatepass
	public function cancel_gatepass($invoice_code = '') {
		$params['invoiceCode'] = $invoice_code;
		$end_point = 'gatepass/cancel';
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$end_point,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status' => true, 'cancelledGatePassNo' => $response['response']['cancelledGatePassNo']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	// sales by filter
	public function get_sales_by_filter($search_params=[]) {
		$end_point = 'reports/sales-by-filter';
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return ['status' => true, 'sales' => $response['response']];
		} elseif($status === 'failed') {
			return ['status' => false, 'apierror' => $response['reason']];
		}		
	}

}
