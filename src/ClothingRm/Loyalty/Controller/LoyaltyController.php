<?php 

namespace ClothingRm\Loyalty\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Atawa\Utilities;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Loyalty\Model\Loyalty;

class LoyaltyController
{
	protected $views_path,$flash,$tax_model;

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->flash = new Flash();
    $this->loyalty_model = new Loyalty();
	}

  public function addLoyaltyMember(Request $request) {

    $submitted_data = $form_errors = [];

    # ---------- get location codes from api -----------------------
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
        $result = $this->loyalty_model->add_loyalty_member($cleaned_params);
        if($result['status']) {
          $this->flash->set_flash_message('Member added successfully');
          Utilities::redirect('/loyalty-member/add');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
      } else {
        $form_errors = $form_validation['errors'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'submitted_data' => $submitted_data,
      'form_errors' => $form_errors,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes, 
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Loyalty Programme',
      'icon_name' => 'fa fa-diamond',
    );

    // render template
    return array($this->template->render_view('add-loyalty-member',$template_vars),$controller_vars);
  }

  public function updateLoyaltyMember(Request $request) {

    $submitted_data = $form_errors = [];

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    if( count($request->request->all())>0 ) {
      $submitted_data = $request->request->all();
      # check member code from form and URL.
      if(!is_null($request->get('memberCode')) && $request->get('memberCode') !== $submitted_data['memberCode']) {
        $this->flash->set_flash_message('Invalid member update.', 1);
        Utilities::redirect('/loyalty-members/list');
      } else {
        $member_code = Utilities::clean_string($submitted_data['memberCode']);
      }
      $form_validation = $this->_validate_form_data($submitted_data);
      if($form_validation['status']) {
        $cleaned_params = $form_validation['cleaned_params'];
        $result = $this->loyalty_model->update_loyalty_member($cleaned_params, $member_code);
        if($result['status']) {
          $this->flash->set_flash_message('Member updated successfully');
          Utilities::redirect('/loyalty-members/list');
        } else {
          $page_error = $result['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
      } else {
        $form_errors = $form_validation['errors'];
      }
    } elseif(!is_null($request->get('memberCode'))) {
      $member_code = $request->get('memberCode');
      $member_details = $this->loyalty_model->get_loyalty_member_details($member_code);
      if($member_details['status']===false) {
        $this->flash->set_flash_message('Invalid member (or) member does not exists',1);         
        Utilities::redirect('/loyalty-members/list');
      } else {
        $submitted_data = $member_details['member_details'];
      }
    } else {
      $this->flash->set_flash_message('Invalid member code',1);         
      Utilities::redirect('/loyalty-members/list');
    }

    // prepare form variables.
    $template_vars = array(
      'submitted_data' => $submitted_data,
      'form_errors' => $form_errors,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,      
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Loyalty Programme',
      'icon_name' => 'fa fa-diamond',
    );

    // render template
    return array($this->template->render_view('update-loyalty-member',$template_vars),$controller_vars);
  }

  public function listLoyaltyMembers(Request $request) {

    $members_a = $search_params = $location_ids = $location_codes = [];
    $page_error = '';
    
    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }

    # parse request parameters.
    $location_code = $request->get('locationCode')!==null ? Utilities::clean_string($request->get('locationCode')) : '';
    $member_name = $request->get('memberName')!==null ? Utilities::clean_string($request->get('memberName')) : '';    
    $page_no = $request->get('pageNo')!==null ? Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = 100;

    $search_params = array(
      'locationCode' => $location_code,
      'memberName' => $member_name,
      'pageNo' => $page_no,
      'perPage' => $per_page,
    );

    $api_response = $this->loyalty_model->get_loyalty_members($search_params);
    // dump($api_response);
    // exit;
    if($api_response['status']) {
      if(count($api_response['data']['members'])>0) {
          $slno = Utilities::get_slno_start(count($api_response['data']['members']),$per_page,$page_no);
          $to_sl_no = $slno+$per_page;
          $slno++;
          if($page_no <= 3) {
            $page_links_to_start = 1;
            $page_links_to_end = 10;
          } else {
            $page_links_to_start = $page_no-3;
            $page_links_to_end = $page_links_to_start+10;            
          }
          if($api_response['data']['total_pages']<$page_links_to_end) {
            $page_links_to_end = $api_response['data']['total_pages'];
          }
          if($api_response['data']['this_page'] < $per_page) {
            $to_sl_no = ($slno+$api_response['data']['this_page'])-1;
          }
          $members_a = $api_response['data']['members'];
          $total_pages = $api_response['data']['total_pages'];
          $total_records = $api_response['data']['total_records'];
          $record_count = $api_response['data']['this_page'];
      } else {
        $page_error = $api_response['apierror'];
      }
    } else {
      $page_error = $api_response['apierror'];
    }

    // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'members' => $members_a,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params,
      'client_locations' => ['' => 'All Stores'] + $client_locations,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Loyalty Members Register',
      'icon_name' => 'fa fa-diamond',
    );

    // render template
    return array($this->template->render_view('loyalty-members-list',$template_vars),$controller_vars);
  }

  public function getLoyaltyMemberLedger(Request $request) {

    $transactions_a = $member_info = $location_ids = $location_codes = $query_totals = [];
    $page_error = '';
    
    $total_pages = $total_records = $record_count = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    $page_no = $request->get('pageNo')!==null ? Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = 100;

    # check member code is present or not.
    if(!is_null($request->get('memberCode'))) {
      $member_code = Utilities::clean_string($request->get('memberCode'));
      $member_details = $this->loyalty_model->get_loyalty_member_details($member_code);
      if($member_details['status']===false) {
        $this->flash->set_flash_message('Invalid member (or) member does not exists',1);         
        Utilities::redirect('/loyalty-members/list');
      }
    } else {
      $this->flash->set_flash_message('Invalid member (or) member does not exists',1);         
      Utilities::redirect('/loyalty-members/list');      
    }

    # ---------- get location codes from api -----------------------
    $client_locations = Utilities::get_client_locations(true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
      $location_codes[$location_key_a[1]] = $location_key_a[0];      
    }    

    $api_response = $this->loyalty_model->get_loyalty_member_ledger($member_code);
    if($api_response['status']) {
      if( count($api_response['ledger']) > 0 ) {
        $slno = Utilities::get_slno_start(count($api_response['ledger']['tran']['transactions']),$per_page,$page_no);
        $to_sl_no = $slno + $per_page;
        $slno++;
        if($page_no <= 3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;            
        }
        if($api_response['ledger']['tran']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $api_response['ledger']['tran']['total_pages'];
        }
        if($api_response['ledger']['tran']['this_page'] < $per_page) {
          $to_sl_no = ($slno+$api_response['ledger']['tran']['this_page'])-1;
        }
        $transactions_a = $api_response['ledger']['tran']['transactions'];
        $query_totals = $api_response['ledger']['tran']['query_totals'];
        $total_pages = $api_response['ledger']['tran']['total_pages'];
        $total_records = $api_response['ledger']['tran']['total_records'];
        $record_count = $api_response['ledger']['tran']['this_page'];
        $member_info = $api_response['ledger']['info'];
      } else {
        $page_error = $api_response['apierror'];
      }
    }

    // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'transactions' => $transactions_a,
      'member_info' => $member_info,
      'query_totals' => $query_totals,
      'member_code' => $member_code,
      'total_pages' => $total_pages,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'location_ids' => $location_ids,
      'location_codes' => $location_codes,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Loyalty Members Ledger',
      'icon_name' => 'fa fa-diamond',
    );

    // render template
    return array($this->template->render_view('loyalty-member-ledger',$template_vars),$controller_vars);
  }

  private function _validate_form_data($form_data=array()) {
    $cleaned_params = $errors = [];

    $member_name = Utilities::clean_string($form_data['memberName']);
    $mobile_no = Utilities::clean_string($form_data['mobileNo']);
    $card_no = Utilities::clean_string($form_data['cardNo']);
    $ref_card_no = Utilities::clean_string($form_data['refCardNo']);
    $location_code = Utilities::clean_string($form_data['locationCode']);

    if(ctype_alpha(str_replace(' ', '', $member_name))) {
      $cleaned_params['memberName'] = $member_name;
    } else {
      $errors['memberName'] = 'Invalid member name.';
    }
    if(is_numeric($mobile_no) && strlen($mobile_no)===10) {
      $cleaned_params['mobileNo'] = $mobile_no;
    } else {
      $errors['mobileNo'] = 'Invalid mobile number.';
    }
    if(is_numeric($card_no)) {
      $cleaned_params['cardNo'] = $card_no;
    } else {
      $errors['cardNo'] = 'Invalid card number.';
    }
    if(is_numeric($ref_card_no)) {
      $cleaned_params['refCardNo'] = $ref_card_no;
    } else {
      $errors['refCardNo'] = 'Invalid card number.';
    }

    if((int)$ref_card_no === (int)$card_no) {
      $errors['refCardNo'] = 'Card number and referral card number should not be same.';
    }

    if(ctype_alnum($location_code) && $location_code !== '') {
      $cleaned_params['locationCode'] = $location_code;
    } else {
      $errors['locationCode'] = 'Invalid store name.';
    }
    if(count($errors)>0) {
      return array('status' => false, 'errors' => $errors);
    } else {
      return array('status' => true,'cleaned_params' => $cleaned_params);
    }
  }
}