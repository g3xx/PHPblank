<?php declare(strict_types = 1);

namespace HelloBD;

require __DIR__ .'/../vendor/autoload.php';

use Pimple\Container;
use FastRoute\RouteCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Capsule\Manager as Capsule;


class Bootstrap {

    private $request;
    private $inject;
    private $dispatcher;

    public function __construct(){

        $this->request = Request::createFromGlobals();
        $this->inject = new Container();

    }
/*
	private function LoadDb(){
        $capsule = new Capsule;
        $capsule->addConnection([
                'driver'   => 'sqlite',
            	'database' => __DIR__ .'/cclocal.db',
		   	'prefix'   => '',
		]);
        $capsule->bootEloquent();
    }
*/
	private function preloadInject(){
        $load = new \Twig\Loader\FilesystemLoader( __DIR__ . '/Views');
        $this->inject['twig'] = fn($c) => new \Twig\Environment($load, [
                'cache' => false, 'debug' => 'true',
		]);

        $this->inject['render'] = fn($c) =>
		new \HelloBD\Render($c['twig'],
		new \Symfony\Component\HttpFoundation\Response,
		new \Symfony\Component\HttpFoundation\Request);

        return $this->inject;
    }


	protected function buildRoutes(){
        $routes = require __DIR__ . '/route.php';
        $this->dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $r) use($routes) {
            foreach ($routes as $route) {
                $r->addRoute($route[0], $route[1], $route[2]);
            }
        });
        return $this->dispatcher;
    }


      public function start(){

        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();

       // $this->LoadDb();

        $routeInfo = $this->buildRoutes()->dispatch($this->request->getMethod(), $this->request->getPathInfo());
        $depency = $this->preloadInject();

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                Response::create("404 Not Found!", Response::HTTP_NOT_FOUND)->prepare($this->request)->send();
                break;
                case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    Response::create("405 Method Not Allowed!", Response::HTTP_METHOD_NOT_ALLOWED)->prepare($this->request)->send();
                    break;
                    case \FastRoute\Dispatcher::FOUND:
                        $handler = $routeInfo[1][0]; // nama class
			  $method = $routeInfo[1][1]; // method func
			  $name = lcfirst($handler);
              if( $this->request->getMethod() == "GET" ){
                  $vars = $routeInfo[2];
              }else{
                  $vars = $this->request->query->all();
              }
			$depency[$name] = fn($c) => new $handler($c['render']);
              $response = $depency[$name];
              $response->$method($vars);
              if ($response instanceof Response) {
                  $response->prepare($this->request)->send();
              }
		      break;
        }

    }

}