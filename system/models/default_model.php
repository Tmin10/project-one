<?php

class default_model extends model {
    
    public function get_data()
	{	
       $return=array();
        if (!sys::is_autorised())
        {
           
        }
        return $return;
    }
    
    public function get_mail_auth_random()
	{	
       $rnd=rand(10000, getrandmax());
       $_SESSION['mail_auth_random']=$rnd;
       return $rnd;
    }
    
}