<?php declare(strict_types = 1);


namespace HelloBD\Controller;


use HelloBD\Render;

class IndexClass{

    private $view;

    function __construct(Render $view){
        $this->view = $view;
    }

	public function Index() {

        $this->view->send_text('hello world');
    }
	public function News() {

        $this->view->send_text('hello News');
    }
}