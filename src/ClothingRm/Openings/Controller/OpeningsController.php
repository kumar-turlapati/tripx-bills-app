<?php 

namespace ClothingRm\Openings\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Openings\Model\Openings;
use ClothingRm\Taxes\Model\Taxes;

class OpeningsController
{
	protected $views_path;

	public function __construct() {
		$this->views_path = __DIR__.'/../Views/';
    $this->taxes_model = new Taxes;
	}

  public function opBalCreateAction(Request $request) {

    $errors = $taxes = array();
    $page_error = $page_success = '';
    $update_flag = false;
    $submitted_data = $opbal_details = array();
    $qtys_a = array(''=>'Choose');
    $months_a = array(''=>'Choose')+Utilities::get_calender_months();
    $years_a = array(''=>'Choose')+Utilities::get_calender_years();
    
    # initiate supplier model.
    $openings_api_caller = new Openings;

    for($i=1;$i<=1000;$i++) {
      $qtys_a[$i] = $i;
    }

    if($request->get('opCode') && $request->get('opCode')!='') {
      $op_code = Utilities::clean_string($request->get('opCode'));
      $opbal_response = $openings_api_caller->get_opbal_details($op_code);
      if($opbal_response['status']===true) {
        $opbal_details = $opbal_response['opDetails'];
        $update_flag = true;
      }
      $page_title = 'Opening Balances';
      $btn_label = 'Update Opening Balance';
    } else {
      $op_code = '';
      $btn_label = 'Create Opening Balance';
      $page_title = 'Opening Balances';
    }

    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      if(count($opbal_details)>0) {
        $opbal_response = $openings_api_caller->updateOpBal($request->request->all(),$op_code);             
      } else {
        $opbal_response = $openings_api_caller->createOpBal($request->request->all());
      }
      $status = $opbal_response['status'];
      $flash = new Flash;
      if($status === false) {
        if(isset($opbal_response['errors'])) {
          $errors = $opbal_response['errors'];
        } elseif(isset($opbal_response['apierror'])) {
          $page_error = $opbal_response['apierror'];
        }
        $submitted_data = $opbal_details;
      } elseif($update_flag===false) {
        $page_success   = 'Opening Balance added successfully for Item [ '.$request->get('itemName').' ]';
        $flash->set_flash_message($page_success);
        Utilities::redirect('/opbal/add');                
      } else {
        $page_success   = 'Opening Balance updated successfully for Item [ '.$opbal_details['itemName'].' ]';
        $flash->set_flash_message($page_success);
        Utilities::redirect('/opbal/update/'.$op_code);
      }
    } elseif(count($opbal_details)>0) {
      $submitted_data = $opbal_details;
    }

    $taxes_a = $this->taxes_model->list_taxes();
    if($taxes_a['status'] && count($taxes_a['taxes'])>0 ) {
      $taxes_raw = $taxes_a['taxes'];
      foreach($taxes_a['taxes'] as $tax_details) {
        $taxes[$tax_details['taxCode']] = $tax_details['taxPercent'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'submitted_data' => $submitted_data,
      'errors' => $errors,
      'qtys' => $qtys_a,
      'months' => $months_a,
      'years' => $years_a,
      'vat_percents' => array(''=>'Choose')+$taxes,
      'btn_label' => $btn_label,
      'update_mode' => $update_flag,
    );

    // build variables
    $controller_vars = array(
      'page_title' => $page_title,
      'icon_name' => 'fa fa-folder-open',            
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('opbal-create', $template_vars), $controller_vars);
  }    

  public function opBalListAction(Request $request) {
      
    $search_params = $openings_a = [];

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';
    $categories_a = [''=>'Choose'];

    $client_locations = Utilities::get_client_locations();

    $openings_api = new Openings;
    if( $request->get('pageNo') ) {
      $page_no = $request->get('pageNo');
    } else {
      $page_no = 1;
    }

    if( $request->get('perPage') ) {
      $per_page = $request->get('perPage');
    } else {
      $per_page = 100;
    }
      
    if(count($request->request->all()) > 0) {
      if($request->get('itemName') !== '') {
        $search_params['itemName'] = Utilities::clean_string($request->get('itemName'));
      }
      if($request->get('batchNo') !== '') {
        $search_params['batchNo'] = Utilities::clean_string($request->get('batchNo'));
      }
      if($request->get('category') !== '') {
        $search_params['category'] = Utilities::clean_string($request->get('category'));
      }
      if($request->get('locationCode') !== '') {
        $search_params['locationCode'] = Utilities::clean_string($request->get('locationCode'));
      }      
    } else {
      $search_params = [];
    }

    $search_params['pageNo'] = $page_no;
    $search_params['perPage'] = $per_page;

    $openings = $openings_api->opbal_list($search_params);
    $api_status = $openings['status'];

    // check api status
    if($api_status) {
      # check whether we got products or not.
      if(count($openings['openings'])>0) {
        $slno = Utilities::get_slno_start(count($openings['openings']), $per_page, $page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;            
        }
        if($openings['total_pages']<$page_links_to_end) {
          $page_links_to_end = $openings['total_pages'];
        }
        if($openings['record_count'] < $per_page) {
          $to_sl_no = ($slno+$openings['record_count'])-1;
        }
        $openings_a = $openings['openings'];
        $total_pages = $openings['total_pages'];
        $total_records = $openings['total_records'];
        $record_count = $openings['record_count'];
      } else {
        $page_error = $openings['apierror'];
      }
    } else {
      $page_error = $openings['apierror'];
    }           

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'openings' => $openings_a,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'search_params' => $search_params,            
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'categories' => $categories_a,
      'location_codes' => ['' => 'All Stores'] + $client_locations,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Opening Balances',
      'icon_name' => 'fa fa-folder-open',             
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('openings-list', $template_vars), $controller_vars);
  }

}