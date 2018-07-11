<?php 

namespace ClothingRm\Finance\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;

use ClothingRm\Finance\Model\Finance;

class FinanceController
{
	protected $views_path;

	public function __construct() {
		$this->views_path = __DIR__.'/../Views/';
    $this->fin_model = new Finance();
	}

	public function cashBook(Request $request) {

    $page_error = $page_success = '';
    $fromDate = $toDate = '';
    $records = $search_params = array();

    if(count($request->request->all()) > 0) {
      $search_params = $request->request->all();
    }

    if(!isset($search_params['fromDate'])) {
      // $fromDate = date("d-m-Y");
      $fromDate = '16-01-2017';
    } else {
      $fromDate = $search_params['fromDate'];
    }
    if(!isset($search_params['toDate'])) {
      // $toDate = date("d-m-Y");
      $toDate = '16-01-2017';
    } else {
      $toDate = $search_params['toDate'];
    }

    // dump($fromDate, $toDate);

    $api_response = $this->fin_model->cash_book($fromDate, $toDate);
    if($api_response['status'] === true) {
      $records = $api_response['records'];
    }

     // prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'fromDate' => $fromDate,
      'toDate' => $toDate,
      'records' => $records,
    );

    // build variables
    $controller_vars = array(
      'page_title' => 'Finance Management - Cash Book',
      'icon_name' => 'fa fa-list',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('cash-book', $template_vars), $controller_vars);
	}

  public function bankBook(Request $request) {
    
  }

}