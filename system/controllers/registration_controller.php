<?php
class registration_controller extends Controller{
    function __construct()
	{
		$this->model = new registration_model();
        $this->view = new View();
	}
	
	function index_action()
	{
        $this->model->get_data();
	}
    //Ошибка регистрации
    function error_action()
	{
        $data=array();
        if (isset($_SESSION['error']))
        {
            switch ($_SESSION['error'])
            {
                case 'mail_error':
                    $data['error']=lang::$lang['errors']['mail_error'];
                    $data['val']=$_SESSION['val'];
                    break;
                case 'void_email':
                    $data['error']=lang::$lang['errors']['void_email'];
                    $data['val']=$_SESSION['val'];
                    break;
                case 'void_password':
                    $data['error']=lang::$lang['errors']['void_password'];
                    $data['val']=$_SESSION['val'];
                    break;
            }
        $this->view->render('registration_view.php', 'unreg_temp.php', $data);
        }
    }
    //Успешная регистрация
    function success_action()
	{
        if (isset($_SESSION['email']))
        {
            $data['email']=$_SESSION['email'];
        }
        $this->view->render('registration_view.php', 'unreg_temp.php', $data);
	}
    //Активация email
    function email_action($code='')
	{
        $this->model->activate_email($code);
	}
    
    //Сброс пароля
    function reset_action()
    {
        $this->view->render('reset_view.php', 'unreg_temp.php', $data);
    }
    
    
}
