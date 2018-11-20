<?php

namespace ClothingRm\Finance\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Finance 
{
	public function create_bank($params=array()) {
		$client_id = Utilities::get_current_client_id();
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','fin/bank-names/'.$client_id,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'bankCode' => $response['response']['bankCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function update_bank($params=array(),$bank_code='') {
		$client_id = Utilities::get_current_client_id();
		$api_url = 'fin/bank-names/'.$bank_code.'/'.$client_id;
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put',$api_url,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	public function get_bank_details($bank_code='') {
		$client_id = Utilities::get_current_client_id();
		$api_url = 'fin/bank-names/'.$bank_code.'/'.$client_id;
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$api_url,array());
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'bankDetails' => $response['response']['bankDetails']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}

	public function banks_list() {
		$client_id = Utilities::get_current_client_id();
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','fin/bank-names/'.$client_id);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'banks'=>$response['response']['banks']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}		
	}

	public function create_payment_voucher($form_data=array()) {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','fin/payments/'.$client_id, $form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'vocNo'=>$response['response']['vocNo']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	public function update_payment_voucher($form_data=array(),$voc_no='') {
		$client_id = Utilities::get_current_client_id();
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put','fin/payments/'.$voc_no.'/'.$client_id,$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}
	}

	public function delete_payment_voucher($voc_no = '') {
		$client_id = Utilities::get_current_client_id();
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('delete','fin/payments/'.$voc_no.'/'.$client_id,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}
	}

	public function get_payment_vouchers_list($search_params=array()) {
		$client_id = Utilities::get_current_client_id();
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','fin/payments/'.$client_id,$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	public function get_payment_voucher_details($voucher_no=0) {
		$client_id = Utilities::get_current_client_id();
		$end_point = 'fin/payments/'.$voucher_no.'/'.$client_id;
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,array());
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response['response']['vocDetails']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	public function get_receivables_ason($page_no=1, $per_page=10) {
		$client_id = Utilities::get_current_client_id();
		$end_point = 'reports/receivables-ason/'.$client_id;

		$query_params = array(
			'pageNo' => $page_no,
			'perPage' => $per_page,
		);

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,$query_params);
		$status = $response['status'];
		if($status === 'success') {
      $total_receivables = $response['response']['receivables'];
      $total_pages = $response['response']['total_pages'];
      if($total_pages>1) {
        for($i=2;$i<=$total_pages;$i++) {
          $query_params['pageNo'] = $i;
          $response = $api_caller->sendRequest('get',$end_point,$query_params);
          if($response['status'] === 'success') {
            $total_receivables = array_merge($total_receivables,$response['response']['receivables']);
          }
        }
      }
			return array('status'=>true,'receivables'=>$total_receivables);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}		
	}

	public function get_debtors() {
		$client_id = Utilities::get_current_client_id();
		$end_point = 'fin/debtors-list/'.$client_id;
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response['response']['debtors']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	/**************************** receipts apis ******************************************/
	public function create_receipt_voucher($form_data=array()) {
		$client_id = Utilities::get_current_client_id();
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post','fin/receipts/'.$client_id, $form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'vocNo'=>$response['response']['vocNo']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	public function update_receipt_voucher($form_data = [], $voc_no = '') {
		$client_id = Utilities::get_current_client_id();
		// call api.
		$api_caller = new ApiCaller();
		$api_uri = 'fin/receipts/'.$voc_no.'/'.$client_id;
		$response = $api_caller->sendRequest('put', $api_uri, $form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}
	}

	public function get_receipt_vouchers_list($search_params=array()) {
		$client_id = Utilities::get_current_client_id();
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get','fin/receipts/'.$client_id,$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	public function get_receipt_voucher_details($voucher_no=0) {
		$client_id = Utilities::get_current_client_id();
		$end_point = 'fin/receipts/'.$voucher_no.'/'.$client_id;
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,array());
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'data'=>$response['response']['vocDetails']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}

	public function delete_receipt_voucher($voc_no = '') {
		$client_id = Utilities::get_current_client_id();
		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('delete','fin/receipts/'.$voc_no.'/'.$client_id,[]);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true);
		} elseif($status === 'failed') {
			return array('status'=>false,'apierror'=>$response['reason']);
		}
	}	

	public function cash_book($from_date='', $to_date='') {
		$client_id = Utilities::get_current_client_id();
		$end_point = 'fin/cash-book/'.$client_id;

		$search_params = array(
			'fromDate' => $from_date,
			'toDate' => $to_date,
		);

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,$search_params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'records'=>$response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}		
	}

	// get payables
	public function get_payables($form_data=[]) {
		$client_id = Utilities::get_current_client_id();
		$end_point = 'reports/payables';

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$end_point,$form_data);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'response'=>$response['response']);
		} elseif($status === 'failed') {
			return array('status'=>false, 'apierror'=>$response['reason']);
		}
	}
}