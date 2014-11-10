<?php
class login_controller extends Controller{
    function __construct()
	{
		$this->model = new login_model();
        $this->view = new View();
	}
	
	function index_action()
	{
        $this->model->get_data();
	}
    
}