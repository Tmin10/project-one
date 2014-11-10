<?php
class default_controller extends Controller{
    function __construct()
	{
		$this->model = new default_model();
		$this->view = new View();
	}
	
	function index_action()
	{
		$data = $this->model->get_data();
        if (sys::is_autorised())
        {
           $this->view->render('default_view.php', 'main_temp.php', $data);
        }
        else
        {
           if (isset($_SESSION['login_error']))
           {
              $data['login_error']=$_SESSION['login_error'];
              unset($_SESSION['login_error']);
           }
           $this->view->render('unreg_view.php', 'unreg_temp.php', $data);
        }
	}
  

}
?>