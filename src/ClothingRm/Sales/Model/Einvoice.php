<?php

namespace ClothingRm\Sales\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class Einvoice {
	protected $api_caller;
	
	public function __construct() {
		$this->api_caller = new ApiCaller();
	}

	// retrieve gst details of a business.
	public function get_gst_details($gst_no_client='', $gst_no_customer='') {
		// call api.
		$response = $this->api_caller->sendRequestExternal('get',"einvoice/gstn/details/$gst_no_client/$gst_no_customer",[]);
		return $response;
	}

	// create an einvoice
	public function create_einvoice($payload=[], $seller_gst_no='') {
		// call api.
		$response = $this->api_caller->sendRequestExternal('post',"einvoice/$seller_gst_no",$payload);
		if(isset($response['status']) && $response['status'] === 'failed') {
			return ['status' => false, 'errorcode'=>'einv000', 'errortext'=>json_encode($response['error_message'])];
		} else {
			return $response;
		}
	}

	// cancel an einvoice
	public function cancel_einvoice($payload=[], $seller_gst_no='') {
		$response = $this->api_caller->sendRequestExternal('post',"einvoice/cancel/$seller_gst_no",$payload);
		if(isset($response['status']) && $response['status'] === 'failed') {
			return ['status' => false, 'errorcode'=>'einv000', 'errortext'=>json_encode($response['error_message'])];
		} else {
			return $response;
		}
	}

	// generate ewaybill from Irn
	public function generate_ewaybill($payload=[], $seller_gst_no='') {
		$response = $this->api_caller->sendRequestExternal('post',"einvoice/waybill/$seller_gst_no",$payload);
		if(isset($response['status']) && $response['status'] === 'failed') {
			return ['status' => false, 'errorcode'=>'einv000', 'errortext'=>json_encode($response['error_message'])];
		} else {
			return $response;
		}
	}

  // get einvoices
  public function get_all_einvoices($payload=[], $seller_gst_no='') {
    // call api.
    $response = $this->api_caller->sendRequestExternal('get',"einvoice/list/$seller_gst_no",$payload);
    if(isset($response['status']) && $response['status'] === 'failed') {
      return ['status' => false, 'errorcode'=>'einv000', 'errortext'=>json_encode($response['error_message'])];
    } else {
      return $response;
    }
  }

  // get einvoice details
  public function get_einvoice_details($gst_no='', $doc_no='') {
    // call api.
    $response = $this->api_caller->sendRequestExternal('get',"einvoice/$gst_no?docNo=$doc_no",[]);
    if(isset($response['status']) && $response['status'] === 'failed') {
      return ['status' => false, 'errorcode'=>'einv000', 'errortext'=>json_encode($response['error_message'])];
    } else {
      return $response;
    }
  }

}
