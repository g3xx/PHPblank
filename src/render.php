<?php declare(strict_types = 1);

namespace HelloBD;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment as Twig;


class Render{

    public $view;
    public $respone;
    public $request;

    function __construct(Twig $view, Response $response, Request $request){

        $this->view = $view;
        $this->request = $request;
        $this->response = $response;

    }

	public function path($path){
        $param = $this->request->get($path);
        return $param;

    }

	public function get($arg){
        $param = json_decode($this->request->getContent(), true);
        return $param[$arg];
    }

	public function send_text($code){
        $this->response->headers->set('Content-Type', 'text/plain');
        //$this->response->headers->set('Content-Type', 'text/html');
		$this->response->setContent($code);
        $this->response->prepare($this->request);
        $this->response->send();
    }

	public function send_html($tpl, $var = array() ){
        $html = $this->view->load($tpl)->display($var);
        $this->response->setContent($html);
    }

    public function send_json($obj = array() ){
        $this->response->headers->set('Content-Type', 'application/json ');
        $this->response->setContent( json_encode($obj));
        $this->response->prepare($this->request);
        $this->response->send();
    }

    public function encrypt($str){
        return base64_encode(base64_encode($str));
    }

}