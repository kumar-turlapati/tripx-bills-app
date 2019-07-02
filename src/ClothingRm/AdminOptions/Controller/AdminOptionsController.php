<?php

namespace ClothingRm\AdminOptions\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;
use Atawa\ApiCaller;
use Atawa\PDF;

use ClothingRm\Sales\Model\Sales;
use ClothingRm\Inventory\Model\Inventory;
use ClothingRm\Inward\Model\Inward;
use ClothingRm\Suppliers\Model\Supplier;
use ClothingRm\Grn\Model\GrnNew;
use ClothingRm\SalesReturns\Model\SalesReturns;
use ClothingRm\SalesIndent\Model\SalesIndent;
use User\Model\User;

class AdminOptionsController
{
	protected $views_path, $template, $sales_model, $inward_model, $flash;

	public function __construct() {
		$this->views_path = __DIR__.'/../Views/';
		$this->template = new Template($this->views_path);
		$this->sales_model = new Sales;
    $this->inward_model = new Inward;
    $this->inv_model = new Inventory;
    $this->user_model = new User;
		$this->flash = new Flash;
    $this->supplier_model = new Supplier;
    $this->grn_model = new GrnNew;
    $this->sales_return_model = new SalesReturns;
    $this->api_caller = new ApiCaller;
    $this->sindent_model = new SalesIndent;
	}

  // delete Org. Summary
  public function orgSummary(Request $request) {
    $org_summary = Utilities::get_org_summary();

    // prepare form variables.
    $template_vars = array(
      'org_summary' => $org_summary,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Organization Summary',
      'icon_name' => 'fa fa-server',
    );

    // render template
    return array($this->template->render_view('org-summary',$template_vars),$controller_vars);
  }

  // delete GRN
  public function deleteGRN(Request $request) {
    $form_errors = $submitted_data = $suppliers = [];
    $client_locations = $suppliers_a = [];

    // get location codes
    $client_locations = Utilities::get_client_locations();

    // get suppliers from the portal
    $suppliers = $this->supplier_model->get_suppliers(0,0,[]);
    if($suppliers['status']) {
      $suppliers_a += $suppliers['suppliers'];
    }

    // check for form Submission
    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_po_details($submitted_data, $client_locations);
      if($form_validation['status']===false) {
        $form_errors = $form_validation['errors'];
      } else {
        $api_response = $this->grn_model->deleteGRN($form_validation['cleaned_params']);
        if($api_response['status']) {
          $this->flash->set_flash_message('GRN Deleted successfully. PO No `'.$form_validation['cleaned_params']['vocNo'].'` is editable now.');
          Utilities::redirect('/admin-options/delete-grn');
        } else {
          $page_error = $api_response['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
      }
    }

    // prepare form variables.
    $template_vars = array(
      'errors' => $form_errors,
      'submitted_data' => $submitted_data,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'suppliers' => array(''=>'Choose')+$suppliers_a,
      'flash_obj' => $this->flash,
      'voc_type' => 'GRN',
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Delete GRN',
      'icon_name' => 'fa fa-laptop',
    );

    // render template
    return array($this->template->render_view('inward-info',$template_vars),$controller_vars);    
  }

  // delete PO
  public function deletePO(Request $request) {
    $form_errors = $submitted_data = $suppliers = [];
    $client_locations = $suppliers_a = [];

    // get location codes
    $client_locations = Utilities::get_client_locations();

    // get suppliers from the portal
    $suppliers = $this->supplier_model->get_suppliers(0,0,[]);
    if($suppliers['status']) {
      $suppliers_a += $suppliers['suppliers'];
    }

    // check for form Submission
    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_po_details($submitted_data, $client_locations);
      if($form_validation['status']===false) {
        $form_errors = $form_validation['errors'];
      } else {
        $api_response = $this->inward_model->delete_po($form_validation['cleaned_params']);
        if($api_response['status']) {
          $this->flash->set_flash_message('PO deleted successfully');
          Utilities::redirect('/admin-options/delete-po');
        } else {
          $page_error = $api_response['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
      }
    }

    // prepare form variables.
    $template_vars = array(
      'errors' => $form_errors,
      'submitted_data' => $submitted_data,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'suppliers' => array(''=>'Choose')+$suppliers_a,
      'flash_obj' => $this->flash,
      'voc_type' => 'PO',
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Delete Purchase Order',
      'icon_name' => 'fa fa-keyboard-o',
    );

    // render template
    return array($this->template->render_view('inward-info',$template_vars),$controller_vars);    
  }

  // delete Invoice
  public function deleteInvoice(Request $request) {
    $form_errors = $submitted_data = [];

    // get location codes
    $client_locations = Utilities::get_client_locations();

    // check for form Submission
    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_invoice_details($submitted_data, $client_locations);
      if($form_validation['status']===false) {
        $form_errors = $form_validation['errors'];
      } else {
        $api_response = $this->sales_model->remove_sales_transaction($submitted_data);
        if($api_response['status']) {
          $this->flash->set_flash_message('Invoice Deleted successfully.');
          Utilities::redirect('/admin-options/delete-invoice');
        } else {
          $page_error = $api_response['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
      }
    }    

     // prepare form variables.
    $template_vars = array(
      'errors' => $form_errors,
      'submitted_data' => $submitted_data,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'flash_obj' => $this->flash,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Delete Invoice',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('delete-invoice',$template_vars),$controller_vars);
  }

  // delete Sales Return
  public function deleteSalesReturn(Request $request) {
    $form_errors = $submitted_data = [];

    // get location codes
    $client_locations = Utilities::get_client_locations();

    // check for form Submission
    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_sales_return_details($submitted_data, $client_locations);
      if($form_validation['status']===false) {
        $form_errors = $form_validation['errors'];
      } else {
        $submitted_data = $form_validation['cleaned_params'];
        $api_response = $this->sales_return_model->delete_sales_return($submitted_data);
        if($api_response['status']) {
          $this->flash->set_flash_message('<i class="fa fa-times" aria-hidden="true"></i> Sales Return Voucher Deleted successfully.');
          Utilities::redirect('/admin-options/delete-invoice');
        } else {
          $page_error = '<i class="fa fa-times" aria-hidden="true"></i> '.$api_response['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
      }
    }    

     // prepare form variables.
    $template_vars = array(
      'errors' => $form_errors,
      'submitted_data' => $submitted_data,
      'client_locations' => array(''=>'Choose') + $client_locations,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'flash_obj' => $this->flash,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Delete Sales Return',
      'icon_name' => 'fa fa-repeat',
    );

    // render template
    return array($this->template->render_view('delete-sales-return',$template_vars),$controller_vars);
  }  

  // deleted vouchers
  public function deletedVouchers(Request $request) {
    $form_errors = $submitted_data = [];

    // get location codes
    $client_locations = Utilities::get_client_locations();
    $voc_types = Constants::$VOC_TYPES;
    asort($voc_types);

    // check for form Submission
    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_delete_register_params($submitted_data, $client_locations);
      if($form_validation['status']===false) {
        $form_errors = $form_validation['errors'];
      } else {
        $submitted_data = $form_validation['cleaned_params'];
        $submitted_voc_type = $voc_types[$submitted_data['vocType']];
        $api_response = $this->_get_deleted_vouchers($submitted_data);
        if($api_response['status']) {
          $total_records = $api_response['response']['vouchers'];
          $total_pages = $api_response['response']['total_pages'];
          if($total_pages>1) {
            for($i=2;$i<=$total_pages;$i++) {
              $submitted_data['pageNo'] = $i;
              $api_response = $this->_get_deleted_vouchers($submitted_data);
              if($api_response['status']) {
                $total_records = array_merge($total_records,$api_response['response']['vouchers']);
              }
            }
          }

          // dump($total_records);
          // exit;

          // start PDF printing.
          $heading1 = 'Deleted Vouchers - '.$submitted_voc_type;
          $item_widths = array(15,20,20,20,115);
          //                    0, 1, 2, 3, 4
          $slno = 0;

          $pdf = PDF::getInstance();
          $pdf->AliasNbPages();
          $pdf->AddPage('P','A4');

          // Print Bill Information.
          $pdf->SetFont('Arial','B',16);
          $pdf->Cell(0,0,$heading1,'',1,'C');
          $pdf->SetFont('Arial','B',10);
          $pdf->Ln(5);
          $pdf->Cell($item_widths[0],6,'Sno.','LRTB',0,'C');
          $pdf->Cell($item_widths[1],6,'Voc. No','RTB',0,'C');
          $pdf->Cell($item_widths[2],6,'Voc. Date','RTB',0,'C');
          $pdf->Cell($item_widths[3],6,'Deleted on','RTB',0,'C');
          $pdf->Cell($item_widths[4],6,'Reason','RTB',0,'C');
          $pdf->SetFont('Arial','',9);

          foreach($total_records as $record_details) {
            $slno++;
            $voc_no = $record_details['vocNo'];
            $voc_date = date("d-m-Y", strtotime($record_details['vocDate']));
            if($record_details['delDate'] !== '0000-00-00 00:00:00') {
              $del_date = date("d-m-Y", strtotime($record_details['delDate']));
            } else {
              $del_date = '';
            }
            $remarks = substr($record_details['remarks'],0,50);
            $pdf->Ln();
            $pdf->Cell($item_widths[0],6,$slno,'LRTB',0,'R');
            $pdf->Cell($item_widths[1],6,$voc_no,'RTB',0,'R');
            $pdf->Cell($item_widths[2],6,$voc_date,'RTB',0,'R');
            $pdf->Cell($item_widths[3],6,$del_date,'RTB',0,'R');
            $pdf->Cell($item_widths[4],6,$remarks,'RTB',0,'R');
          }
        
          $pdf->Output();

        } else {
          $page_error = '<i class="fa fa-times" aria-hidden="true"></i> '.$api_response['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
      }
    }

    // prepare form variables.
    $template_vars = array(
      'client_locations' => [''=>'Choose'] + $client_locations,
      'voc_types' => [''=>'Choose'] + $voc_types,
      'errors' => $form_errors,
      'submitted_data' => $submitted_data,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'flash_obj' => $this->flash,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Deleted Vouchers Register',
      'icon_name' => 'fa fa-times',
    );

    // render template
    return array($this->template->render_view('deleted-vouchers-register',$template_vars),$controller_vars);    
  }

  // export indents
  public function exportIndents(Request $request) {
    $form_errors = $submitted_data = [];

    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $form_validation = $this->_validate_export_indent_data($submitted_data);
      if($form_validation['status']===false) {
        $form_errors = $form_validation['errors'];
      } else {
        $submitted_data = $form_validation['cleaned_params'];
        $api_response = $this->sindent_model->export_indents($submitted_data);
        if($api_response['status']) {
          $this->_dump_csv_for_indents($api_response['response']);
        } else {
          $page_error = '<i class="fa fa-times" aria-hidden="true"></i> '.$api_response['apierror'];
          $this->flash->set_flash_message($page_error, 1);
        }
      }
    }

    // prepare form variables.
    $template_vars = array(
      'errors' => $form_errors,
      'submitted_data' => $submitted_data,
      'default_location' => isset($_SESSION['lc']) ? $_SESSION['lc'] : '',
      'flash_obj' => $this->flash,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Export Indents to CSV',
      'icon_name' => 'fa fa-delicious',
    );

    // render template
    return array($this->template->render_view('export-indents',$template_vars),$controller_vars);    
  }

  // dump csv for indents.
  private function _dump_csv_for_indents($records = []) {
    $total_records = [];
    // dump($records);
    // exit;
    foreach($records as $record_key => $record_details) {

      $agent_address = '';
      if($record_details['agentAddress'] !== '') {
        $agent_address .= $record_details['agentAddress'];
      }
      if($record_details['agentGstNo'] !== '') {
        $gst_no = $record_details['agentGstNo'];
      }

      $amount = round($record_details['itemRate']*$record_details['itemQty'], 2);
      $moq = $record_details['mOq'];
      $gst = $record_details['taxPercent'];

      if($record_details['itemQty'] > 0 && $moq > 0) {
        $pcs = round($record_details['itemQty'] / $moq,2);
      } else {
        $pcs = '';
      }

      $total_records[$record_key] = [
        'Indent.No' => '',
        'Date' => date("d-m-Y", strtotime($record_details['indentDate'])), 
        'PartyLedger' => $record_details['customerName'],
        'PartyAddress' => '',
        'Supplytype' => '',
        'PartyGSTIN' => $gst_no,
        'Mode/Terms of Payment' => '',
        'Order Ref.No' => $record_details['indentNo'],
        'Despatch Through' => '',
        'Destination' => '',
        'Agent Name' => $record_details['agentName'], 
        'Section Name' => '', 
        'Scheme Name' => '',
        'Sales Man' => '',
        'Division' => '',
        'Brand' => $record_details['brandName'],
        'Remarks' => $record_details['remarks'],
        'Product Name' => $record_details['itemName'],
        'Units' => $record_details['uomName'],
        'HSN Code' => $record_details['hsnSacCode'],
        'GST%' => number_format($gst, 2, '.', ''),
        'Qty' => number_format($record_details['itemQty'], 2, '.', ''),
        'Due On' => '',
        'PCS' => $pcs,
        'Rate' => number_format($record_details['itemRate'], 2, '.', ''),
        'Amount' => number_format($amount, 2, '.', ''),
        'Other ref' => '',
        'Cases' => '',
      ];
    }

    $csv_file_name = 'IndentExportCSV_'.date('d-m-Y');

    Utilities::download_as_CSV_attachment($csv_file_name, [], $total_records);
    return;
  }


  // update business information
	public function editBusinessInfoAction(Request $request) {

    $form_data = $states = $form_errors = array();

    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $validation = $this->_validate_businessinfo($form_data);
      $status = $validation['status'];
      if($status) {
        $form_data = $validation['cleaned_params'];
        $result = $this->user_model->update_client_details($form_data);
        if($result['status']===true) {
          $message = 'Information updated successfully. Changes will be updated after you logout from current session.';
          $this->flash->set_flash_message($message);
          Utilities::redirect('/admin-options/edit-business-info');
        } else {
          $message = 'An error occurred while updating your information.';
          $this->flash->set_flash_message($message,1);
          Utilities::redirect('/admin-options/edit-business-info');
        }
      } else {
        $form_errors = $validation['errors'];
        $client_details = $form_data;
      }

    } else {
      // get client details.
      $client_details = $this->user_model->get_client_details()['clientDetails'];
    }

    $states_a = Constants::$LOCATION_STATES;
    asort($states_a);

    // template variables
    $template_vars = array(
      'states' => array(0=>'Choose')+$states_a,
      'page_error' => '',
      'page_success' => '',
      'form_data' => $client_details,
      'form_errors' => $form_errors,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Update Business Information',
      'icon_name' => 'fa fa-building',
    );

    // render template
    return array($this->template->render_view('edit-business-info',$template_vars),$controller_vars);
	}

  // validate indent export data inputs.
  private function _validate_export_indent_data($submitted_data = []) {
    $cleaned_params = $form_errors = [];

    $from_indent_no = isset($submitted_data['fromIndentNo']) && $submitted_data['fromIndentNo'] !== '' ? $submitted_data['fromIndentNo'] : '';
    $to_indent_no = isset($submitted_data['toIndentNo']) && $submitted_data['toIndentNo'] !== '' ? $submitted_data['toIndentNo'] : '';
    $from_date = isset($submitted_data['fromDate']) && $submitted_data['fromDate'] !== '' ? $submitted_data['fromDate'] : date("01-m-Y");
    $to_date = isset($submitted_data['toDate']) && $submitted_data['toDate'] !== '' ? $submitted_data['toDate'] : date("d-m-Y");

    if($from_indent_no === '') {
      $form_errors['fromIndentNo'] = 'Invalid Indent No.';
    } else {
      $cleaned_params['fromIndentNo'] = $from_indent_no;
    }
    if($to_indent_no === '') {
      $form_errors['toIndentNo'] = 'Invalid Indent No.';
    } else {
      $cleaned_params['toIndentNo'] = $to_indent_no;
    }    
    if(Utilities::validate_date($from_date)) {
      $cleaned_params['fromDate'] = $from_date;
    } else {
      $form_errors['fromDate'] = 'Invalid From Date';
    }
    if(Utilities::validate_date($to_date)) {
      $cleaned_params['toDate'] = $to_date;
    } else {
      $form_errors['toDate'] = 'Invalid To Date';
    }

    // dump($form_errors, $submitted_data);
    // exit;

    if(count($form_errors)>0) {
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

  // validation of business info.
  private function _validate_businessinfo($form_data=[]) {
    $cleaned_params = $errors = array();
    $image_data = '';

    $states_a = array_keys(Constants::$LOCATION_STATES);

    $business_name = Utilities::clean_string($form_data['businessName']);
    $gst_no = Utilities::clean_string($form_data['gstNo']);
    $dl_no = Utilities::clean_string($form_data['dlNo']);
    $address1 = Utilities::clean_string($form_data['address1']);
    $address2 = Utilities::clean_string($form_data['address2']);
    $state_id = Utilities::clean_string($form_data['locState']);
    $pincode = Utilities::clean_string($form_data['pincode']);
    $phones = Utilities::clean_string($form_data['phones']);

    # check logo information.
    if( isset($_FILES['logoName']) && $_FILES['logoName']['name'] !== '') {
      $file_details = $_FILES['logoName'];
      if( exif_imagetype($file_details['tmp_name']) !== 2 ) {
        $errors['logoName'] = 'Invalid Business Logo. Only .jpg or .jpeg file formats are allowed.';
      } else {
        $image_info = file_get_contents($file_details['tmp_name']);
        $image_data = 'data:' . $file_details['type'] . ';base64,' . base64_encode($image_info);
      }
    }

    if( !ctype_alnum(str_replace(' ', '', $business_name)) ) {
      $errors['businessName'] = 'Invalid business name. Only alphabets and digits are allowed.';
    } else {
      $cleaned_params['businessName'] = $business_name;
    }
    if($gst_no !== '' && strlen(str_replace('_','',$gst_no)) !== 15 ) {
      $errors['gstNo'] = 'Invalid GST No.';
    } else {
      $cleaned_params['gstNo'] = $gst_no;
    }
    if(in_array($state_id, $states_a) === false) {
      $errors['locState'] = 'Invalid State.';
    } else {
      $cleaned_params['locState'] = $state_id;
    }
    if($pincode !== '' && !is_numeric($pincode)) {
      $errors['pincode'] = 'Invalid Pincode.';
    } else {
      $cleaned_params['pincode'] = $pincode;
    }    

    $cleaned_params['dlNo'] = $dl_no;
    $cleaned_params['address1'] = $address1;
    $cleaned_params['address2'] = $address2;
    $cleaned_params['phones'] = $phones;
    $cleaned_params['logoData'] = $image_data;

    if(count($errors)>0) {
      return array('status'=>false, 'errors'=>$errors);
    } else {
      return array('status'=>true, 'cleaned_params'=>$cleaned_params);
    }
  }

  // validation for ask poinfo
  private function _validate_po_details($form_data=[], $locations=[]) {
    $cleaned_params = $errors = [];

    $voc_no = isset($form_data['vocNo']) ? Utilities::clean_string($form_data['vocNo']) : '';
    $supplier_id = isset($form_data['supplierID']) ? Utilities::clean_string($form_data['supplierID']) : '';
    $location_code = isset($form_data['locationCode']) ? Utilities::clean_string($form_data['locationCode']) : '';
    $delete_reason = isset($form_data['deleteReason']) ? Utilities::clean_string($form_data['deleteReason']) : '';
    $location_keys = array_keys($locations);

    if($voc_no === '') {
      $errors['vocNo'] = 'PO No. is required.';
    } else {
      $cleaned_params['vocNo'] = $voc_no;
    }
    if($supplier_id === '') {
      $errors['supplierID'] = 'Supplier name is required.';
    } else {
      $cleaned_params['supplierID'] = $supplier_id;
    }
    if($delete_reason === '') {
      $errors['deleteReason'] = 'Delete reason is required.';
    } else {
      $cleaned_params['deleteReason'] = $delete_reason;
    }
    if(in_array($location_code, $location_keys)) {
      $cleaned_params['locationCode'] = $location_code;
    } else {
      $errors['locationCode'] = 'Invalid store name.';
    }
    if(count($errors)>0) {
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

  // validation for invoice details.
  private function _validate_invoice_details($form_data=[], $locations=[]) {
    $cleaned_params = $errors = [];

    $voc_no = isset($form_data['vocNo']) ? Utilities::clean_string($form_data['vocNo']) : '';
    $delete_reason = isset($form_data['deleteReason']) ? Utilities::clean_string($form_data['deleteReason']) : '';
    $location_code = isset($form_data['locationCode']) ? Utilities::clean_string($form_data['locationCode']) : '';
    $location_keys = array_keys($locations);

    if($voc_no === '') {
      $errors['vocNo'] = 'Invoice Number is required.';
    } else {
      $cleaned_params['vocNo'] = $voc_no;
    }
    if(in_array($location_code, $location_keys)) {
      $cleaned_params['locationCode'] = $location_code;
    } else {
      $errors['locationCode'] = 'Invalid Store Name.';
    }
    if($delete_reason === '') {
      $errors['deleteReason'] = 'Delete reason is required.';
    } else {
      $cleaned_params['deleteReason'] = $delete_reason;
    }    
    if(count($errors)>0) {
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

  // validation for sales return voucher
  private function _validate_sales_return_details($form_data=[], $locations=[]) {
    $cleaned_params = $errors = [];

    $voc_no = isset($form_data['vocNo']) ? Utilities::clean_string($form_data['vocNo']) : '';
    $location_code = isset($form_data['locationCode']) ? Utilities::clean_string($form_data['locationCode']) : '';
    $delete_reason = isset($form_data['deleteReason']) ? Utilities::clean_string($form_data['deleteReason']) : '';
    $location_keys = array_keys($locations);

    if($voc_no === '') {
      $errors['vocNo'] = 'Voucher Number is required.';
    } else {
      $cleaned_params['vocNo'] = $voc_no;
    }
    if($delete_reason === '') {
      $errors['deleteReason'] = 'Delete reason is required.';
    } else {
      $cleaned_params['deleteReason'] = $delete_reason;
    }    
    if(in_array($location_code, $location_keys)) {
      $cleaned_params['locationCode'] = $location_code;
    } else {
      $errors['locationCode'] = 'Invalid Store Name.';
    }
    if(count($errors)>0) {
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

  // validate delete register params
  private function _validate_delete_register_params($form_data=[], $locations=[]) {
    $cleaned_params = $errors = [];
    $voc_types = Constants::$VOC_TYPES;

    $location_code = isset($form_data['locationCode']) ? Utilities::clean_string($form_data['locationCode']) : '';
    $voc_type = isset($form_data['vocType']) ? Utilities::clean_string($form_data['vocType']) : '';
    $from_date = isset($form_data['fromDate']) ? Utilities::clean_string($form_data['fromDate']) : '';
    $to_date = isset($form_data['toDate']) ? Utilities::clean_string($form_data['toDate']) : '';

    $location_keys = array_keys($locations);
    $voc_keys = array_keys($voc_types);

    // dump($voc_keys, $voc_types, $voc_type);

    if(in_array($location_code, $location_keys)) {
      $cleaned_params['locationCode'] = $location_code;
    } else {
      $errors['locationCode'] = 'Invalid store name';
    }
    if($form_data['fromDate'] !== '' && Utilities::validate_date($from_date)) {
      $cleaned_params['fromDate'] = $form_data['fromDate'];
    } else {
      $errors['fromDate'] = 'Invalid from date';
    }
    if($form_data['toDate'] !== '' && Utilities::validate_date($to_date)) {
      $cleaned_params['toDate'] = $form_data['toDate'];
    } else {
      $errors['toDate'] = 'Invalid to date';
    }
    if(in_array($voc_type, $voc_keys)) {
      $cleaned_params['vocType'] = $voc_type;
    } else {
      $errors['vocType'] = 'Invalid voucher type';
    }

    if(count($errors)>0) {
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

  // deleted vouchers register
  private function _get_deleted_vouchers($form_data = []) {
    $response = $this->api_caller->sendRequest('post', 'clients/deleted-vouchers', $form_data);
    $status = $response['status'];
    if($status === 'success') {
      return array(
        'status' => true,
        'response' => $response['response'],
      );
    } elseif($status === 'failed') {
      return array('status' => false, 'apierror' => $response['reason']);
    }
  }
}

/*
  // ask for bill no
  public function askForBillNo(Request $request) { 
    
    $page_error = $page_title = $bill_no = '';

    if(count($request->request->all()) > 0) {
      $bill_no = Utilities::clean_string($request->get('editBillNo'));
      $bill_type = Utilities::clean_string($request->get('billType'));
      if($bill_type === 'sale') {
        $bill_details = $this->sales_model->get_sales_details($bill_no,true);
        if($bill_details['status']) {
          Utilities::redirect('/admin-options/edit-sales-bill?billNo='.$bill_no);
        } else {
          $page_error = 'Invalid Bill No.';
        }       
      } elseif($bill_type === 'purc') {
        $bill_details = $this->inward_model->get_purchase_details($bill_no, true);
        if($bill_details['status']) {
          Utilities::redirect('/admin-options/edit-po?poNo='.$bill_no);
        } else {
          $this->flash->set_flash_message('Invalid PO No. (or) PO does not exist.',1);
          Utilities::redirect('/admin-options/enter-bill-no?billType=purc');
        }
      }
    }

    # check for filter variables.
    if(!is_null($request->get('billType')) && 
        $request->get('billType')!=='' &&
        ($request->get('billType') === 'sale' || $request->get('billType') === 'purc')
      ) {
      $bill_type = Utilities::clean_string($request->get('billType'));
    } else {
      $bill_type = 'sale';
    }

    switch ($bill_type) {
      case 'sale':
        $page_title = 'Edit Sales Bill';
        $label_name = 'Enter bill no. to edit';
        $icon_name = 'fa fa-inr';
        break;
      case 'purc':
        $page_title = 'Edit Purchase Bill';
        $label_name = 'Enter PO no. to edit';
        $icon_name = 'fa fa-compass';
        break;
    }

     // prepare form variables.
    $template_vars = array(
      'label_name' => $label_name,
      'page_error' => $page_error,
      'bill_no' => $bill_no,
      'bill_type' => $bill_type,
    );

    // build variables
    $controller_vars = array(
      'page_title' => $page_title,
      'icon_name' => $icon_name,
    );

    // render template
    return array($this->template->render_view('ask-for-billno',$template_vars),$controller_vars);
  }

    if(count($request->request->all()) > 0) {
      $bill_no = Utilities::clean_string($request->get('delSaleBill'));
      $bill_details = $this->sales_model->get_sales_details($bill_no,true);
      if($bill_details['status']) {
        # delete sale bill api.
        $api_response = $this->sales_model->removeSalesTransaction($bill_details['saleDetails']['invoiceCode']);
        $status = $api_response['status'];
        if($status===false) {
          if(isset($api_response['errors'])) {
            if(isset($api_response['errors']['itemDetails'])) {
              $page_error = $api_response['errors']['itemDetails'];
              unset($api_response['errors']['itemDetails']);
            }
            $errors = $api_response['errors'];
          } elseif(isset($api_response['apierror'])) {
            $page_error = $api_response['apierror'];
          }
        } else {
          $this->flash->set_flash_message('Sales transaction with Bill No. <b>'.$bill_no. '</b> deleted successfully');
          Utilities::redirect('/admin-options/delete-invoice');
        }
      } else {
        $page_error = 'Invalid Bill No. (or) Bill does not exist.';
      }
    }

  // edit sales bill with limited information
  public function editSalesBillAction(Request $request) {

    $errors = $sales_details = $submitted_data = array();
    $page_error = $page_success = '';

    if($request->get('billNo') && $request->get('billNo')!=='') {
      $bill_no = Utilities::clean_string($request->get('billNo'));
      $sales_response = $this->sales_model->get_sales_details($bill_no,true);
      if($sales_response['status']===true) {
        $sales_details = $sales_response['saleDetails'];
      } else {
        $page_error = $sales_response['apierror'];
        $flash->set_flash_message($page_error,1);
        Utilities::redirect('/admin-options/enter-bill-no');
      }
    } else {
      $this->flash->set_flash_message('Invalid Bill No. (or) Bill does not exist.',1);
      Utilities::redirect('/admin-options/enter-bill-no');
    }

    if(count($request->request->all()) > 0) {
      $submitted_data = $request->request->all();
      $sales_code = $sales_details['invoiceCode'];
      $sales_response = $this->sales_model->updateSale($submitted_data,$sales_code);
      $status = $sales_response['status'];
      if($status===false) {
        if(isset($sales_response['errors'])) {
          if(isset($sales_response['errors']['itemDetails'])) {
            $page_error = $sales_response['errors']['itemDetails'];
            unset($sales_response['errors']['itemDetails']);
          }
          $errors = $sales_response['errors'];
        } elseif(isset($sales_response['apierror'])) {
          $page_error = $sales_response['apierror'];
        }
      } else {
        $this->flash->set_flash_message('Sales transaction with Bill No. <b>'.$sales_details['billNo']. '</b> updated successfully');
        $redirect_url = '/admin-options/edit-sales-bill?billNo='.$bill_no;
        Utilities::redirect($redirect_url);
      }

    } elseif(count($sales_details)>0) {
      $submitted_data = $sales_details;
    }

    $qtys_a = array(0=>'Sel');
    $doctors_a = array(-1=>'Choose', 0=>'D.M.O')+$this->sales_model->get_doctors();
    $ages_a[0] = 'Choose';
    for($i=1;$i<=150;$i++) {
      $ages_a[$i] = $i;
    }
    for($i=1;$i<=365;$i++) {
      $credit_days_a[$i] = $i;
    }
    for($i=1;$i<=500;$i++) {
      $qtys_a[$i] = $i;
    }

    // prepare form variables.
    $template_vars = array(
      'sale_types' => Constants::$SALE_TYPES,
      'sale_modes' => Constants::$SALE_MODES,
      'status' => Constants::$RECORD_STATUS,
      'doctors' => $doctors_a,
      'age_categories' => Constants::$AGE_CATEGORIES,
      'genders' => array(''=>'Choose') + Constants::$GENDERS,
      'payment_methods' => Constants::$PAYMENT_METHODS,
      'ages' => $ages_a,
      'credit_days_a' => array(0=>'Choose') +$credit_days_a,
      'qtys_a' => $qtys_a,
      'yes_no_options' => array(''=>'Choose', 1=>'Yes', 0=>'No'),
      'errors' => $errors,
      'page_error' => $page_error,
      'page_success' => $page_success,
      'btn_label' => 'Edit sale transaction',
      'submitted_data' => $submitted_data,
      'flash' => $this->flash,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Edit Sales Bill',
      'icon_name' => 'fa fa-inr',
    );

    // render template
    return array($this->template->render_view('edit-sale-bill',$template_vars),$controller_vars);
  }

  */