<?php

namespace Atawa;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpFoundation\Cookie;
use Atawa\Template;
use Atawa\Utilities;

ini_set('date.timezone', 'Asia/Kolkata');

if(Utilities::is_session_started() === FALSE) session_start();

class Framework {

  protected $matcher;
  protected $resolver;
  protected $template;
  protected $response;

  public function __construct(UrlMatcher $matcher, ControllerResolver $resolver) {
    $this->matcher = $matcher;
    $this->resolver = $resolver;
    $this->template = new Template(__DIR__.'/../Layout/');
    $this->response = new Response;
  }

  public function handle(Request $request) {
    $this->matcher->getContext()->fromRequest($request);
    $path = $request->getPathInfo();

    // validate access token before loading any route except listed routes here.
    if(in_array($path, $this->_skip_routes_for_token_validation()) === false) {
      // check device information.
      Utilities::check_device_name();

      // check access token
      Utilities::check_access_token();

      # check ACL.
      $role_id = isset($_SESSION['utype']) && $_SESSION['utype'] >0 ? $_SESSION['utype'] : 0;
      Utilities::acls($role_id, $path);
    }

    try {
      $request->attributes->add($this->matcher->match($path));
      $controller = $this->resolver->getController($request);
      $arguments = $this->resolver->getArguments($request, $controller);
      $controller_response = call_user_func_array($controller, $arguments);
      if(is_array($controller_response) && count($controller_response)>0) {
        $controller_output = $controller_response[0];
        if(is_array($controller_response[1]) && count($controller_response[1]) > 0) {
          $view_vars = $controller_response[1];
        } else {
          $view_vars = [];
        }
      } else {
        $controller_output = $controller_response;
        $view_vars = [];
      }

      if(!isset($view_vars['render'])) {
        $view_vars['render'] = true;
      }

      if($path === '/login') {
        $page_content = $this->template->render_view('login', array('content' => $controller_output,  'path_url' => '/login','view_vars' => $view_vars));
      } elseif($view_vars['render']) {
        $page_content = $this->template->render_view('layout', array('content' => $controller_output, 'path_url' => $path, 'view_vars' => $view_vars));
      } else {
        $page_content = $controller_output;
      }
      return new Response($page_content);
    } catch (ResourceNotFoundException $e) {
      // dump($e);
      return new Response('Not Found', 404);
    } catch (\Exception $e) {
      if($_SERVER['SERVER_ADDR'] === '127.0.0.1') {
        dump($e);
      }
      return new Response('An error occurred', 500);
    }
  }

  private function _skip_routes_for_token_validation() {
    return [
      '/login', '/forgot-password', '/send-otp',
      '/reset-password', '/error-device', '',
      '/id__mapper',
      '/force-logout',
      '/__id__lo',
    ];
  }
}