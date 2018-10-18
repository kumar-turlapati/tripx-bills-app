<?php 

namespace ClothingRm\Mfg\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Mfg\Model\Mfg;

class MfgController
{
	protected $template, $mfg_model, $flash;

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');    
    $this->mfg_model = new Mfg;
    $this->flash = new Flash;
	}

  public function createMfg(Request $request) {

    $submitted_data = $form_errors = [];
    $status_options = array(''=>'Select','1'=>'Active','0'=>'Inactive');

    # ---------- get location codes from api -----------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->mfg_model->create_mfg($cleaned_params);
        if($result['status']) {
          $mfg_code = $result['mfgCode'];
          $this->flash->set_flash_message('Brand / Mfg. created successfully with code `'.$mfg_code.'`');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/mfg/create');
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    # prepare form variables.
    $template_vars = array(
      'status_options' => $status_options,
      'submitted_data' => $submitted_data,
      'form_errors' => $form_errors,
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'Product Brands / Manufacturers',
      'icon_name' => 'fa fa-thumbs-o-up',
    );

    # render template
    return array($this->template->render_view('add-mfg',$template_vars),$controller_vars);
  }

  public function updateMfg(Request $request) {
    $submitted_data = $form_errors = array();
    $status_options = array(''=>'Select','1'=>'Active','0'=>'Inactive');

    # ---------- get location codes from api -----------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    $mfg_code = $request->get('mfgCode');
    $mfg_details = $this->mfg_model->get_mfg_details($mfg_code);
    if($mfg_details === false) {
      $this->flash->set_flash_message('Invalid brand / mfg. code', 1);
      Utilities::redirect('/mfgs/list');
    } else {
      $submitted_data = $mfg_details;
    }

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->mfg_model->update_mfg($cleaned_params,$mfg_code);
        if($result['status']) {
          $this->flash->set_flash_message('Brand / Mfg details updated successfully');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
        Utilities::redirect('/mfgs/list');
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    # prepare form variables.
    $template_vars = array(
      'status_options' => $status_options,
      'submitted_data' => $submitted_data,
      'form_errors' => $form_errors,
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,      
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'Product Brands / Manufacturers',
      'icon_name' => 'fa fa-thumbs-o-up',
    );

    # render template
    return array($this->template->render_view('update-mfg',$template_vars),$controller_vars);
  }

  public function listMfgs(Request $request) {
    $mfgs_list = $search_params = $mfgs = [];
    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $page_success = $page_error = '';

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];
    }

    $per_page = 100;
    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';
    $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')):1;
    $location_code = $request->get('locationCode')!== null ? Utilities::clean_string($request->get('locationCode')) : $default_location;

    $search_params = array(
      'locationCode' => $location_code,
      'pageNo' => $page_no,
      'perPage' => $per_page,
    );

    $api_response = $this->mfg_model->get_mfgs($search_params);
    if($api_response['status']) {
      if(count($api_response['mfgs']) >0) {
        $slno = Utilities::get_slno_start(count($api_response['mfgs']['mfgs']),$per_page,$page_no);
        $to_sl_no = $slno+$per_page;
        $slno++;
        if($page_no<=3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;        
        }
        if($api_response['mfgs']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $api_response['mfgs']['total_pages'];
        }
        if($api_response['mfgs']['this_page'] < $per_page) {
          $to_sl_no = ($slno+$api_response['mfgs']['this_page'])-1;
        }
        $mfgs_a = $api_response['mfgs']['mfgs'];
        $total_pages = $api_response['mfgs']['total_pages'];
        $total_records = $api_response['mfgs']['total_records'];
        $record_count = $api_response['mfgs']['this_page'];
      } else {
        $page_error = $api_response['apierror'];
        $this->flash->set_flash_message($page_error);
      }
    } else {
      $page_error = $api_response['apierror'];
      $this->flash->set_flash_message($page_error);      
    }

    # build variables
    $controller_vars = array(
      'page_title' => 'Product Brands / Manufacturers',
      'icon_name' => 'fa fa-thumbs-o-up',
    );
    $template_vars = array(
      'mfgs' => $mfgs_a,
      'sl_no' => $slno,
      'search_params' => $search_params,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'flash_obj' => $this->flash,
      'client_locations' => array(''=>'All Stores') + $client_locations,
      'default_location' => $default_location,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,      
    );

    return array($this->template->render_view('mfgs-list', $template_vars), $controller_vars);        
  }

  private function _validate_form_data($form_data=[]) {
    $cleaned_params = $errors = [];

    if(isset($form_data['mfgName']) && $form_data['mfgName'] !== '') {
      $mfg_name = Utilities::clean_string($form_data['mfgName']);
      if(!preg_match('/^[a-zA-Z0-9 .%\-]+$/i', $mfg_name)) {
        $errors['mfgName'] = 'Brand / Mfg. name should contain only alphabets, digits, period, dash and percentage symbol.';
      } else {
        $cleaned_params['mfgName'] = $mfg_name;
      }
    } else {
      $errors['mfgName'] = 'Brand / Mfg. name is required.';
    }

    if( is_numeric($form_data['status']) && ((int)$form_data['status']===0 || (int)$form_data['status']===1) ) {
      $cleaned_params['status'] = Utilities::clean_string($form_data['status']);
    } else {
      $errors['status'] = 'Invalid status.';
    }

    $cleaned_params['locationCode'] = Utilities::clean_string($form_data['locationCode']);

    if(count($errors)>0) {
      return array(
        'status' => false,
        'errors' => $errors,
      );
    } else {
      return array(
        'status' => true,
        'cleaned_params' => $cleaned_params,
      );
    }
  }
}