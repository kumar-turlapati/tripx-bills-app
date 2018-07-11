<?php 

namespace ClothingRm\Purchases\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;

use ClothingRm\Purchases\Model;
use ClothingRm\Purchases\Model\Purchases;
use ClothingRm\Suppliers\Model\Supplier;

class PurchasesController
{
  protected $views_path;

  public function __construct() 
    {
    $this->views_path = __DIR__.'/../Views/';
  }

    public function purchaseEntryAction(Request $request)
    {

        # initialize variables.
        $errors = $credit_days_a = array();
        $page_error = $page_success = $purchase_code = '';
        $submitted_data = $purchase_details = $suppliers = $search_params = array();
        $path_info = $request->getpathInfo();
        $suppliers_a = array(''=>'Choose');
        $edit_transaction = false;

        $qtys_a = array(0=>'Sel');
        // for($i=1;$i<=1000;$i++) {
        //     $qtys_a[$i] = $i;
        // }

        # check request source.
        $update_flag = strpos($path_info, 'purchase/update');

        # assign template variables.
        $search_params['pagination'] = 'no';

        for($i=1;$i<=365;$i++) {
            $credit_days_a[$i] = $i;
        }        
        
        # initiate purchase model.
        $purchases = new Purchases;
        $supplier_api_call = new Supplier;
        $suppliers = $supplier_api_call->get_suppliers(0,0,$search_params);
        if($suppliers['status']) {
            $suppliers_a += $suppliers['suppliers'];
        }

        if($update_flag) {
            if($request->get('purchaseCode') && $request->get('purchaseCode')!=='') {
                $purchase_code = Utilities::clean_string($request->get('purchaseCode'));
                $purchase_response = $purchases->get_purchase_details($purchase_code);
                // dump($purchase_response);
                // exit;
                if($purchase_response['status']===true) {
                    $purchase_details = $purchase_response['purchaseDetails'];
                    if($purchase_details['grnFlag']==='yes') {
                        $page_error = 'GRN is already generated for this PO. Edit action not allowed. You can only view this transaction.';
                    }
                } else {
                    $page_error =   $purchase_response['apierror'];
                    Utilities::set_flash_message($page_error,1);
                    Utilities::redirect('/purchase/entry');
                }
                $page_title = 'Purchases';
                $btn_label = 'Save';
                $edit_transaction = true;
            } else {
                Utilities::set_flash_message('Invalid purchase transaction for edit',1);
                Utilities::redirect('/purchase/entry');
            }
        } else {
            $supplier_code = '';
            $btn_label = 'Save';
            $page_title = 'Purchases';
        }

        if(count($request->request->all())>0) {
            $submitted_data = $request->request->all();
            if(count($purchase_details)>0) {
                $purch_response = $purchases->updatePurchase($request->request->all(), $purchase_code);
            } else {
                $purch_response = $purchases->createPurchase($request->request->all());
            }

            // dump($purch_response);

            $status = $purch_response['status'];
            if($status === false) {
                if(isset($purch_response['errors'])) {
                    $errors = $purch_response['errors'];
                    $submitted_data = $request->request->all();
                } elseif(isset($purch_response['apierror'])) {
                    $page_error =   $purch_response['apierror'];
                }
            } elseif($update_flag===false) {
                $page_success   = 'Purchase entry added successfully with code ['.$purch_response['purchaseCode'].']';
                Utilities::set_flash_message($page_success);
                Utilities::redirect('/purchase/list');                
            } else {
                $page_success   = 'Purchase entry updated successfully';
                Utilities::set_flash_message($page_success);
                Utilities::redirect('/purchase/list');                
            }
        } elseif(count($purchase_details)>0) {
            $submitted_data = $purchase_details;
        }

        // prepare form variables.
        $template_vars = array(
            'page_error' => $page_error,
            'page_success' => $page_success,
            'status' =>  Constants::$RECORD_STATUS,
            'submitted_data' => $submitted_data,
            'errors' => $errors,
            'btn_label' => $btn_label,
            'payment_methods' => Constants::$PAYMENT_METHODS_PURCHASE,
            'credit_days_a' => array(0=>'Choose') +$credit_days_a,            
            'purchase_code' => $purchase_code,
            'suppliers' => $suppliers_a,
            'qtys_a' => $qtys_a,
            'tax_a' => Constants::$VAT_PERCENTS,
            'edit_transaction' => $edit_transaction,
        );

        // build variables
        $controller_vars = array(
            'page_title' => $page_title,
            'icon_name' => 'fa fa-compass',
        );

        // render template
        $template = new Template($this->views_path);
        return array($template->render_view('purchase-create', $template_vars), $controller_vars);
    }

    public function purchaseEntryActionNew(Request $request)
    {
      # initialize variables.
      $errors = $credit_days_a = array();
      $page_error = $page_success = $purchase_code = '';
      $submitted_data = $purchase_details = $suppliers = $search_params = array();
      $path_info = $request->getpathInfo();
      $suppliers_a = array(''=>'Choose');
      $edit_transaction = false;

      $qtys_a = array(0=>'Sel');

      # check request source.
      $update_flag = strpos($path_info, 'purchase/update');

      # assign template variables.
      $search_params['pagination'] = 'no';

      for($i=1;$i<=365;$i++) {
        $credit_days_a[$i] = $i;
      }        
        
      # initiate purchase model.
      $purchases = new Purchases;
      $supplier_api_call = new Supplier;
      $suppliers = $supplier_api_call->get_suppliers(0,0,$search_params);
      if($suppliers['status']) {
        $suppliers_a += $suppliers['suppliers'];
      }

      if($update_flag) {
        if($request->get('purchaseCode') && $request->get('purchaseCode')!=='') {
          $purchase_code = Utilities::clean_string($request->get('purchaseCode'));
          $purchase_response = $purchases->get_purchase_details($purchase_code);
          // dump($purchase_response);
          // exit;
          if($purchase_response['status']===true) {
            $purchase_details = $purchase_response['purchaseDetails'];
            if($purchase_details['grnFlag']==='yes') {
              $page_error = 'GRN is already generated for this PO. Edit action not allowed. You can only view this transaction.';
            }
          } else {
            $page_error =   $purchase_response['apierror'];
            Utilities::set_flash_message($page_error,1);
            Utilities::redirect('/purchase/entry-new');
          }
          $page_title = 'Purchases';
          $btn_label = 'Save';
          $edit_transaction = true;
        } else {
          Utilities::set_flash_message('Invalid purchase transaction for edit',1);
          Utilities::redirect('/purchase/entry-new');
        }
      } else {
        $supplier_code = '';
        $btn_label = 'Save';
        $page_title = 'Purchases';
      }

      if(count($request->request->all())>0) {
        $submitted_data = $request->request->all();
        if(count($purchase_details)>0) {
          $purch_response = $purchases->updatePurchase($submitted_data, $purchase_code);
        } else {
          $purch_response = $purchases->createPurchase($submitted_data);
        }

        // dump($purch_response);

        $status = $purch_response['status'];
        if($status === false) {
          if(isset($purch_response['errors'])) {
            $errors = $purch_response['errors'];
          } elseif(isset($purch_response['apierror'])) {
            $page_error =   $purch_response['apierror'];
          }
        } elseif($update_flag===false) {
          $page_success   = 'Purchase entry added successfully with code ['.$purch_response['purchaseCode'].']';
          Utilities::set_flash_message($page_success);
          Utilities::redirect('/purchase/list');                
        } else {
          $page_success   = 'Purchase entry updated successfully';
          Utilities::set_flash_message($page_success);
          Utilities::redirect('/purchase/list');                
        }
      } elseif(count($purchase_details)>0) {
        $submitted_data = $purchase_details;
      }

      // prepare form variables.
      $template_vars = array(
        'page_error' => $page_error,
        'page_success' => $page_success,
        'status' =>  Constants::$RECORD_STATUS,
        'submitted_data' => $submitted_data,
        'errors' => $errors,
        'btn_label' => $btn_label,
        'payment_methods' => Constants::$PAYMENT_METHODS_PURCHASE,
        'credit_days_a' => array(0=>'Choose') +$credit_days_a,            
        'purchase_code' => $purchase_code,
        'suppliers' => $suppliers_a,
        'qtys_a' => $qtys_a,
        'tax_a' => Constants::$VAT_PERCENTS,
        'edit_transaction' => $edit_transaction
      );

      // build variables
      $controller_vars = array(
        'page_title' => $page_title,
        'icon_name' => 'fa fa-compass',
      );

      // render template
      $template = new Template($this->views_path);
      return array($template->render_view('purchase-create-new', $template_vars), $controller_vars);
    }    

    /*
    public function purchaseRemoveAction(Request $request)
    {
        if($request->get('supplierCode') && $request->get('supplierCode')!='') {
            $supplier_code = Utilities::clean_string($request->get('supplierCode'));
        } else {
            Utilities::set_flash_message("Invalid Supplier Code",1);
            Utilities::redirect('/suppliers/list');
        }

        # initiate supplier model.
        $suppliers = new Supplier;            
        
        $supplier_response = $suppliers->get_supplier_details($supplier_code);
        if($supplier_response['status']===true) {
            $supplier_details = $supplier_response['supplierDetails'];
        } else {
            Utilities::set_flash_message("Invalid Supplier Code",1);
            Utilities::redirect('/suppliers/list');                
        }

        $remove_response = $suppliers->removeSupplier($supplier_code);
        if($remove_response['status']===true) {
            $flash_message = "Supplier '$supplier_details[supplierName]' removed successfully";
            Utilities::set_flash_message($flash_message);            
        } else {
            $flash_message = "Unable to remove Supplier";
            Utilities::set_flash_message($flash_message,1);
        }
        
        Utilities::redirect('/suppliers/list');
    }*/

    public function purchaseListAction(Request $request)
    {

        $suppliers= $search_params = $suppliers_a = $purchases_a = array();

        $total_pages = $total_records = $record_count = $page_no = 0 ;
        $slno = $to_sl_no = $page_links_to_start =  $page_links_to_end = 0;
        $page_success = $page_error = '';

        $supplier_api_call = new Supplier;
        $purchase_api_call = new Purchases;
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
          $search_params = $request->request->all();
        } else {
            if( !is_null($request->get('fromDate')) ) {
              $search_params['fromDate'] = $request->get('fromDate');
            }
            if( !is_null($request->get('toDate')) ) {
              $search_params['toDate'] =  $request->get('toDate');
            }
            if( !is_null($request->get('supplierID')) ) {
              $search_params['supplierID'] =  $request->get('supplierID');
            }
        }

        $supplier_api_call = new Supplier;
        $suppliers = $supplier_api_call->get_suppliers(0,0);
        if($suppliers['status']) {
          $suppliers_a = array(''=>'All Suppliers')+$suppliers['suppliers'];
        }

        $purchase_api_call = $purchase_api_call->get_purchases($page_no,$per_page,$search_params);
        $api_status = $purchase_api_call['status'];        

        # check api status
        if($api_status) {

            # check whether we got products or not.
            if(count($purchase_api_call['purchases'])>0) {
                $slno = Utilities::get_slno_start(count($purchase_api_call['purchases']), $per_page, $page_no);
                $to_sl_no = $slno+$per_page;
                $slno++;

                if($page_no<=3) {
                    $page_links_to_start = 1;
                    $page_links_to_end = 10;
                } else {
                    $page_links_to_start = $page_no-3;
                    $page_links_to_end = $page_links_to_start+10;            
                }

                if($purchase_api_call['total_pages']<$page_links_to_end) {
                    $page_links_to_end = $purchase_api_call['total_pages'];
                }

                if($purchase_api_call['record_count'] < $per_page) {
                    $to_sl_no = ($slno+$purchase_api_call['record_count'])-1;
                }

                $purchases_a = $purchase_api_call['purchases'];
                $total_pages = $purchase_api_call['total_pages'];
                $total_records = $purchase_api_call['total_records'];
                $record_count = $purchase_api_call['record_count'];
            } else {
                $page_error = $purchase_api_call['apierror'];
            }

        } else {
            $page_error = $purchase_api_call['apierror'];
        }

         // prepare form variables.
        $template_vars = array(
            'page_error' => $page_error,
            'page_success' => $page_success,
            'suppliers' => $suppliers_a,
            'purchases' => $purchases_a,
            'total_pages' => $total_pages ,
            'total_records' => $total_records,
            'record_count' =>  $record_count,
            'sl_no' => $slno,
            'to_sl_no' => $to_sl_no,
            'search_params' => $search_params,            
            'page_links_to_start' => $page_links_to_start,
            'page_links_to_end' => $page_links_to_end,
            'current_page' => $page_no,
        );

        // build variables
        $controller_vars = array(
            'page_title' => 'Purchases',
            'icon_name' => 'fa fa-compass',
        );

        // render template
        $template = new Template($this->views_path);
        return array($template->render_view('purchase-register', $template_vars), $controller_vars);       
    }

}