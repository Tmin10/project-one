<?php

class auth_model extends model{
   
   public function mail_auth($id)
   {
      if ((int) $id !== 0 && (int) $id !== (int) $_SESSION['mail_auth_random'])
      {
         echo 'Код: '.$id;
      }
      else
      {
         $_SESSION['login_error'] = 'Ошибка авторизации, попробуйте ещё раз.';
         header("location:".conf::BASE_URL);
         die();
      }
   }
   
}
