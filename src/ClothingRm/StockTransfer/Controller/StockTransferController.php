<?php 

namespace ClothingRm\StockTransfer\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Atawa\Utilities;
use Atawa\Constants;
use Atawa\Template;
use Atawa\Flash;


class StockTransferController
{
	protected $views_path,$finmodel;

	public function __construct() {
		$this->views_path = __DIR__.'/../Views/';
    $this->flash = new Flash;
	}

  # Stock transfer create action.
	public function stockTransferLocationAction(Request $request) {

    $page_error = $page_success = '';
    $submitted_data = $form_errors = $form_data = [];
    $search_params = [];

    # ---------------------- get location codes from api --------------------------------------------
    $from_locations = ['' => 'Choose a Store'] + Utilities::get_client_locations();
    $to_locations = ['' => 'Choose a Store'] + Utilities::get_client_locations(false, true);
    // unset($to_locations[$_SESSION['lc']]);

    if(count($request->request->all()) > 0) {
      $form_data = $request->request->all();
      $from_location = Utilities::clean_string($form_data['fromLocation']);
      $to_location = Utilities::clean_string($form_data['toLocation']);
      if(ctype_alnum($from_location) === false) {
        $form_errors['fromLocation'] = 'From location is required.';
      }
      if(ctype_alnum($to_location) === false) {
        $form_errors['toLocation'] = 'To location is required.';
      }
      if($from_location === $to_location) {
        $form_errors['toLocation'] = 'To location should not be same as From location.';
      }
      if(count($form_errors) <= 0) {
        $redirect_url = "/stock-transfer/out?fromLocation=$from_location&toLocation=$to_location";
        Utilities::redirect($redirect_url);
      }
    }

    # prepare form variables.
    $template_vars = array(
      'page_error' => $page_error,
      'page_success' => $page_success,
      'form_data' => $form_data,
      'form_errors' => $form_errors,
      'from_locations' => $from_locations,
      'to_locations' => $to_locations,
      'flash' => $this->flash,
    );

    # build variables
    $controller_vars = array(
      'page_title' => 'Inventory Management - Stock Transfer - Choose Location',
      'icon_name' => 'fa fa-truck',
    );

    // render template
    $template = new Template($this->views_path);
    return array($template->render_view('choose-stransfer-location', $template_vars), $controller_vars);
	}
}