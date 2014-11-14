<?php

//Контроллер авторизации через социальные сети

class auth_controller extends Controller{
   
   function __construct()
   {
      $this->model = new auth_model();
      $this->view = new View();
   }
   
   function index_action()
   {
        
   }
   
   function mail_action($id)
   {
        $data = $this->model->mail_auth($id);
   }
   
}
