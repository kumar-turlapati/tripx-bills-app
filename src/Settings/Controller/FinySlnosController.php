<?php 

namespace Settings\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Atawa\Utilities;
use Atawa\Template;
use Atawa\Flash;
use Atawa\Constants;

use Settings\Model\FinySlno;
use Settings\Model\Finy;

class FinySlnosController {
	
  protected $template, $flash;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->flash = new Flash;
    $this->finy_model = new Finy;
    $this->finy_slno_model = new FinySlno;
  }

  // create finy slnos
  public function createFinySlnos(Request $request) {

    $form_data = $form_errors = [];
    $finys = [];

    $voc_types = Constants::$VOC_TYPES;
    
    $finy_response = $this->finy_model->get_finys();
    if($finy_response['status'] && count($finy_response['finys']) > 0) {
      $finy_codes = array_column($finy_response['finys'], 'finyCode');
      $finy_names = array_column($finy_response['finys'], 'finyName');
      $finys = array_combine($finy_codes, $finy_names);
    } else {
      $this->flash->set_flash_message('No Financial years were defined to create Serial numbers.');
      Utilities::redirect('/finy-slnos/list');
    }

    // ---------- get location codes from api ------------------
    $client_locations = Utilities::get_client_locations(true, false, true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    // dump($location_codes);

    if( count($request->request->all())>0 ) {
      $form_data = $request->request->all();
      $validation = $this->_validate_fin_year_slnos_data($form_data, $location_codes);
      if($validation['status']) {
        $api_response = $this->finy_slno_model->create_finy_slnos($validation['cleaned_params']);
        if($api_response['status']) {
          $message = 'Financial year Slnos. successfully created with code ` '.$api_response['finySlnoCode'].' `';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/finy-slnos/create');
        } else {
          $api_error = $api_response['apierror'];
          $this->flash->set_flash_message($api_error, 1);
        }
      } else {
        $form_errors = $validation['form_errors'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'form_data' => $form_data,
      'form_errors' => $form_errors,
      'flash_obj' => $this->flash,
      'voc_types' => $voc_types,
      'finys' => $finys,
      'client_locations' => ['' => 'All Stores'] + $client_locations,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Settings - Financial Year Slnos',
      'icon_name' => 'fa fa-cogs',
    );

    return array($this->template->render_view('create-finy-slnos', $template_vars), $controller_vars);
  }

  // update finy slnos
  public function updateFinySlnos(Request $request) {

    $form_data = $form_errors = [];
    $voc_types = Constants::$VOC_TYPES;
    $selected_finy_code = $location_code = '';
    
    $finy_response = $this->finy_model->get_finys();
    if($finy_response['status'] && count($finy_response['finys']) > 0) {
      $finy_codes = array_column($finy_response['finys'], 'finyCode');
      $finy_names = array_column($finy_response['finys'], 'finyName');
      $finys = array_combine($finy_codes, $finy_names);
    } else {
      $finys = [];
    }

    // ---------- get location codes from api ------------------
    $client_locations = Utilities::get_client_locations(true, false, false);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    if( count($request->request->all())>0 ) {
      if(!is_null($request->get('finySlnoCode'))) {
        $finy_slno_code = Utilities::clean_string($request->get('finySlnoCode'));
      } else {
        $this->flash->set_flash_message('Invalid Financial Year Slno code.');
        Utilities::redirect('/finy/list');
      }
      $submitted_data = $request->request->all();
      $validation = $this->_validate_fin_year_slnos_data($submitted_data, $location_codes);
      if($validation['status']) {
        $api_response = $this->finy_slno_model->update_finy_slnos($validation['cleaned_params'], $finy_slno_code);
        if($api_response['status']) {
          $message = 'FY serial numbers information updated successfully.';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/finy-slnos/list');
        } else {
          $api_error = $api_response['apierror'];
          $this->flash->set_flash_message($api_error, 1);
        }
      } else {
        $form_errors = $validation['form_errors'];
      }
    } elseif( !is_null($request->get('finySlnoCode')) ) {
      $finy_slno_code = Utilities::clean_string($request->get('finySlnoCode'));
      $finy_slno_details = $this->finy_slno_model->get_finy_slno_details($finy_slno_code);
      // dump($finy_slno_details, $location_codes);
      if($finy_slno_details === false) {
        $this->flash->set_flash_message('Invalid parameter detected.');
        Utilities::redirect('/finy/list');
      } else {
        $form_data = $this->_map_api_data_with_form($finy_slno_details['finySlnoDetails']);
        $selected_finy_code = $finy_slno_details['finySlnoDetails']['finyCode'];
        $location_code = isset($location_codes[$finy_slno_details['finySlnoDetails']['locationID']]) ? $location_codes[$finy_slno_details['finySlnoDetails']['locationID']] : ''; 
      }
    }

    // prepare form variables.
    $template_vars = array(
      'form_data' => $form_data,
      'errors' => $form_errors,
      'flash_obj' => $this->flash,
      'voc_types' => $voc_types,
      'finys' => $finys,
      'selected_finy_code' => $selected_finy_code,
      'client_locations' => ['' => 'All Stores'] + $client_locations,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
      'location_code' => $location_code,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Settings - Financial Year Slnos',
      'icon_name' => 'fa fa-cogs',      
    );

    return array($this->template->render_view('update-finy-slnos', $template_vars), $controller_vars);
  }  

  // list finys slnos
  public function listFinySlnos(Request $request) {
    
    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';
    $default_finy_code = '';

    $finy_response = $this->finy_model->get_finys();
    if($finy_response['status'] && count($finy_response['finys']) > 0) {
      $finy_codes = array_column($finy_response['finys'], 'finyCode');
      $finy_names = array_column($finy_response['finys'], 'finyName');
      $finys = array_combine($finy_codes, $finy_names);
      $actvie_finy_index = array_search(1, array_column($finy_response['finys'], 'isActive'));
      $default_finy_code = $finy_response['finys'][$actvie_finy_index]['finyCode'];
    } else {
      $this->flash->set_flash_message('No Financial years were defined to create Serial numbers.');
      Utilities::redirect('/finy/list');
    }

    // ---------- get location codes from api ------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

    $slnos = $search_params = [];

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';

    $page_no = is_null($request->get('pageNo')) ? 1 : Utilities::clean_string($request->get('pageNo'));
    $per_page = is_null($request->get('perPage')) ? 100 : Utilities::clean_string($request->get('perPage'));
    $finy_code = is_null($request->get('finyCode')) ? $default_finy_code : Utilities::clean_string($request->get('finyCode'));
    $location_code = is_null($request->get('locationCode')) ? $default_location : Utilities::clean_string($request->get('locationCode'));

    $search_params = [
      'pageNo' => $page_no,
      'perPage' => $per_page,
      'finyCode' => $finy_code,
      'locationCode' => $location_code,
    ];

    $api_response = $this->finy_slno_model->get_finy_slnos($search_params);

    // check api status
    if($api_response['status']) {
      // check whether we got products or not.
      if(count($api_response['slnos']) >0) {
        $slno = Utilities::get_slno_start(count($api_response['slnos']['slnos']),$per_page,$page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;
        }
        if($api_response['slnos']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $api_response['slnos']['total_pages'];
        }
        if($api_response['slnos']['this_page'] < $per_page) {
          $to_sl_no = ($slno+$api_response['slnos']['this_page'])-1;
        }
        $slnos = $api_response['slnos']['slnos'];
        $total_pages = $api_response['slnos']['total_pages'];
        $total_records = $api_response['slnos']['total_records'];
        $record_count = $api_response['slnos']['this_page'];
      } else {
        $page_error = $api_response['apierror'];
      }
    } else {
      $page_error = $api_response['apierror'];
    }

    // prepare form variables.
    $template_vars = array(
      'slnos' => $slnos,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' =>  $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'search_params' => $search_params,            
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'finys' => $finys,
      'client_locations' => ['' => 'All Stores'] + $client_locations,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,      
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Settings - Financial Year Serial Numbers',
      'icon_name' => 'fa fa-cogs',
    );

    // render template
    return array($this->template->render_view('finy-slnos-list', $template_vars), $controller_vars);    
  }

  // validate finy slnos
  private function _validate_fin_year_slnos_data($form_data = [], $location_codes = []) {
    $cleaned_params = $form_errors = [];
    $voc_types = Constants::$VOC_TYPES;

    if(isset($form_data['vocCodesText']) && is_array($form_data['vocCodesText']) && count($form_data['vocCodesText'])>0 ) {
      $voc_codes_text_a = $form_data['vocCodesText'];
    } else {
      return [
        'status' => false,
        'form_errors' => ['vocCodesText' => 'Invalid data'],
      ];      
    }
    if(isset($form_data['vocCodesAic']) && is_array($form_data['vocCodesAic']) && count($form_data['vocCodesAic'])>0 ) {
      $voc_codes_aic_a = $form_data['vocCodesAic'];
    } else {
      return [
        'status' => false,
        'form_errors' => ['vocCodesAic' => 'Invalid data'],
      ];
    }

    if(isset($form_data['finyCode']) && $form_data['finyCode'] !== '') {
      $cleaned_params['finyCode'] = Utilities::clean_string($form_data['finyCode']);
    } else {
      $form_errors['finyCode'] = 'Invalid Financial year.';
    }
    if(isset($form_data['locationCode']) && $form_data['locationCode'] !== '') {
      $location_code = Utilities::clean_string($form_data['locationCode']);
      if(in_array($location_code, $location_codes)) {
        $cleaned_params['locationCode'] = $location_code;
      } else {
        $form_errors['locationCode'] = 'Store does not exists.';
      }
    } else {
      $form_errors['locationCode'] = 'Invalid Store Name.';
    }    

    foreach($voc_types as $voc_key => $voc_name) {
      $text_key = $voc_key.'_text';
      $aic_key = $voc_key.'_aic';
      if(isset($voc_codes_text_a[$text_key]) && isset($voc_codes_aic_a[$aic_key])) {
        $text_name = Utilities::clean_string($voc_codes_text_a[$text_key]);
        $aic_value = (int)Utilities::clean_string($voc_codes_aic_a[$aic_key]);
        if(strlen($text_name) > 0 && strlen($text_name) <= 14) {
          $cleaned_params['vocCodesText'][$text_key] = $text_name;
        } else {
          $form_errors['vocCodesText'][$text_key] = 'Invalid. Must be 1 - 14 characters long.';          
        }
        if($aic_value > 0 && $aic_value <= 99999 ) {
          $cleaned_params['vocCodesAic'][$aic_key] = $aic_value;
        } else {
          $form_errors['vocCodesAic'][$aic_key] = 'Invalid. Must be 1 - 99999';          
        }
      } else {
        $form_errors['vocCodesText'][$text_key] = 'Invalid key.';
        $form_errors['vocCodesAic'][$aic_key] = 'Invalid key.';
      }
    }
      
    if(count($form_errors) > 0) {
      return [
        'status' => false,
        'form_errors' => $form_errors,
      ];
    } else {
      return [
        'status' => true,
        'cleaned_params' => $cleaned_params,
      ];      
    }
  }

  public function _map_api_data_with_form($form_data=[]) {
    $mapped_data = [];

    $slno_aic_a = array_column($form_data['_tranItems'], 'slnoAic');
    $slno_text_a = array_column($form_data['_tranItems'], 'slnoText');
    $voc_types = array_column($form_data['_tranItems'], 'vocType');

    foreach($voc_types as $key => $voc_code) {
      $var_name_txt = $voc_code.'_text';
      $var_name_aic = $voc_code.'_aic';
      $mapped_data['vocCodesText'][$var_name_txt] = $slno_text_a[$key];
      $mapped_data['vocCodesAic'][$var_name_aic] = $slno_aic_a[$key];      
    }
    $mapped_data['finyCode'] = $form_data['finyCode'];

    return $mapped_data;
  }
}