<?php
class exit_controller extends Controller{

    //Так как контроллер маленький, тут у нас почти ничего не будет и все действия в контроллере
	function index_action()
	{
        session_destroy();
        header("location:".conf::ROOT_URL);
	}
    
}
?>