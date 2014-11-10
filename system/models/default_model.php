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
    
}