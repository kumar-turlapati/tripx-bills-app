<?php

namespace ClothingRm\SalesCombos\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class SalesCombos
{
	/** create promo offer in the system **/
	public function create_sales_combo($params=[]) {
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'sales-combo/create';

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('post',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'comboCode' => $response['response']['comboCode']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	/** get combo details **/
	public function get_combo_details($combo_code='') {
		$request_uri = 'sales-combo/details/'.$combo_code;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,[]);
		$status = $response['status'];
		if($status === 'success') {
			return array('status' => true,'comboDetails' => $response['response']['comboDetails']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}	

	/** update promo offer in the system **/
	public function updatePromoOffer($params = [], $offer_code='', $location_code='') {
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'promo-offers/update/'.$offer_code.'/'.$client_id.'?lc='.$location_code;
		
		# call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('put',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'updatedRows' => $response['response']['updated']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}		
	}

	/** list promo offers in the system **/
	public function getAllPromoOffers($params=[]) {
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'promo-offers/list/'.$client_id;

		// call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$params);
		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'response' => $response['response']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

	/** get live promo offers from the portal **/
	public function getLivePromoOffers($params = []) {
		$client_id = Utilities::get_current_client_id();
		$request_uri = 'promo-offers/live/'.$client_id;

		$params['locationCode'] = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';

		# call api.
		$api_caller = new ApiCaller();
		$response = $api_caller->sendRequest('get',$request_uri,$params);

		$status = $response['status'];
		if ($status === 'success') {
			return array('status'=>true,'response' => $response['response']);
		} elseif($status === 'failed') {
			return array('status' => false, 'apierror' => $response['reason']);
		}
	}

}