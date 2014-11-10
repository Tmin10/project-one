<?php
class registration_model extends model {
    
    public function get_data()
	{
        $temp_reg=sys::registration($_POST['name'], $_POST['sname'], $_POST['email'], $_POST['pass']);
        if ($temp_reg===true)
        {
            header("location:".conf::BASE_URL.'registration/success');
        }
        else
        {
            if (isset($_POST['name']))
            {
                $_SESSION['val']['name']=htmlspecialchars($_POST['name']);
            }
            if (isset($_POST['sname']))
            {
                $_SESSION['val']['sname']=htmlspecialchars($_POST['sname']);
            }
            if (isset($_POST['email']))
            {
                $_SESSION['val']['email']=htmlspecialchars($_POST['email']);
            }
            if (isset($_POST['pass']))
            {
                $_SESSION['val']['pass']=htmlspecialchars($_POST['pass']);
            }
            
            switch ($temp_reg)
            {
                case 'mail_error':
                    $_SESSION['error']='mail_error';
                    header("location:".conf::BASE_URL.'registration/error?1');
                    break;
                case 'void_email':
                    $_SESSION['error']='void_email';
                    header("location:".conf::BASE_URL.'registration/error?2');
                    break;
                case 'void_password':
                    $_SESSION['error']='void_password';
                    header("location:".conf::BASE_URL.'registration/error?3');
                    break;
            }
        }
    }
    
    public function activate_email()
    {
        
    }
}
