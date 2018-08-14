<?php 

namespace ClothingRm\PromoOffers\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Products\Model\Products;
use ClothingRm\PromoOffers\Model\PromoOffers;

class PromoOffersController
{
  private $template;
  private $products_model, $offers_model;

  public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->flash = new Flash;
    $this->products_model = new Products;
    $this->offers_model = new PromoOffers;
  }

  # promo offer creation action
  public function promoOfferEntryAction(Request $request) {
    #-------------------------------------------------------------------------------
    # Initialize variables

    $offer_types_a = $form_errors = $form_data = [];
    $api_error = '';

    $offer_types_a = Constants::$PROMO_OFFER_CATEGORIES;
    $status_a = [1=>'Active', 0 => 'Inactive'];

    # end of initializing variables
    #-------------------------------------------------------------------------------

    # check if form is submitted.
    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $validation_status = $this->_validate_form_data($submitted_data,false);
      if($validation_status['status']===true) {
        $cleaned_params = $validation_status['cleaned_params'];
        # hit api
        $api_response = $this->offers_model->createPromoOffer($cleaned_params);
        if($api_response['status']===true) {
          $message = 'Promotional offer created successfully with code ` '.$api_response['offerCode'].' `';
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
      'status_a' => $status_a
    );

    return array($this->template->render_view('promo-offer-entry',$template_vars),$controller_vars);
  }

  # update promo offer
  public function promoOfferUpdateAction(Request $request) {
    #-------------------------------------------------------------------------------
    # Initialize variables

    $offer_types_a = $form_errors = $form_data = $offer_details = [];
    $api_error = '';

    $offer_types_a = Constants::$PROMO_OFFER_CATEGORIES;
    $status_a = [1=>'Active', 0 => 'Inactive'];

    # end of initializing variables
    #-------------------------------------------------------------------------------

    # check if form is submitted.
    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $offer_code = isset($submitted_data['pO']) && $submitted_data['pO'] !== '' ?
                    Utilities::clean_string($submitted_data['pO']) :
                    '';

      # check submitted offer code. there is a chance of malformed codes.
      # if not matched redirects to offers list page.
      $this->_validate_promo_offer_code($submitted_data['pO']);
      $validation_status = $this->_validate_form_data($submitted_data,false);
      if($validation_status['status']) {
        $cleaned_params = $validation_status['cleaned_params'];
        # hit api
        $api_response = $this->offers_model->updatePromoOffer($cleaned_params, $offer_code);
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

    } elseif( !is_null($request->get('offerCode')) ) {
      $offer_code = Utilities::clean_string($request->get('offerCode'));
      $form_data = $this->_map_api_variables_with_form($this->_validate_promo_offer_code($offer_code));
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
    );

    return array($this->template->render_view('promo-offer-update',$template_vars),$controller_vars);
  }  

  # promo offers list action
  public function promoOffersListAction(Request $request) {

    $search_params = $offer_types_a = $offers_a = [];
    $page_error = $offer_type = '';
    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
    $offer_types_a = array(''=>'Choose') + Constants::$PROMO_OFFER_CATEGORIES_DIGITS;

    // parse request parameters.
    $start_date = $request->get('startDate')!== null ? Utilities::clean_string($request->get('startDate')) : '';
    $end_date = $request->get('endDate')!== null ? Utilities::clean_string($request->get('endDate')) : '';
    $offer_type = $request->get('offerType') !== null?Utilities::clean_string($request->get('offerType')) : '';
    $page_no = $request->get('pageNo')!==null ? Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = 100;

    $search_params = array(
      'startDate' => $start_date,
      'endDate' => $end_date,
      'offerType' => $offer_type,
      'pageNo' => $page_no,
      'perPage' => $per_page,
    );

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
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Promo Offers Management',
      'icon_name' => 'fa fa-lemon-o',
    );

    // render template
    return array($this->template->render_view('promo-offers-list',$template_vars),$controller_vars);
  }

  private function _validate_promo_offer_code($offer_code='') {
    if($offer_code !== null || $offer_code !== '') {
      $offer_details = $this->offers_model->getPromoOfferDetails($offer_code);
      $status = $offer_details['status'];
      if($status) {
        return $offer_details['offerDetails'];
      }
    }

    $this->flash->set_flash_message('Invalid Promo Offer code', 1);
    Utilities::redirect('/promo-offers/list');
  }
  
  private function _map_api_variables_with_form($offer_details = []) {
    $mapped_data = [];
    foreach($offer_details as $key => $value) {
      if($key === 'promoType') {
        if((int)$value === 0) {
          $value = 'a';
          $mapped_data['discountOnProduct'] = $offer_details['discountPercent'];
        } elseif((int)$value === 1) {
          $value = 'b';
        } elseif((int)$value === 2) {
          $value = 'c';
          $mapped_data['discountOnBillValue'] = $offer_details['discountPercent'];
        }
        $mapped_data['offerType'] = $value;
      } elseif($key === 'totalQty') {
        $mapped_data['totalProducts'] = $value;
      } elseif($key === 'freeQty') {
        $mapped_data['freeProducts'] = $value;
      } elseif($key === '') {
      } else {
        $mapped_data[$key] = $value;
      }
    }
    return $mapped_data;
  }

  private function _validate_form_data($form_data=[]) {

    $form_errors = $cleaned_params = [];
    $is_one_item_found = false;

    $offer_code = Utilities::clean_string($form_data['offerCode']);
    $offer_type = Utilities::clean_string($form_data['offerType']);
    $offer_desc = Utilities::clean_string($form_data['offerDesc']);
    $start_date = Utilities::clean_string($form_data['startDate']);
    $end_date = Utilities::clean_string($form_data['endDate']);
    $status = Utilities::clean_string($form_data['status']);

    $product_name = Utilities::clean_string($form_data['productName']);
    $discount_on_product = Utilities::clean_string($form_data['discountOnProduct']);
    $total_products = Utilities::clean_string($form_data['totalProducts']);
    $free_products = Utilities::clean_string($form_data['freeProducts']);
    $bill_value = Utilities::clean_string($form_data['billValue']);
    $discount_on_bill_value = Utilities::clean_string($form_data['discountOnBillValue']);

    # validate offer name
    if($offer_code === '') {
      $form_errors['offerCode'] = 'Promo code is required.';
    } elseif(strlen($offer_code) <= 10 && ctype_alnum($offer_code)) {
      $cleaned_params['offerCode'] = $offer_code;
    } else {
      $form_errors['offerCode'] = 'Must be below 10 chars. with alphabets and digits.';      
    }

    # validate offer type
    if($offer_type === '') {
      $form_errors['offerType'] = 'Offer type is mandatory.';
    } elseif( !in_array($offer_type,array('a','b','c')) ) {
      $form_errors['offerType'] = 'Invalid offer type.';      
    } else {
      $cleaned_params['offerType'] = $offer_type;
    }

    # validate start date.
    if( !Utilities::validate_date($start_date) ) {
      $form_errors['startDate'] = 'Invalid start date.';
    } else {
      $cleaned_params['startDate'] = $start_date;
    }

    # validate end date.
    if( !Utilities::validate_date($end_date) ) {
      $form_errors['endDate'] = 'Invalid end date.';
    } else {
      $cleaned_params['endDate'] = $end_date;
    }

    # validate status.
    if( !in_array($status,array(0,1)) ) {
      $form_errors['status'] = 'Invalid status.';
    } else {
      $cleaned_params['status'] = $status;
    }

    # validate offer discount fields.
    if($offer_type === 'a') {
      if(!ctype_alnum(str_replace([' ', '-', '_'], ['','',''], $product_name)) {
        $form_errors['productName'] = 'Invalid product name.';
      } else {
        $cleaned_params['productName'] = $product_name;
      }
      if(!is_numeric($discount_on_product) || $discount_on_product <= 0) {
        $form_errors['discountOnProduct'] = 'Invalid product discount.';
      } else {
        $cleaned_params['discountOnProduct'] = $discount_on_product;
      }
      # make zero or empty for all other config fields.
      $cleaned_params['totalProducts'] = $cleaned_params['freeProducts'] = 0;
      $cleaned_params['billValue'] = $cleaned_params['discountOnBillValue'] = 0;

    } elseif($offer_type === 'b') {

      if(!is_numeric($total_products) || $total_products <= 0) {
        $form_errors['totalProducts'] = 'Invalid total products.';
      } else {
        $cleaned_params['totalProducts'] = $total_products;
      }
      if(!is_numeric($free_products) || $free_products <= 0) {
        $form_errors['freeProducts'] = 'Invalid free products.';
      } else {
        $cleaned_params['freeProducts'] = $free_products;
      }

      # make zero or empty for all other config fields.
      $cleaned_params['productName'] = '';
      $cleaned_params['discountOnProduct'] = 0;
      $cleaned_params['billValue'] = $cleaned_params['discountOnBillValue'] = 0;

    } elseif($offer_type === 'c') {

      if(!is_numeric($bill_value) || $bill_value <= 0) {
        $form_errors['billValue'] = 'Invalid bill value.';
      } else {
        $cleaned_params['billValue'] = $bill_value;
      }
      if(!is_numeric($discount_on_bill_value) || $discount_on_bill_value <= 0) {
        $form_errors['discountOnBillValue'] = 'Invalid discount.';
      } else {
        $cleaned_params['discountOnBillValue'] = $discount_on_bill_value;
      }

      # make zero or empty for all other config fields.
      $cleaned_params['productName'] = '';
      $cleaned_params['discountOnProduct'] = 0;
      $cleaned_params['totalProducts'] = $cleaned_params['freeProducts'] = 0;
    }

    $cleaned_params['offerDesc'] = $offer_desc;

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

} #end of class.