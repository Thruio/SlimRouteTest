<?php
/**
 * Created by PhpStorm.
 * User: geusebio
 * Date: 17/02/15
 * Time: 00:21
 */

use Slim\Slim;
class SlimRouteTestCase extends PHPUnit_Framework_TestCase {

  /**
   * @param Slim $app Slim context
   * @param string $method Method of Request to emulate. POST/GET/etc
   * @param string $route_to_call Name of the Route you wish to call
   * @param null $data optional array of POST data.
   *
   * @var $app \Slim\Slim
   * @var $matched_route \Slim\Route
   * @var $context_app \Slim\Slim
   *
   * @throws \Exception
   *
   * @return \StdClass
   */
  protected function callSlimRoute(Slim $app, $method = 'GET', $route_to_call, $data = null){

    $start = microtime(true);
    $response = new \StdClass();

    $response->route = $route_to_call;
    //echo "Route to call: {$mode} => {$route_to_call}\n";
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['REQUEST_URI'] = $route_to_call;
    $_SERVER['SERVER_NAME'] = "NotARealServer";
    $_SERVER['SERVER_PORT'] = 80;
    #echo "{$mode} => {$route_to_call} with " . json_encode($data) . "\n\n";
    $matched_routes = $app->router->getMatchedRoutes($method, $route_to_call, true);
    $matched_route = end($matched_routes);

    if(!$matched_route){
      throw new \Exception("No matched route for {$method} => {$route_to_call}");
    }

    // update POST.
    if($data !== null && is_array($data) && count($data) > 0) {
      foreach ($data as $key => $value) {
        $formHash = $app->environment->offsetGet('slim.request.form_hash');
        $formHash[$key] = $value;
        $app->environment->offsetSet('slim.request.form_hash', $formHash);
      }
    }

    $matched_route->dispatch();
    $response->body = $app->response()->getBody();
    $headers = $app->response()->headers();
    $response->content_type = isset($headers['data']['Content-Type']) ? $headers['data']['Content-Type'] : null;
    $end = microtime(true);

    $response->time_to_complete = $end - $start;

    unset($app);

    return $response;
  }
}
