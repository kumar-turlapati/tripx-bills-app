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

  public function get_all_indents($filter_params=[]) {
    $response = $this->api_caller->sendRequest('get','sindent/register',$filter_params);
    $status = $response['status'];
    if($status === 'success') {
      return array('status'=>true,'response' => $response['response']);
    } elseif($status === 'failed') {
      return array('status'=>false, 'apierror' => $response['reason']);
    }
  }  

  public function get_indent_details($indent_no=0) {
    $filter_params['indentNo'] = $indent_no;
    $response = $this->api_caller->sendRequest('get','sindent/details',$filter_params);
    $status = $response['status'];
    if($status === 'success') {
      return array('status'=>true,'response' => $response['response']);
    } elseif($status === 'failed') {
      return array('status'=>false, 'apierror' => $response['reason']);
    }
  }


}