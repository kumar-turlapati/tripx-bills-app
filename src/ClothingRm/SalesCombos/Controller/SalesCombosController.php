<?php 

namespace ClothingRm\SalesCombos\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Products\Model\Products;
use ClothingRm\SalesCombos\Model\SalesCombos;

class SalesCombosController
{
  private $template;
  private $products_model, $sc_model;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->flash = new Flash;
    $this->products_model = new Products;
    $this->sc_model = new SalesCombos;
  }  

  // create Sales Combo
  public function createSalesCombo(Request $request) {
    #-------------------------------------------------------------------------------
    # Initialize variables

    $form_errors = $form_data = [];
    $location_ids = $location_codes = [];
    $api_error = '';

    $status_a = [ 99 => 'Select',  1 => 'Active', 0 => 'Inactive'];

    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    # end of initializing variables
    #-------------------------------------------------------------------------------

    // check if form is submitted.
    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $validation_status = $this->_validate_form_data($submitted_data,false);
      if($validation_status['status']) {
        $cleaned_params = $validation_status['cleaned_params'];
        $api_response = $this->sc_model->create_sales_combo($cleaned_params);
        if($api_response['status']) {
          $message = '<i class="fa fa-check aria-hidden="true"></i>&nbsp;Comobo created successfully with code ` '.$api_response['comboCode'].' `';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/sales-combo/add');
        } else {
          $api_error = $api_response['apierror'];
          $this->flash->set_flash_message($api_error, 1);
          $form_data = $submitted_data;
        }
      } else {
        $form_errors = $validation_status['form_errors'];
        $form_data = $submitted_data;
      }
    }

    // theme variables.
    $controller_vars = array(
      'page_title' => 'Sales Combos - Create a Combo',
      'icon_name' => 'fa fa-object-ungroup',
    );

    // template variables
    $template_vars = array(
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'api_error' => $api_error,
      'status_a' => $status_a,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,      
    );

    return array($this->template->render_view('sales-combos-create',$template_vars),$controller_vars);
  }

  // update Sales Combo
  public function updateSalesCombo(Request $request) {
    #-------------------------------------------------------------------------------
    # Initialize variables

    $form_errors = $form_data = [];
    $location_ids = $location_codes = [];
    $combo_details = [];
    $api_error = '';

    $status_a = [ 99 => 'Select',  1 => 'Active', 0 => 'Inactive'];

    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

    // get combo code.
    if( is_null($request->get('comboCode'))) {
      $this->flash->set_flash_message('Invalid Comobo.');
      Utilities::redirect('/sales-combo/list');
    } else {
      $combo_code = Utilities::clean_string($request->get('comboCode'));
      $combo_details_response = $this->sc_model->get_combo_details($combo_code);
      // dump($combo_details_response);
      // exit;
      if($combo_details_response['status'] === false) {
        $message = '<i class="fa fa-times" aria-hidden="true"></i>&nbsp;Invalid Combo Code';
        $this->flash->set_flash_message($message, 1);
        Utilities::redirect('/sales-combo/list');
      } else {
        $form_data = $combo_details_response['comboDetails'];
      }
    }

    # end of initializing variables
    #-------------------------------------------------------------------------------

    // check if form is submitted.
    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $validation_status = $this->_validate_form_data($submitted_data,false);
      if($validation_status['status']) {
        $cleaned_params = $validation_status['cleaned_params'];
        $api_response = $this->sc_model->update_sales_combo($cleaned_params, $combo_code);
        if($api_response['status']) {
          $message = '<i class="fa fa-check" aria-hidden="true"></i>&nbsp;Comobo updated successfully.';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/sales-combo/list');
        } else {
          $api_error = $api_response['apierror'];
          $this->flash->set_flash_message($api_error, 1);
          $form_data = $submitted_data;
        }
      } else {
        $form_errors = $validation_status['form_errors'];
        $form_data = $submitted_data;
      }
    }

    // theme variables.
    $controller_vars = array(
      'page_title' => 'Sales Combos - Update a Combo',
      'icon_name' => 'fa fa-object-ungroup',
    );

    // template variables
    $template_vars = array(
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'api_error' => $api_error,
      'status_a' => $status_a,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,      
    );

    return array($this->template->render_view('sales-combos-update',$template_vars),$controller_vars);
  }

  // promo offers list action
  public function listSalesCombo(Request $request) {

    $search_params = $combos_a = [];
    $page_error = $offer_type = '';
    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';
    $location_code = '';

    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    // parse request parameters.
    $location_code = $request->get('locationCode')!==null ? Utilities::clean_string($request->get('locationCode')) : '';

    $search_params = array(
      'locationCode' => $location_code,
    );

    // dump($search_params);
    // exit;

    // hit api for combos.
    $api_response =  $this->sc_model->get_all_sales_combos($search_params);
    // dump($api_response);
    // exit;
    if($api_response['status']) {
      $combos_a = $api_response['response'];
    } else {
      $page_error = $api_response['apierror'];
    }

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'combos' => $combos_a,
      'search_params' => $search_params,
      'location_codes' => $location_codes,
      'location_ids' => $location_ids,
      'client_locations' => ['' => 'All Stores'] + $client_locations,
      'default_location' => $default_location,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Sales Combos - List',
      'icon_name' => 'fa fa-object-ungroup',
    );

    // render template
    return array($this->template->render_view('sales-combos-list',$template_vars),$controller_vars);
  }

  private function _validate_form_data($form_data=[]) {
    $form_errors = $cleaned_params = [];
    $rate_found = 0;

    $location_code = Utilities::clean_string($form_data['locationCode']);
    $combo_name = Utilities::clean_string($form_data['comboName']);
    $combo_number = Utilities::clean_string($form_data['comboNumber']);
    $status = Utilities::clean_string($form_data['status']);
    $combo_rate2 = Utilities::clean_string($form_data['comboPrice2']);
    $combo_rate3 = Utilities::clean_string($form_data['comboPrice3']);
    $combo_rate4 = Utilities::clean_string($form_data['comboPrice4']);
    $combo_rate5 = Utilities::clean_string($form_data['comboPrice5']);
    $combo_rate6 = Utilities::clean_string($form_data['comboPrice6']);
    $combo_rate7 = Utilities::clean_string($form_data['comboPrice7']);
    $combo_rate8 = Utilities::clean_string($form_data['comboPrice8']);
    $combo_rate9 = Utilities::clean_string($form_data['comboPrice9']);
    $combo_rate10 = Utilities::clean_string($form_data['comboPrice10']);
    $combo_rate11 = Utilities::clean_string($form_data['comboPrice11']);
    $combo_rate12 = Utilities::clean_string($form_data['comboPrice12']);
    $products = $form_data['itemDetails'];

    // validate combo name
    if($combo_name === '') {
      $form_errors['comboName'] = '<i class="fa fa-times" aria-hidden="true"></i> Comobo name is mandatory and must be unique.';
    } elseif(strlen($combo_name) <= 50) {
      $cleaned_params['comboName'] = $combo_name;
    } else {
      $form_errors['comboName'] = '<i class="fa fa-times" aria-hidden="true"></i> Must be below 50 chars.';      
    }

    // validate combo numeric code
    if(is_numeric($combo_number) && $combo_number > 0 && $combo_number <= 99 && strlen($combo_number)===2) {
      $cleaned_params['comboNumber'] = $combo_number;
    } else {
      $form_errors['comboNumber'] = '<i class="fa fa-times" aria-hidden="true"></i> Comobo numeric code is mandatory and it must be between 01-99';
    }

    // validate location code
    if($location_code === '') {
      $form_errors['locationCode'] = '<i class="fa fa-times" aria-hidden="true"></i> Invalid store name.';      
    } else {
      $cleaned_params['locationCode'] = $location_code;
    }

    // validate combo items.
    if(isset($products) && count($products)>0) {
      $products_final = [];
      foreach($products as $product_name) {
        $product_name = Utilities::clean_string($product_name);
        if($product_name !== '') {
          $products_final[] = $product_name;
        }
      }
      if(count($products_final) >= 2) {
        $cleaned_params['itemDetails'] = $products_final;
      } else {
        $form_errors['itemDetails'] = '<i class="fa fa-times" aria-hidden="true"></i> Minimum two Products are required for a combo.'; 
      }
    } else {
      $form_errors['itemDetails'] = '<i class="fa fa-times" aria-hidden="true"></i> Invalid products. Minimum two products are required for a Combo'; 
    }

    // rates loop
    if(is_numeric($combo_rate2) && $combo_rate2>0) {
      $cleaned_params['comboPrice2'] = $combo_rate2;
      $rate_found++;
    } else {
      $cleaned_params['comboPrice2'] = 0;
    }
    if(is_numeric($combo_rate3) && $combo_rate3>0) {
      $cleaned_params['comboPrice3'] = $combo_rate3;
      $rate_found++;
    } else {
      $cleaned_params['comboPrice3'] = 0;
    }
    if(is_numeric($combo_rate4) && $combo_rate4>0) {
      $cleaned_params['comboPrice4'] = $combo_rate4;
      $rate_found++;
    } else {
      $cleaned_params['comboPrice4'] = 0;
    }
    if(is_numeric($combo_rate5) && $combo_rate5>0) {
      $cleaned_params['comboPrice5'] = $combo_rate5;
      $rate_found++;
    } else {
      $cleaned_params['comboPrice5'] = 0;
    }    
    if(is_numeric($combo_rate6) && $combo_rate6>0) {
      $cleaned_params['comboPrice6'] = $combo_rate6;
      $rate_found++;
    } else {
      $cleaned_params['comboPrice6'] = 0;
    }
    if(is_numeric($combo_rate7) && $combo_rate7>0) {
      $cleaned_params['comboPrice7'] = $combo_rate7;
      $rate_found++;
    } else {
      $cleaned_params['comboPrice7'] = 0;
    }
    if(is_numeric($combo_rate8) && $combo_rate8>0) {
      $cleaned_params['comboPrice8'] = $combo_rate8;
      $rate_found++;
    } else {
      $cleaned_params['comboPrice8'] = 0;
    }
    if(is_numeric($combo_rate9) && $combo_rate9>0) {
      $cleaned_params['comboPrice9'] = $combo_rate9;
      $rate_found++;
    } else {
      $cleaned_params['comboPrice9'] = 0;
    }
    if(is_numeric($combo_rate10) && $combo_rate10>0) {
      $cleaned_params['comboPrice10'] = $combo_rate10;
      $rate_found++;
    } else {
      $cleaned_params['comboPrice10'] = 0;
    }
    if(is_numeric($combo_rate11) && $combo_rate11>0) {
      $cleaned_params['comboPrice11'] = $combo_rate11;
      $rate_found++;
    } else {
      $cleaned_params['comboPrice11'] = 0;
    }
    if(is_numeric($combo_rate12) && $combo_rate12>0) {
      $cleaned_params['comboPrice12'] = $combo_rate12;
      $rate_found++;
    } else {
      $cleaned_params['comboPrice12'] = 0;
    }

    if(count($products_final) > 0) {
      if($rate_found === 0) {
        $form_errors['itemDetails'] = '<i class="fa fa-times" aria-hidden="true"></i> Combo rates must be given for the various units.';
      } elseif($rate_found < count($products_final)-1) {
        $form_errors['itemDetails'] = '<i class="fa fa-times" aria-hidden="true"></i> The entered rates are less than the products in Combo.';
      } elseif($rate_found > count($products_final)-1) {
        $form_errors['itemDetails'] = '<i class="fa fa-times" aria-hidden="true"></i> The entered rates are more than the products in Combo.';
      }
    }

    // validate status.
    if( !in_array($status,array(0,1)) ) {
      $form_errors['status'] = 'Status should be Active or Inactive.';
    } else {
      $cleaned_params['status'] = $status;
    }

    if(count($form_errors)>0) {
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

} // end of class.


/*
    # validate start date.
    if( !Utilities::validate_date($start_date) ) {
      $form_errors['startDate'] = 'Invalid start date.';
    } else {
      $cleaned_params['startDate'] = $start_date;
    }
*/