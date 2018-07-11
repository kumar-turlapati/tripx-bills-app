<?php

require_once '../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing;
use Symfony\Component\HttpKernel;

if(isset($_COOKIE['__ata__'])) {
	$cookie_string_a = explode("##",base64_decode($_COOKIE['__ata__']));
	$bc = (int)$cookie_string_a[8];
	switch ($bc) {
		case 2:
			$route_file_name = 'routes_clothing_rm.php';
			break;
		default:
			$route_file_name = 'routes_default.php';
			break;
	}
} else {
	$route_file_name = 'routes_default.php';
}

$request = Request::createFromGlobals();
$routes = include __DIR__.'/../src/'.$route_file_name;

$context = new Routing\RequestContext();
$matcher = new Routing\Matcher\UrlMatcher($routes, $context);
$resolver = new HttpKernel\Controller\ControllerResolver();

$framework = new Atawa\Framework($matcher, $resolver);
$response = $framework->handle($request);

$response->send();