<?php

namespace ClothingRm\SalesIndent\Model;

use Atawa\ApiCaller;
use Atawa\Utilities;

class SalesIndent {

  private $api_caller;
  
  public function __construct() {
    $this->api_caller = new ApiCaller;
  }
  
  public function create_sindent($form_data=[]) {
    $response = $this->api_caller->sendRequest('post','sindent',$form_data);
    $status = $response['status'];
    if($status === 'success') {
      return array('status'=>true,'indentCode' => $response['response']['indentCode'], 'indentNo' => $response['response']['indentNo']);
    } elseif($status === 'failed') {
      return array('status' => false, 'apierror' => $response['reason']);
    }
  }

  public function update_sindent($form_data=[], $indent_code='') {
    $response = $this->api_caller->sendRequest('put','sindent/'.$indent_code,$form_data);
    $status = $response['status'];
    if($status === 'success') {
      return array('status'=>true);
    } elseif($status === 'failed') {
      return array('status' => false, 'apierror' => $response['reason']);
    }
  }

  public function change_sindent_status($form_data, $indent_code='') {
    $response = $this->api_caller->sendRequest('put','sindent/change/status/'.$indent_code,$form_data);
    $status = $response['status'];
    if($status === 'success') {
      return array('status'=>true);
    } elseif($status === 'failed') {
      return array('status' => false, 'apierror' => $response['reason']);
    }
  }

  public function get_all_indents($filter_params=[]) {
    $response = $this->api_caller->sendRequest('get','sindent/register',$filter_params);
    $status = $response['status'];
    if($status === 'success') {
      return array('status'=>true,'response' => $response['response']);
    } elseif($status === 'failed') {
      return array('status'=>false, 'apierror' => $response['reason']);
    }
  }  

  public function get_indent_details($needle='', $by_code=false) {
    $filter_params['indentNo'] = $needle;
    if($by_code) {
      $filter_params['byCode'] = true;
    }
    $response = $this->api_caller->sendRequest('get','sindent/details',$filter_params);
    $status = $response['status'];
    if($status === 'success') {
      return array('status'=>true,'response' => $response['response']);
    } elseif($status === 'failed') {
      return array('status'=>false, 'apierror' => $response['reason']);
    }
  }

  public function get_indent_item_avail($filter_params=[]) {
    $response = $this->api_caller->sendRequest('get','reports/indent-item-avail-report',$filter_params);
    $status = $response['status'];
    if($status === 'success') {
      return array('status'=>true,'response' => $response['response']);
    } elseif($status === 'failed') {
      return array('status'=>false, 'apierror' => $response['reason']);
    }
  }

  public function get_indents_itemwise($filter_params=[]) {
    $response = $this->api_caller->sendRequest('get','reports/itemwise-indents-booked',$filter_params);
    $status = $response['status'];
    if($status === 'success') {
      return array('status'=>true,'response' => $response['response']);
    } elseif($status === 'failed') {
      return array('status'=>false, 'apierror' => $response['reason']);
    }
  }

  public function get_indents_agentwise($filter_params=[]) {
    $response = $this->api_caller->sendRequest('get','reports/agentwise-indents-booked',$filter_params);
    $status = $response['status'];
    if($status === 'success') {
      return array('status'=>true,'response' => $response['response']);
    } elseif($status === 'failed') {
      return array('status'=>false, 'apierror' => $response['reason']);
    }
  }

  public function get_indents_statewise($filter_params=[]) {
    $response = $this->api_caller->sendRequest('get','reports/statewise-indents-booked',$filter_params);
    $status = $response['status'];
    if($status === 'success') {
      return array('status'=>true,'response' => $response['response']);
    } elseif($status === 'failed') {
      return array('status'=>false, 'apierror' => $response['reason']);
    }
  }

  public function get_indent_dispatch_register($filter_params=[]) {
    $response = $this->api_caller->sendRequest('get','reports/itemwise-dispatch-register',$filter_params);
    $status = $response['status'];
    if($status === 'success') {
      return array('status'=>true,'response' => $response['response']);
    } elseif($status === 'failed') {
      return array('status'=>false, 'apierror' => $response['reason']);
    }    
  }
}