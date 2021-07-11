<?php 

namespace ClothingRm\Sales\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;
use Atawa\Config\Config;
use Atawa\S3;

use ClothingRm\Sales\Model\Einvoice;
use ClothingRm\Sales\Model\Sales;
use User\Model\User;
use Location\Model\Location;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;

class EInvoiceController {
	protected $views_path;

	public function __construct() {
    $this->template = new Template(__DIR__.'/../Views/');
    $this->flash = new Flash;
    $this->einvoice = new Einvoice;
    $this->sales = new Sales;
    $this->location_model = new Location;
	}

  // create einvoice
  public function generateEinvoiceAction(Request $request) {
    // -------- initialize variables ---------------------------
    $form_data = $errors = $form_errors = [];
    $submitted_data = $sales_details = [];
    $page_error = $page_success = '';

    $from_date = !is_null($request->get('fromDate')) ? $request->get('fromDate') : '';
    $to_date = !is_null($request->get('toDate')) ? $request->get('toDate') : '';
    $location_code = !is_null($request->get('locationCode')) ? $request->get('locationCode') : '';

    if($request->get('salesCode') && $request->get('salesCode')!=='') {
      $sales_code = Utilities::clean_string($request->get('salesCode'));
      $sales_response = $this->sales->get_sales_details($sales_code);
      // dump($sales_response);
      // exit;
      if($sales_response['status']) {
        $sales_details = $sales_response['saleDetails'];
      } else {
        $page_error = $sales_response['apierror'];
        $flash->set_flash_message($page_error,1);
        Utilities::redirect("/sales/list?fromDate=$from_date&toDate=$toDate&locationCode=$locationCode");
      }
    } else {
      $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i>Invalid Invoice No. (or) Invoice No. does not exist.',1);
      Utilities::redirect("/sales/list?fromDate=$from_date&toDate=$toDate&locationCode=$locationCode");
    }

    // ------------------------------------- check for form Submission --------------------------------
    // ------------------------------------------------------------------------------------------------
    if( count($request->request->all()) > 0 && 
        isset($sales_details['gstIrn']) && 
        $sales_details['gstIrn'] === ''
      ) {
      $submitted_data = $request->request->all();
      $cleaned_data = $this->_prepare_data_for_einvoice($submitted_data, $sales_details);
      if( $cleaned_data['status'] ) {
        $api_response = $this->einvoice->create_einvoice($cleaned_data['payload'], $cleaned_data['seller_gst_no']);
        if((bool)$api_response['status'] === false) {
          $this->flash->set_flash_message(
            '<i class="fa fa-times" aria-hidden="true"></i> '.
            '{ '.$api_response['errorcode'].' '.$api_response['errortext'].' }',
            1);
        } else {
          // dump($api_response);
          // exit;
          $eway_bill_no = $irn = $ack_no = $doc_no = '';
          if( isset($api_response['response']['EwbNo']) && (int)$api_response['response']['EwbNo']>0) {
            $message = '<i class="fa fa-check" aria-hidden="true"></i> eInvoice generated successfully with IRN No. '.
                       '{{ <span style="font-size: 16px;">'.$api_response['response']['Irn'].'</span> }}'.
                       ' with Way Bill No. (Part - A): {{ <span style="font-size: 20px;">'.$api_response['response']['EwbNo'].'</span> }}';
            $eway_bill_no = $api_response['response']['EwbNo'];                  
          } else {
            $message = '<i class="fa fa-check" aria-hidden="true"></i> eInvoice generated successfully with IRN No. '.
                       '{{ <span style="font-size: 16px;">'.$api_response['response']['Irn'].'</span> }}';            
          }

          // Update shipping information
          $shipping_info = [];
          $shipping_info['gstDocNo'] = $cleaned_data['payload']['DocDtls']['No'];
          $shipping_info['gstAckNo'] = $api_response['response']['AckNo'];
          $shipping_info['gstAckDate'] = $api_response['response']['AckDt'];
          $shipping_info['gstIrn'] = $api_response['response']['Irn'];
          $shipping_info['ewbNo'] = $api_response['response']['EwbNo'];
          $shipping_info['ewbDate'] = $api_response['response']['EwbDt'];

          // update shipping info
          $shipping_response = $this->sales->update_shipping_info($shipping_info, $sales_code);
          // dump($shipping_response);
          // exit;
          $this->flash->set_flash_message($message);
          Utilities::redirect("/sales/list?fromDate=$from_date&toDate=$to_date&locationCode=$location_code");
        }
      } else {
        $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i> '.$cleaned_data['reason'],1);
      }
    }

    // check irn already generated or not
    if($sales_details['gstIrn'] !== '') {
      $message = '<i class="fa fa-check" aria-hidden="true"></i> eInvoice already generated with IRN No. '.
                 '{{ <span style="font-size: 14px;">'.$sales_details['gstIrn'].'</span> }}'.
                 ' Ack No.: {{ <span style="font-size: 14px;">'.$sales_details['gstAckNo'].'</span>}} '.
                 ' Ack Dt.: {{ <span style="font-size: 14px;">'.date('d-m-Y h:ia', strtotime($sales_details['gstAckDate'])).'</span> }}';
      $this->flash->set_flash_message($message);
    }

    // --------------- build variables -----------------
    $controller_vars = array(
      'page_title' => 'Generate eInvoice',
      'icon_name' => 'fa fa-italic',
    );
    
    # ---------------- prepare form variables. ---------
    $template_vars = array(
      'errors' => $form_errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'form_data' => $sales_details,
      'submitted_data' => $submitted_data,
    );

    return array($this->template->render_view('generate-einvoice', $template_vars),$controller_vars);
  }

  // einvoice register
  public function eInvoiceRegister(Request $request) {
    $einvoices_a = $search_params = [];
    $page_error = '';

    $total_pages = $total_records = $record_count = $page_no = 0 ;
    $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;

    $location_details_response = $this->location_model->get_client_location_details($_SESSION['lc']);
    if(isset($location_details_response['status']) && $location_details_response['status']) {
      $location_details = $location_details_response['locationDetails'];
    } else {
      $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i> Invalid location/store detected', 1);
      Utilities::redirect("/sales/list");      
    }

    // parse request parameters.
    $from_date = $request->get('fromDate') !== null ? Utilities::clean_string($request->get('fromDate')) : '01-'.date('m').'-'.date("Y");
    $to_date = $request->get('toDate') !== null ? Utilities::clean_string($request->get('toDate')) : date("d-m-Y");
    $page_no = $request->get('pageNo') !== null ? Utilities::clean_string($request->get('pageNo')) : 1;
    $per_page = 100;

    $search_params = array(
      'fromDate' => $from_date,
      'toDate' => $to_date,
      'page' => $page_no,
      'limit' => $per_page,
    );

    // $seller_gst_no = '29AABCT1332L000';
    $seller_gst_no = $location_details['locGstNo'];
    $api_response = $this->einvoice->get_all_einvoices($search_params, $seller_gst_no);
    // dump($api_response);
    if($api_response['status']) {
      if(count($api_response['data'])>0) {
        $slno = Utilities::get_slno_start(count($api_response['data']),$per_page,$page_no);
        $total_pages = ceil( $api_response['totalRecords'] / $api_response['limit'] );
        $to_sl_no = $slno + $per_page;
        // dump($slno, $to_sl_no);
        if($page_no <= 3) {
          $page_links_to_start = 1;
          $page_links_to_end = 10;
        } else {
          $page_links_to_start = $page_no-3;
          $page_links_to_end = $page_links_to_start+10;            
        }
        if($total_pages < $page_links_to_end) {
          $page_links_to_end = $total_pages;
        }
        if(count($api_response['data']) < $per_page) {
          $to_sl_no = ($slno+count($api_response['data']))-1;
        }
        $einvoices_a = $api_response['data'];
        $total_records = $api_response['totalRecords'];
        $record_count = count($api_response['data']);
      } else {
        $page_error = $api_response['apierror'];
      }
    } elseif(isset($api_response['apierror']) && $api_response['apierror'] !== '') {
      $page_error = $api_response['apierror'];
    }

    // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'einvoices' => $einvoices_a,
      'total_pages' => $total_pages ,
      'total_records' => $total_records,
      'record_count' => $record_count,
      'sl_no' => $slno,
      'to_sl_no' => $to_sl_no,
      'page_links_to_start' => $page_links_to_start,
      'page_links_to_end' => $page_links_to_end,
      'current_page' => $page_no,
      'search_params' => $search_params,
      'seller_gst_no' => $seller_gst_no,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'eInvoices Register',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('einvoices-list', $template_vars),$controller_vars);
  }

  // view einvoice
  public function eInvoiceView(Request $request) {

    $location_ids = [];

    // --------------- get location codes from api ------------------------
    $client_locations = Utilities::get_client_locations(true, false, true);
    foreach($client_locations as $location_key => $location_value) {
      $location_key_a = explode('`', $location_key);
      $location_ids[$location_key_a[1]] = $location_value;
    }

    $invoice_code = !is_null($request->get('invoiceCode')) ? Utilities::clean_string($request->get('invoiceCode')) :'';
    $doc_no = !is_null($request->get('docNo')) ? Utilities::clean_string($request->get('docNo')) : null;
    if($invoice_code === '') {
      $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i> Invalid Invoice Code', 1);
      Utilities::redirect("/einvoices/list");
    } elseif($invoice_code !== 'unknown') {
      $invoice_details = $this->sales->get_sales_details($invoice_code);
      if(!$invoice_details['status']) {
        $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i> Invalid Invoice', 1);
        Utilities::redirect("/einvoices/list");      
      }
    }

    $seller_gst_no = $invoice_details['saleDetails']['locGstNo'];
    // $seller_gst_no = '29AABCT1332L000';
    $api_response = $this->einvoice->get_einvoice_details($seller_gst_no, $doc_no);
    $status = $api_response['status'];
    if(!$status) {
      $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i> Invalid Document No. {{ '.$doc_no.' }}',1);
      Utilities::redirect("/einvoices/list");
    }

    // build variables
    $controller_vars = array(
      'page_title' => 'View eInvoice',
      'icon_name' => 'fa fa-inr',
    );

    $template_vars = array(
      'seller_gst_no' => $seller_gst_no,
      'doc_no' => $doc_no,
      'einvoice_details' => $api_response['response'],
      'location_ids' => $location_ids,
    );

    // render template
    return array($this->template->render_view('einvoice-view', $template_vars),$controller_vars);
  }

  // cancel einvoice
  public function cancelEinvoice(Request $request) {
    $seller_gst_no = !is_null($request->get('gstNo')) ? Utilities::clean_string($request->get('gstNo')) :'29AABCT1332L000';
    $irn = !is_null($request->get('irn')) ? Utilities::clean_string($request->get('irn')) :'';
    $invoice_code = !is_null($request->get('invoiceCode')) ? Utilities::clean_string($request->get('invoiceCode')) : '';

    if($irn === '') {
      $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i> Invalid IRN', 1);
      Utilities::redirect("/einvoices/list");
    }

    if($invoice_code !== 'unknown') {
      $invoice_details = $this->sales->get_sales_details($invoice_code);
      if(!$invoice_details['status']) {
        $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i> Invalid Invoice', 1);
        Utilities::redirect("/einvoices/list");      
      }
    }

    $seller_gst_no = $invoice_details['saleDetails']['locGstNo'];

    $cancel_reasons = [
      1 => 'Duplicate',
      2 => 'Data entry mistake',
      3 => 'Order Cancelled',
      4 => 'Others',
    ];

    $cleaned_data = $errors = $submitted_data = [];

    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      // validate data
      if(isset($submitted_data['cancelReason']) && 
         in_array($submitted_data['cancelReason'], array_keys($cancel_reasons)) 
        ) {
        $cleaned_data['CnlRsn'] = $submitted_data['cancelReason'];
      } else {
        $errors['cancelReason'] = 'Invalid Cancel Reason';
      }
      if(isset($submitted_data['cancelRemarks']) && $submitted_data['cancelRemarks'] !== '') {
        if(strlen($submitted_data['cancelRemarks']) > 100 ) {
          $errors['cancelRemarks'] = 'Remarks must be less than 100 characters';
        } else {
          $cleaned_data['CnlRem'] = Utilities::clean_string($submitted_data['cancelRemarks']);
        }
      }

      if(count($errors)>0) {
        $form_errors = $errors;
      } else {
        /* call api */
        $cleaned_data['Irn'] = $irn;
        $api_response = $this->einvoice->cancel_einvoice($cleaned_data, $seller_gst_no);
        if((bool)$api_response['status'] === false) {
          $this->flash->set_flash_message(
            '<i class="fa fa-times" aria-hidden="true"></i> '.
            '{ '.$api_response['errorcode'].' '.$api_response['errortext'].' }',
            1);
        } else {
          $message = '<i class="fa fa-check" aria-hidden="true"></i> IRN No. '.
                     '{{ <span style="font-size: 14px;">'.$irn.'</span> }} cancelled successfully.';
          Utilities::redirect("/einvoices/list");                      
        }
      }
    }

    // build variables
    $controller_vars = array(
      'page_title' => 'Cancel IRN',
      'icon_name' => 'fa fa-inr',
    );

    $template_vars = array(
      'seller_gst_no' => $seller_gst_no,
      'cancel_reasons' => [0 => "Choose"] + $cancel_reasons,
      'form_data' => $submitted_data,
      'form_errors' => $errors,
      'irn' => $irn,
      'invoice_details' => isset($invoice_details['saleDetails']) ? $invoice_details['saleDetails'] : [],
    );

    // render template
    return array($this->template->render_view('einvoice-cancel', $template_vars),$controller_vars);    
  }

  // generate qrcode
  public function generateQrCode(Request $request) {
    $data = $request->get('data') !== null ? Utilities::clean_string($request->get('data')) : '';
    $irn = $request->get('irn') !== null ? Utilities::clean_string($request->get('irn')) : '';
    if( strlen($data) > 0) {
      $s3_config = Config::get_s3_details();

      $client_code = $_SESSION['ccode'];
      $file_name = $irn.'.png';
      $s3_url = 'https://'.$s3_config['BUCKET_NAME'].'.'.$s3_config['END_POINT_FULL'].'/'.$client_code;
      $file_url = $s3_url.'/einvqrcodes/'.$file_name;
      if (!file_exists($file_url)) {
        $qr_code = new QrCode($data);
        $qr_code->setSize(210);
        $qr_code->setMargin(5);
        $qr_code->setWriterByName('png');
        $qr_code->setEncoding('UTF-8');
        $local_file_name = __DIR__.'/../../../../bulkuploads/einvqrcodes/'.$file_name;
        $qr_code->writeFile($local_file_name);

        $s3 = new S3($s3_config['IAM_KEY'], $s3_config['IAM_SECRET'], false, $s3_config['END_POINT_FULL'], $s3_config['END_POINT_SHORT']);
        $meta_headers = [
          'Content-Disposition' => "inline; filename=$file_name",
          'Content-Type' => 'image/png',
        ];
        $key_name = $client_code.'/einvqrcodes/'.$file_name;
        $upload_result = $s3->putObjectFile($local_file_name, 
                                            $s3_config['BUCKET_NAME'], 
                                            $key_name, 
                                            S3::ACL_PUBLIC_READ, 
                                            [], 
                                            $meta_headers
                                          );
      } // second if
      return $file_url;
    } //main if
    exit;
  }

  // generate eway bill
  public function generateEwayBill(Request $request) {

    $seller_gst_no = !is_null($request->get('gstNo')) ? Utilities::clean_string($request->get('gstNo')) :'29AABCT1332L000';
    $irn = !is_null($request->get('irn')) ? Utilities::clean_string($request->get('irn')) :'';
    $invoice_code = !is_null($request->get('invoiceCode')) ? Utilities::clean_string($request->get('invoiceCode')) : '';

    if($irn === '') {
      $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i> Invalid IRN', 1);
      Utilities::redirect("/einvoices/list");
    }

    if($invoice_code !== 'unknown') {
      $invoice_details = $this->sales->get_sales_details($invoice_code);
      if(!$invoice_details['status']) {
        $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i> Invalid Invoice', 1);
        Utilities::redirect("/einvoices/list"); 
      }
    }

    $seller_gst_no = $invoice_details['saleDetails']['locGstNo'];    

    $transport_modes = [
      1 => 'Road',
      2 => 'Rail',
      3 => 'Air',
      4 => 'Ship',
    ];

    $vehicle_types = [
      'O' => 'ODC',
      'R' => 'Regular',
    ];

    $cleaned_params = $errors = $form_data = [];

    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $validation = $this->_validate_ewaybill_data($form_data, $transport_modes, $vehicle_types);
      if($validation['status']) {
        $cleaned_params = $validation['cleaned_params'];
        $cleaned_params['Irn'] = $irn;
        $api_response = $this->einvoice->generate_ewaybill($cleaned_params, $seller_gst_no);
        if((bool)$api_response['status'] === false) {
          $this->flash->set_flash_message(
            '<i class="fa fa-times" aria-hidden="true"></i> '.
            '{ '.$api_response['errorcode'].' '.$api_response['errortext'].' }',
            1);
        } else {
          $message  = '<i class="fa fa-check" aria-hidden="true"></i>&nbsp;';
          $message .= 'eWayBill generated successfully with eWayBillNo: {{ '.$api_response['response']['EwbNo'].' }} Valid till: '.
                      '{{ '.date("d-M-Y h:ia", strtotime($api_response['response']['EwbValidTill'])).' }}';
          $this->flash->set_flash_message($message);
        }
        Utilities::redirect("/einvoices/list"); 
      } else {
        $errors = $validation['errors'];
      }
    }

    // build variables
    $controller_vars = array(
      'page_title' => 'Generate eWayBill from IRN',
      'icon_name' => 'fa fa-bus',
    );

    $template_vars = array(
      'seller_gst_no' => $seller_gst_no,
      'transport_modes' => [0 => "Choose"] + $transport_modes,
      'vehicle_types' =>  [0 => "Choose"] + $vehicle_types,
      'form_data' => $form_data,
      'form_errors' => $errors,
      'irn' => $irn,
      'invoice_details' => isset($invoice_details['saleDetails']) ? $invoice_details['saleDetails'] : [],
    );

    // render template
    return array($this->template->render_view('einvoice-generate-ewaybill', $template_vars),$controller_vars);    
  }

  // prepare data for einvoice
  private function _prepare_data_for_einvoice($form_data=[], $sales_details=[]) {
    // dump($form_data, $sales_details);
    // exit;
    // $seller_gst_no = $sales_details['locGstNo'];
    // $buyer_gst_no = $sales_details['customerGstNo'];
    // dump($sales_details);
    // exit;

    // validate document number..
    if(strlen($form_data['gstInvoiceNo']) === 0 || strlen($form_data['gstInvoiceNo']) > 15) {
      return ['status' => false, 'reason' => 'Invalid GST Document No.'];
    }    

    // igst or (cgst and sgst)
    if((int)$sales_details['stateID'] > 0 && (int)$sales_details['locStateID'] > 0) {
      if((int)$sales_details['stateID'] === (int)$sales_details['locStateID']) {
        $gst_tax_type = 'intra';
      } else {
        $gst_tax_type = 'inter';
      }
    } else {
      $gst_tax_type = 'intra';
    }    

    // $seller_gst_no = '29AABCT1332L000';
    $seller_gst_no = $sales_details['locGstNo'];

    $buyer_gst_no = $sales_details['customerGstNo'];
    $distance = $form_data['distance'];

    // retrieve buyer details.
    $buyer_gst_response = $this->einvoice->get_gst_details($seller_gst_no, $buyer_gst_no);
    // dump($buyer_gst_response);
    // exit;
    if( isset($buyer_gst_response['status']) &&
        isset($buyer_gst_response['gstnDetails'])
      ) {
      if(isset($buyer_gst_response['gstnDetails']['status']) && 
         $buyer_gst_response['gstnDetails']['status'] !== 'ACT'
      ) {
        return ['status' => false, 'reason' => 'Buyer GSTIN is blocked. Unable to generate eInvoice.'];
      }
    } else {
      return ['status' => false, 'reason' => 'Unable to fetch details from GST portal.'];
    }

    // retrieve sellter details.
    // $seller_gst_no = '29AABCT1332L000';
    $seller_gst_response = $this->einvoice->get_gst_details($seller_gst_no, $seller_gst_no);
    // dump($seller_gst_response, 'seller....');
    // exit;
    if( isset($seller_gst_response['status']) &&
        isset($seller_gst_response['gstnDetails'])
      ) {
      if(isset($seller_gst_response['gstnDetails']['status']) && 
         $seller_gst_response['gstnDetails']['status'] !== 'ACT'
      ) {
        return ['status' => false, 'reason' => 'Seller GSTIN is blocked. Unable to generate eInvoice.'];
      }
    } else {
      return ['status' => false, 'reason' => 'Unable to fetch details from GST portal.'];
    }
    // dump($buyer_gst_response, $seller_gst_response);
    // exit;

    $payload = $item_list = [];
    $payload['Version'] = '1.1'; // hardcoded always
    $payload['TranDtls'] = [
      'TaxSch' => "GST", // hardcoded always
      'SupTyp' => "B2B", // hardcoded always
      'RegRev' => "N", 
      'EcmGstin' => null, 
      'IgstOnIntra' => "N",
    ];
    $payload['DocDtls'] = [
      'Typ' => 'INV',  // hardcoded always
      'No' => $form_data['gstInvoiceNo'],
      'Dt' => date("d/m/Y", strtotime($sales_details['invoiceDate'])),
    ];

    // add buyer details to payload.
    $buyer_addr1 = $buyer_gst_response['gstnDetails']['addrBnm'].','.
                   $buyer_gst_response['gstnDetails']['addrBno'];
    $buyer_addr2 = $buyer_gst_response['gstnDetails']['addrFlno'].','.
                   $buyer_gst_response['gstnDetails']['addrSt'];
    $payload['BuyerDtls'] = [
      "Gstin" => $buyer_gst_response['gstnDetails']['gstNo'],
      "TrdNm" => $buyer_gst_response['gstnDetails']['tradeName'],
      "LglNm" => !is_null($buyer_gst_response['gstnDetails']['legalName']) ? 
                 $buyer_gst_response['gstnDetails']['legalName'] : 
                 $buyer_gst_response['gstnDetails']['tradeName'],
      "Pos" => (string)$buyer_gst_response['gstnDetails']['stateCode'],
      "Addr1" => $buyer_addr1,
      "Loc" => $buyer_gst_response['gstnDetails']['addrLoc'],
      "Pin" =>  (int)$buyer_gst_response['gstnDetails']['addrPncd'],
      "Stcd" => (string)$buyer_gst_response['gstnDetails']['stateCode'],
    ];
    if(strlen($buyer_addr2) > 3) {
      $payload['BuyerDtls']['Addr2'] = $buyer_addr2;
    }

    // add seller details to payload.
    $seller_addr1 = $seller_gst_response['gstnDetails']['addrBnm'].','.
                   $seller_gst_response['gstnDetails']['addrBno'];
    $seller_addr2 = $seller_gst_response['gstnDetails']['addrFlno'].','.
                   $seller_gst_response['gstnDetails']['addrSt'];    
    $payload['SellerDtls'] = [
      "Gstin" => $seller_gst_response['gstnDetails']['gstNo'],
      "TrdNm" => $seller_gst_response['gstnDetails']['tradeName'],
      "LglNm" => !is_null($seller_gst_response['gstnDetails']['legalName']) ? 
                 $seller_gst_response['gstnDetails']['legalName']:
                 $seller_gst_response['gstnDetails']['tradeName'],
      "Addr1" => $seller_addr1,
      "Loc" => is_null($seller_gst_response['gstnDetails']['addrLoc']) ? 'null' : $seller_gst_response['gstnDetails']['addrLoc'],
      "Pin" =>  (int)$seller_gst_response['gstnDetails']['addrPncd'],
      "Stcd" => (string)$seller_gst_response['gstnDetails']['stateCode'],
    ];
    if(strlen($seller_addr2) > 3) {
      $payload['SellerDtls']['Addr2'] = $seller_addr2;
    }

    if(strlen($form_data['transporterGstin']) > 0) {
      if((int)$distance > 0 && (int)$distance <= 4000) {
        $payload['EwbDtls'] = [
          'Transid' => $form_data['transporterGstin'],
          'Distance' => (int)$distance,
        ];
      } else {
        return ['status' => false, 'reason' => 'If Transporter id is provided, distance is mandatory between 1 to 4000 kms.'];
      }
    }

    $item_details_array = $sales_details['itemDetails'];
    $tot_ass_value = $tot_cgst_value = $tot_sgst_value = $tot_igst_value = 0;
    $tot_total_amount = 0;
    $tot_discount = $tot_inv_value = $rnd_off_amount = 0;
    $tot_total_item_val = 0;
    foreach($sales_details['itemDetails'] as $item_slno => $item_details) {
      $gst_item_array = [];
      $item_qty = (float)$item_details['itemQty'];

      $total_amount = (float)round($item_details['mrp']*$item_qty,2);
      $ass_amount = (float)round($total_amount-$item_details['discountAmount'], 2);
      $tax_percent = $item_details['taxPercent'];

      $tax_value = (float)round(($ass_amount*$tax_percent/100),2);
      $cgst_value = $sgst_value = round($tax_value/2,2);

      // compute correct tax values...
      if($gst_tax_type === 'intra') {
        $sgst_amount = $sgst_value;
        $cgst_amount = $cgst_value;
        $igst_amount = 0;
      } else {
        $igst_amount = round($tax_value, 2);
        $sgst_amount = $cgst_amount = 0;
      }

      $total_item_val = round($ass_amount + $sgst_amount + $cgst_amount + $igst_amount, 2);
      $slno_string = $item_slno+1;

      $gst_item_array['SlNo'] = "$item_slno";
      $gst_item_array['IsServc'] = $item_details['itemType'] === 'p' ? 'N' : 'Y';
      $gst_item_array['PrdDesc'] = $item_details['itemName'];
      $gst_item_array['HsnCd'] = $item_details['hsnSacCode'];
      if(strlen($item_details['barcode']) >= 3) {
        $gst_item_array['Barcde'] = $item_details['barcode'];
      }
      if(strlen($item_details['uomName']) >= 3 && strlen($item_details['uomName']) <= 8) {
        $gst_item_array['Unit'] = $item_details['uomName'];
      }
      $gst_item_array['Qty'] = round($item_details['itemQty'],2);
      $gst_item_array['FreeQty'] = 0;
      $gst_item_array['UnitPrice'] = round($item_details['mrp'],2);
      $gst_item_array['Discount'] = round($item_details['discountAmount'],2);
      $gst_item_array['GstRt'] = round($item_details['taxPercent'],2);
      $gst_item_array['TotAmt'] = round($total_amount,2);
      $gst_item_array['AssAmt'] = round($ass_amount, 2);

      if($igst_amount > 0) {
        $gst_item_array['IgstAmt'] = round($igst_amount,2);
      } else {
        $gst_item_array['SgstAmt'] = round($sgst_amount,2);
        $gst_item_array['CgstAmt'] = round($cgst_amount,2);
      }

      $gst_item_array['TotItemVal'] = $total_item_val;
      $gst_item_array['PreTaxVal'] = 0;
      $gst_item_array['CesRt'] = 0;
      $gst_item_array['CesAmt'] = 0;
      $gst_item_array['CesNonAdvlAmt'] = 0;
      $gst_item_array['StateCesRt'] = 0;
      $gst_item_array['StateCesAmt'] = 0;
      $gst_item_array['StateCesNonAdvlAmt'] = 0;
      $gst_item_array['OthChrg'] = 0;

      $item_list[] = $gst_item_array;

      $tot_total_amount += $total_amount;
      $tot_ass_value += $ass_amount;
      $tot_cgst_value += $cgst_amount;
      $tot_sgst_value += $sgst_amount;
      $tot_igst_value += $igst_amount;
      $tot_discount += $item_details['discountAmount'];
      $tot_total_item_val += $total_item_val;

      // dump($ass_amount.'  ass amount'.$slno_string);
    }

    // dump($item_list);
    // exit;

    $payload['ItemList'] = $item_list;
    $round_off = round(round($tot_total_item_val, 0) - $tot_total_item_val, 2);

    // dump($tot_ass_value, $tot_cgst_value, $tot_sgst_value, $tot_igst_value, $tot_discount);
    // exit;

    $payload['ValDtls']['AssVal'] = round($tot_ass_value,2);
    // $payload['ValDtls']['Discount'] = round($tot_discount,2);
    $payload['ValDtls']['RndOffAmt'] = round($round_off,2);
    $payload['ValDtls']['TotInvVal'] = round($tot_total_item_val+$round_off, 0);
    $payload['ValDtls']['CgstVal'] = round($tot_cgst_value,2);
    $payload['ValDtls']['SgstVal'] = round($tot_sgst_value,2);
    $payload['ValDtls']['IgstVal'] = round($tot_igst_value,2);

    // Additional Details
    $payload['RefDtls']['InvRm'] = $form_data['locationID'].'__'.$form_data['invoiceCode'].
                                   '__'.$form_data['invoiceNo'];
    // dump($payload);
    // echo json_encode($payload);
    // dump($seller_gst_response, $buyer_gst_response);
    // exit;
    
    return ['status' => true, 'payload' => $payload, 'seller_gst_no' => $seller_gst_no];
  }

  // prepare data for ewaybill by irn
  private function _validate_ewaybill_data($form_data = [], $transport_modes=[], $vehicle_types=[]) {
    $form_errors = $cleaned_params = [];

    $transporter_id = Utilities::clean_string($form_data['transporterId']);
    $distance = Utilities::clean_string($form_data['distance']);
    $transport_mode = (int)Utilities::clean_string($form_data['transportMode']);
    $transporter_name = Utilities::clean_string($form_data['transporterName']);
    $vehicle_type = Utilities::clean_string($form_data['vehicleType']);
    $vehicle_no = Utilities::clean_string($form_data['vehicleNo']);
    $transport_doc_no = Utilities::clean_string($form_data['transportDocNumber']);
    $transport_doc_date = Utilities::clean_string($form_data['transportDocDate']);

    if(Utilities::validate_gst_no($transporter_id)) {
      $cleaned_params['TransId'] = $transporter_id; 
    } else {
      $form_errors['transporterId'] = 'Invalid Transporter Id';
    }
    if(is_numeric($distance) && $distance > 0 && $distance <= 4000 ) {
      $cleaned_params['Distance'] = (int)$distance; 
    } else {
      $form_errors['distance'] = 'Invalid Distance. Must be 1 to 4000';
    }

    if($transport_mode > 0) {
      if(in_array($transport_mode, array_keys($transport_modes))) {
        $cleaned_params['TransMode'] = (string)$transport_mode;
        if($transport_mode === 1) {
          if(ctype_alnum($vehicle_no) && strlen($vehicle_no) >= 4 && strlen($vehicle_no) <= 100 ){
            $cleaned_params['VehNo'] = $vehicle_no;
          } else {
            $form_errors['vehicleNo'] = "Invalid Vehicle No.";
          }
          if(in_array($vehicle_type, array_keys($vehicle_types))) {
            $cleaned_params['VehType'] = $vehicle_type;
          } else {
            $form_errors['vehicleType'] = 'Invalid Vehicle Type';
          }
        } else {
          if($transport_doc_no !== '' && strlen($transport_doc_no) <= 15) {
            $cleaned_params['TransDocNo'] = $transport_doc_no;
          } else {
            $form_errors['transportDocNumber'] = 'Invalid Transport Document Number';
          }
          if($transport_doc_date !== '') {
            $cleaned_params['TransDocDt'] = $transport_doc_date;
          } else {
            $form_errors['transportDocDate'] = 'Invalid Transport Document Date';
          }
        }
      } else {
        $form_errors['transportMode'] = 'Invalid Transport Mode';
      }
    }

    // dump($cleaned_params, $form_errors);
    // exit;

    //return data
    if(count($form_errors) > 0) {
      return [
        'status' => false,
        'errors' => $form_errors,
      ];
    } else {
      return [
        'status' => true,
        'cleaned_params' => $cleaned_params,
      ];
    }
  }
}
