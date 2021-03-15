<?php 

namespace Whatsapp\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Template;
use Atawa\Flash;
use Atawa\Importer;
use Atawa\CrmUtilities;

use Whatsapp\Model\Whatsapp;
use ClothingRm\Sales\Model\Sales;

class WhatsappController
{

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->whatsapp_model = new Whatsapp;
    $this->flash = new Flash;
    $this->sales = new Sales;    
	}

	// shipping update action
	public function pushShippingUpdate(Request $request) {

		$page_error = $page_success = '';
    $form_data = $form_errors = $sales_data = [];
    $client_details = Utilities::get_client_details();

    $sales_code = !is_null($request->get('ic')) ? Utilities::clean_string($request->get('ic')) : '';
    if($sales_code !== '') {
      $sales_response = $this->sales->get_sales_details($sales_code);
      if($sales_response['status']) {
        $sales_data = $sales_response['saleDetails'];
      } else {
        $page_error = $sales_response['apierror'];
        $this->flash->set_flash_message($page_error,1);
        Utilities::redirect('/sales/list');
      }
    }

    // form submit
    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      # validate form data
      $form_validation = $this->_validate_form_data($form_data);
      if($form_validation['status'] === false) {
        $form_errors = $form_validation['errors'];
        $message = '<i class="fa fa-times" aria-hidden="true"></i>&nbsp;You have errors in the Form. Please fix them before you send the message.';
        $this->flash->set_flash_message($message, 1);
      } else {
        // dump($form_data);
        // exit;
        # hit api and get the status.
        $form_data['ic'] = $sales_code;
        $api_action = $this->whatsapp_model->push_shipping_update($form_data);
        // dump($api_action);
        // exit;
        if($api_action['status']) {
          $sent_messages = $api_action['sentMessages'];
          $message = '<i class="fa fa-check-circle-o" aria-hidden="true"></i>&nbsp;'.$sent_messages.' message(s) pushed successfully.';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/sales/list');
        } else {
          // $form_errors = $api_action['errortext'];
          // $message = 'You have errors in the Form. Please fix them before you send the message.';
          $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i>&nbsp;'.$api_action['apierror'], 1);
        }
      }
    }

    # prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'form_errors' => $form_errors,
      'form_data' => $form_data,
      'flash' => $this->flash,
      'sales_data' => $sales_data,
      'delivery_contact' => isset($client_details['deliveryContact']) ? $client_details['deliveryContact'] : '',
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'Whatsapp - Push Shipping Update',
      'icon_name' => 'fa fa-whatsapp',
    );

    return array($this->template->render_view('whatsapp-shipping-update', $template_vars),$controller_vars);		
	}

	# messages list action
	public function whatsappMessagesListAction(Request $request) {

    $leads = $search_params = $lead_status_a = $lead_ratings_a = [];
    $lead_sources_a = [];

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    $page_error = $page_success = '';

    # check page no and per page variables.
    $page_no = $request->get('pageNo')!== null ? Utilities::clean_string($request->get('pageNo')):1;
    $per_page = $request->get('perPage')!== null ? Utilities::clean_string($request->get('perPage')):100;
    $lead_status_id = $request->get('leadStatusId')!== null ? Utilities::clean_string($request->get('leadStatusId')) : '';

    # hit api and get the status.
    $api_action = $this->lead_model->getAllLeads($page_no, $per_page, $lead_status_id);
    $api_status = $api_action['status'];

    # check api status
    if($api_status) {
      # check whether we got leads or not.
      if(count($api_action['leadsObject']['leads']) >0) {

        $leads = $api_action['leadsObject']['leads'];
        $slno = Utilities::get_slno_start(count($api_action['leadsObject']['leads']),$per_page,$page_no);
        $to_sl_no = $slno+$per_page;

        $slno++;

        if($page_no <= 3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;
        }
        if($api_action['leadsObject']['total_pages']<$page_links_to_end) {
          $page_links_to_end = $api_action['leadsObject']['total_pages'];
        }
        if($api_action['leadsObject']['this_page'] < $per_page) {
          $to_sl_no = ($slno+$api_action['leadsObject']['this_page'])-1;
        }
        $leads = $api_action['leadsObject']['leads'];
        $total_pages = $api_action['leadsObject']['total_pages'];
        $total_records = $api_action['leadsObject']['total_records'];
        $record_count = $api_action['leadsObject']['total_records'];
      } else {
        $page_error = $api_action['apierror'];
      }
    } else {
      $page_error = $api_action['apierror'];
    }    

    # prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'leads' => $leads,
      'total_pages' => $total_pages,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params,
      'lead_status_a' => array(''=>'Lead Status') + CrmUtilities::get_lead_status(),
      'lead_sources_a' => CrmUtilities::get_lead_source(),
      'lead_ratings_a' => CrmUtilities::get_lead_rating(),
      'lead_industries_a' => CrmUtilities::get_crm_industries(),
      'lead_status_id' => $lead_status_id,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'CRM - Leads',
      'icon_name' => 'fa fa-users',
    );

    return array($this->template->render_view('lead-list', $template_vars),$controller_vars);
	}

  # validate form data
  private function _validate_form_data($form_data=[]) {
    $errors = $cleaned_params = [];

    $customer_name = Utilities::clean_string($form_data['customerName']);
    $whatsapp_numbers = Utilities::clean_string($form_data['whatsappNo']);
    $order_nos = Utilities::clean_string($form_data['orderNos']);
    $invoice_nos = Utilities::clean_string($form_data['invoiceNos']);
    $transporter_name = Utilities::clean_string($form_data['transporterName']);
    $lr_case_nos = Utilities::clean_string($form_data['lrCaseNos']);
    $lr_date = Utilities::clean_string($form_data['lrDate']);
    $eway_bill_no = Utilities::clean_string($form_data['eWayBillNo']);
    $contact_number = Utilities::clean_string($form_data['contactNoForQueries']);
    $show_disclaimer = Utilities::clean_string($form_data['showDisclaimer']);
    // $disclaimer_message = Utilities::clean_string($form_data['disclaimerMessage']);
    $disclaimer_message = 'Please note that this message is for information purposes only and not used as an authorization to receive the Goods from the Shipping agency.';

    if($customer_name === '') {
      $errors['customerName'] = 'Invalid customer name.';
    } else {
      $cleaned_params['customerName'] = $customer_name;
    }

    if($whatsapp_numbers !== '' && Utilities::validate_multiple_mobile_numbers(',', $whatsapp_numbers)) {
      $cleaned_params['whatsappNo'] = $whatsapp_numbers;
    } else {
      $errors['whatsappNo'] = 'Invalid whatsapp number.';
    }

    if($order_nos !== '' && Utilities::validate_multiple_order_numbers(',', $order_nos)) {
      $cleaned_params['orderNos'] = $order_nos;
    } else {
      $errors['orderNos'] = 'Invalid order number.';
    }

    if($invoice_nos === '') {
      $errors['invoiceNos'] = 'Invalid invoice number.';
    } else {
      $cleaned_params['invoiceNos'] = $invoice_nos;
    }

    if($transporter_name === '') {
      $errors['transporterName'] = 'Invalid transporter name.';
    } else {
      $cleaned_params['transporterName'] = $transporter_name;
    }

    if($lr_case_nos === '') {
      $errors['lrCaseNos'] = 'Invalid input.';
    } else {
      $cleaned_params['lrCaseNos'] = $lr_case_nos;
    }

    if($contact_number === '') {
      $errors['contactNoForQueries'] = 'Invalid input.';
    } else {
      $cleaned_params['contactNoForQueries'] = $contact_number;
    }

    if($eway_bill_no === '') {
      $eway_bill_no = '--NA--';
    }

    if(count($errors) > 0) {
      return [
        'status' => false,
        'errors' => $errors,
      ];
    } else {
      return [
        'status' => true,
        'cleaned_params' => $cleaned_params,
      ];  
    }
  }

  # shipping update
  public function pushShippingUpdate(Request $request) {
    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      
      $sid = $form_data->sid;
      $status = $form_data->status;
      $updated = date("Y-m-d H:i:s");

      # prepare form data
      $form_data = [];
      $form_data['sid'] = $sid;
      $form_data['status'] = $status;

      # push the update to api.
      $api_action = $this->whatsapp_model->push_shipping_update($form_data);
    }
  }

}