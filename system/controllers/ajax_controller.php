<?php
class ajax_controller extends Controller{

   function __construct()
   {
      $this->model = new ajax_model();
      $this->view = new View();
   }
   
   /*function get_chairs_action()
   {
        $data = $this->model->get_chairs();
        //У представления ajax данных будет только 1 файл
        $this->view->render('', 'ajax_view.php', $data);
   }*/
   
}