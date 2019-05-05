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

    $status_a = [1=>'Active', 0 => 'Inactive'];

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

    $status_a = [1=>'Active', 0 => 'Inactive'];

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
        $api_response = $this->sc_model->create_sales_combo($cleaned_params);
        if($api_response['status']) {
          $message = '<i class="fa fa-check" aria-hidden="true"></i>&nbsp;Comobo updated successfully with code ` '.$api_response['comboCode'].' `';
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


  /*
  // update promo offer
  public function updateSalesCombo(Request $request) {
    #-------------------------------------------------------------------------------
    # Initialize variables

    $offer_types_a = $form_errors = $form_data = $offer_details = [];
    $location_ids = $location_codes = [];
    $api_error = '';

    $offer_types_a = Constants::$PROMO_OFFER_CATEGORIES;
    $status_a = [1=>'Active', 0 => 'Inactive'];

    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

    # end of initializing variables
    #-------------------------------------------------------------------------------

    # check if form is submitted.
    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $offer_code = isset($submitted_data['pO']) && $submitted_data['pO'] !== '' ?
                    Utilities::clean_string($submitted_data['pO']) :
                    '';

      $location_code = !is_null($request->get('lc')) ? Utilities::clean_string($request->get('lc')) : '';

      # check submitted offer code. there is a chance of malformed codes.
      # if not matched redirects to offers list page.
      $this->_validate_promo_offer_code($submitted_data['pO'], $location_code);
      $validation_status = $this->_validate_form_data($submitted_data,false);
      if($validation_status['status']) {
        $cleaned_params = $validation_status['cleaned_params'];
        # hit api
        $api_response = $this->offers_model->updatePromoOffer($cleaned_params, $offer_code, $location_code);
        if($api_response['status']) {
          $message = 'Promotional Offer updated successfully for offer code ` '.$offer_code.' `';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/promo-offers/list');
        } else {
          $api_error = $api_response['apierror'];
          $this->flash->set_flash_message($api_error, 1);
          $form_data = $submitted_data;          
        }
      } else {
        $form_errors = $validation_status['form_errors'];
        $form_data = $submitted_data;
      }

    } elseif( !is_null($request->get('offerCode')) && !is_null($request->get('lc')) ) {
      $offer_code = Utilities::clean_string($request->get('offerCode'));
      $location_code = Utilities::clean_string($request->get('lc'));
      $form_data = $this->_map_api_variables_with_form($this->_validate_promo_offer_code($offer_code, $location_code), $location_codes);
    } else {
      $this->flash->set_flash_message('Invalid promo code.', 1);
      Utilities::redirect('/promo-offers/list');
    }

    # theme variables.
    $controller_vars = array(
      'page_title' => 'Promo Offers',
      'icon_name' => 'fa fa-lemon-o',
    );
    $template_vars = array(
      'offer_types' => array(''=>'Choose') + $offer_types_a,
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'api_error' => $api_error,
      'status_a' => $status_a,
      'offerCode' => $offer_code,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
    );

    return array($this->template->render_view('promo-offer-update',$template_vars),$controller_vars);
  }  

  // promo offers list action
  public function promoOffersListAction(Request $request) {

    $search_params = $offer_types_a = $offers_a = [];
    $page_error = $offer_type = '';
    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $offer_types_a = array(''=>'Choose') + Constants::$PROMO_OFFER_CATEGORIES_DIGITS;

    $default_location = isset($_SESSION['lc']) ? $_SESSION['lc'] : '';

    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    // parse request parameters.
    $start_date = $request->get('startDate')!== null ? Utilities::clean_string($request->get('startDate')) : date("01-m-Y");
    $end_date = $request->get('endDate')!== null ? Utilities::clean_string($request->get('endDate')) : date("d-m-Y");
    $offer_type = $request->get('offerType') !== null ? Utilities::clean_string($request->get('offerType')) : '';
    $page_no = $request->get('pageNo')!==null ? Utilities::clean_string($request->get('pageNo')) : 1;
    $location_code = $request->get('locationCode')!==null ? Utilities::clean_string($request->get('locationCode')) : $default_location;
    $per_page = 100;

    $search_params = array(
      'startDate' => $start_date,
      'endDate' => $end_date,
      'offerType' => $offer_type,
      'pageNo' => $page_no,
      'perPage' => $per_page,
      'locationCode' => $location_code,
    );

    // dump($search_params);
    // exit;

    # hit api for offers data.
    $api_response =  $this->offers_model->getAllPromoOffers($search_params);
    if($api_response['status']===true) {
      if(count($api_response['response']['offers'])>0) {
          $slno = Utilities::get_slno_start(count($api_response['response']['offers']),$per_page,$page_no);
          $to_sl_no = $slno+$per_page;
          $slno++;
          if($page_no<=3) {
            $page_links_to_start = 1;
            $page_links_to_end = 10;
          } else {
            $page_links_to_start = $page_no-3;
            $page_links_to_end = $page_links_to_start+10;            
          }
          if($api_response['response']['total_pages']<$page_links_to_end) {
            $page_links_to_end = $api_response['response']['total_pages'];
          }
          if($api_response['response']['this_page'] < $per_page) {
            $to_sl_no = ($slno+$api_response['response']['this_page'])-1;
          }

          $offers_a = $api_response['response']['offers'];
          $total_pages = $api_response['response']['total_pages'];
          $total_records = $api_response['response']['total_records'];
          $record_count = $api_response['response']['this_page'];
      } else {
        $page_error = $api_response['apierror'];
      }
    } else {
      $page_error = $api_response['apierror'];
    }

     // prepare form variables.
    $template_vars = array(
      'offer_types' => $offer_types_a,
      'offer_type' => $offer_type,
      'page_error' => $page_error,
      'offers' => $offers_a,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params,
      'location_codes' => $location_codes,
      'client_locations' => $client_locations,
      'default_location' => $default_location,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Promo Offers Management',
      'icon_name' => 'fa fa-lemon-o',
    );

    // render template
    return array($this->template->render_view('promo-offers-list',$template_vars),$controller_vars);
  }

  private function _validate_promo_offer_code($offer_code='', $location_code='') {
    if($offer_code !== null || $offer_code !== '') {
      $offer_details = $this->offers_model->getPromoOfferDetails($offer_code, $location_code);
      $status = $offer_details['status'];
      if($status) {
        return $offer_details['offerDetails'];
      }
    }

    $this->flash->set_flash_message('Invalid Promo Offer code', 1);
    Utilities::redirect('/promo-offers/list');
  }*/

  private function _validate_form_data($form_data=[]) {

    $form_errors = $cleaned_params = [];

    $combo_name = Utilities::clean_string($form_data['comboName']);
    $combo_price = Utilities::clean_string($form_data['comboPrice']);
    $location_code = Utilities::clean_string($form_data['locationCode']);
    $status = Utilities::clean_string($form_data['status']);
    $products = $form_data['itemDetails'];

    // validate combo name
    if($combo_name === '') {
      $form_errors['comboName'] = '<i class="fa fa-times" aria-hidden="true"></i> Comobo name is mandatory and must be unique.';
    } elseif(strlen($combo_name) <= 20 && ctype_alnum($combo_name)) {
      $cleaned_params['comboName'] = $combo_name;
    } else {
      $form_errors['comboName'] = '<i class="fa fa-times" aria-hidden="true"></i> Must be below 20 chars. with alphabets and digits.';      
    }

    // validate combo price
    if(!is_numeric($combo_price)) {
      $form_errors['comboPrice'] = '<i class="fa fa-times" aria-hidden="true"></i> Comobo price is mandatory.';
    } else {
      $cleaned_params['comboPrice'] = $combo_price;
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
        $cleaned_params['itemDetails'] = $products;
      } else {
        $form_errors['itemDetails'] = '<i class="fa fa-times" aria-hidden="true"></i> Minimum two Products are required for a combo.'; 
      }
    } else {
      $form_errors['itemDetails'] = '<i class="fa fa-times" aria-hidden="true"></i> Invalid products. Minimum two products are required for a Combo'; 
    }

    // validate status.
    if( !in_array($status,array(0,1)) ) {
      $form_errors['status'] = 'Invalid status.';
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