<?php
class sys {
    
    static $mysqli;

    static function db_connect($server, $user, $password, $db)
    {
        sys::$mysqli = new mysqli($server, $user, $password, $db);
        if (sys::$mysqli->connect_errno) {
            throw new Exception("Failed to connect to MySQL (".sys::$mysqli->connect_errno.") ".sys::$mysqli->connect_error);
        }
        sys::$mysqli->set_charset("utf8");
    }
    
    static function db_disconnect()
    {
       sys::$mysqli->close();
    }

    //Проверка авторизации пользователя
    static function is_autorised()
    {
        if (isset($_SESSION['authorized'])&&$_SESSION['authorized']===true)
            return true;
        else
            return false;
    }
    
    
    //Авторизация пользователя
    static function autorization($name, $password, $chair)
    {
        //Проверяем наличие такого пользователя с email
        $SQL="SELECT persons.id, persons.person_name, persons.chair_id, 
                persons.password, chair_person.chair_id, chairs.chair_short, 
                faculty.id, faculty.faculty_short,persons.status
              FROM persons
              INNER JOIN chair_person ON chair_person.person_id=persons.id
              INNER JOIN chairs ON chairs.id=".(int)$chair."
              INNER JOIN faculty ON faculty.id=chairs.faculty_id
              WHERE persons.id='".(int)$name."' AND persons.password='".sys::secure($password)."' AND chair_person.chair_id='".(int)$chair."'";
        $result = mssql_query($SQL);
        if (mssql_num_rows($result)==1)
        {
           $row=mssql_fetch_row($result);
            
           //Пишем в сессию и отдаём ответ
           $_SESSION['authorized']=true;
           $_SESSION['id']=$row[0];
           $_SESSION['name']=$row[1];
           $_SESSION['chair']=(int)$chair;
           $_SESSION['chair_name']=$row[5];
           $_SESSION['faculty']=$row[6];
           $_SESSION['faculty_name']=$row[7];
           $_SESSION['user_status']=$row[8];
           
           return true;
        }
        else
        {
            return "error_password";
        }
    }
    
    /*
    static function my_hash($password, $salt)
    {
        $hash=md5($password).md5($salt);
        for ($i=0; $i<47; $i++)
        {
            $hash=md5($i.$hash);
        }
        return $hash;
    }
     *
     */


    /*static function registration($name, $sname, $email, $password)
    {
        if (isset($email)&&$email!=''&&isset($password)&&$password!='')//TODO Добавить проверку формата адреса почты и ошибку
        {
            //Генирируем уникальную соль для будующего пользователя
            $salt=sys::generate();
            $email_code=sys::generate('email'); //Код подтверждения email
            //проверяем уникальность логина и мыла
            $SQL="SELECT count(*) FROM users WHERE email='".sys::$mysqli->real_escape_string($email)."'";
            sys::$mysqli->real_query($SQL);
            $result = sys::$mysqli->store_result();
            $result = $result->fetch_row();
            if ($result[0]==='0')
            {
                //Продолжаем регистрацию
                $SQL="INSERT INTO users (`email`, `password_s`, `name`, `sname`, `email_activation_string`, `registration_time`) VALUES ('".sys::$mysqli->real_escape_string($email)."', '".$salt."', '".sys::$mysqli->real_escape_string($name)."', '".sys::$mysqli->real_escape_string($sname)."', '$email_code', UNIX_TIMESTAMP() )";
                sys::$mysqli->real_query($SQL);
                $last_id=sys::$mysqli->insert_id;
                //Добавляем хэш пароля пользователя
                $SQL="INSERT INTO `hashes`(`user_id`, `hash`) VALUES ('".$last_id."', '".sys::my_hash($password, $salt)."')";
                sys::$mysqli->real_query($SQL);
                //Теперь надо отправить письмо с ссылкой для активации
                //TODO ссылка вида http://social/registration/email/jgov7vfeukngtimkvbde3ph
                
                //Сразу авторизируем нового пользователя, чтобы не мучать его
                sys::autorization($email, $password);
                $_SESSION['email']=$email;
                return true;
            }
            else 
            {
                return "mail_error";
            }
        }
        else 
        {
            if (!isset($email)||$email=='')
                return 'void_email';
            else
                return 'void_password';
        }
    }*/
    
    static function generate($type='salt')
    {
        $uniq=false;
        while(!$uniq)
        {
            $arr = array('q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', 
                        'a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l',
                        'z', 'x', 'c', 'v', 'b', 'n', 'm',
                        '1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
            $salt='';
            for($i = 0; $i < 20; $i++) 
            {
                $index = rand(0, count($arr) - 1);
                $salt .= $arr[$index];
            }
            switch ($type)
            {
                case 'salt':
                    $SQL="SELECT count(*) FROM users WHERE password_s='".$salt."'";
                    break;
                case 'email':
                    $SQL="SELECT count(*) FROM users WHERE email_activation_string='".$salt."'";
                    break;
                case 'session':
                    $SQL="SELECT count(*) FROM sessions WHERE hash='".$salt."'";
                    break;
            }
            sys::$mysqli->real_query($SQL);
            $result = sys::$mysqli->store_result();
            $result = $result->fetch_row();
            if ($result[0]==='0')
                $uniq=true;
        }
        return $salt;
    }
    
    /*
    static function save_password()
    {
        $SQL="SELECT * FROM sessions WHERE user_id='".$_SESSION['id']."'";
        sys::$mysqli->real_query($SQL);
        $result = sys::$mysqli->store_result();
        if ($result->num_rows<1)
        {
            //Если у пользователя ещё нет сессии, то откроем её
            $session=sys::generate('session');
            $SQL="INSERT INTO sessions (`user_id`, `hash`, `create_time`) VALUES ('".$_SESSION['id']."', '".$session."', UNIX_TIMESTAMP())";
            sys::$mysqli->real_query($SQL);
        }
        else
        {
            //Если же у пользователя уже начата сессия, то проверим время действия
            $result = $result->fetch_row();
            $session=$result[1];
            if ($result[2]+conf::SESSION_TIME-time()<1)
            {
                //Если сессия уже не актуальна, то обновим её
                $SQL="UPDATE `sessions` SET `create_time`=UNIX_TIMESTAMP() WHERE user_id='".$_SESSION['id']."'";
                sys::$mysqli->real_query($SQL);
            }
        }
        //Поставим куку
        setcookie('password_session', $session, time()+conf::SESSION_TIME, '/');
    }
    */
    
    /*
    static function cookie_password()
    {
        if (!sys::is_autorised())
        {
            if (isset($_COOKIE['password_session']))
            {
                $SQL="SELECT user_id, create_time FROM sessions WHERE hash='".sys::$mysqli->real_escape_string($_COOKIE['password_session'])."'";
                sys::$mysqli->real_query($SQL);
                $result = sys::$mysqli->store_result();
                if ($result->num_rows==1)
                {
                    //Если сессия найдена и хэш есть в базе, то проверим, а не истекла ли сессия
                    $result = $result->fetch_row();
                    if ($result[1]+conf::SESSION_TIME>time())
                    {
                        //Сессия не истекла, авторизируем
                        $SQL="SELECT id, password_s, login, email_v, acc_v, name, sname
                        FROM users 
                        WHERE id='".$result[0]."'";
                        sys::$mysqli->real_query($SQL);
                        $result = sys::$mysqli->store_result();
                        $row=$result->fetch_row();

                        //Пишем в сессию и отдаём ответ
                        $_SESSION['authorized']=true;
                        $_SESSION['id']=$row[0];
                        $_SESSION['login']=$row[2];
                        $_SESSION['email_v']=$row[3]; //Подтверждение мыла
                        $_SESSION['acc_v']=$row[4]; //Активация аккаунта
                        $_SESSION['name']=$row[5];
                        $_SESSION['sname']=$row[6];
                        header('location:/');
                        return true;
                    }
                }
            }
        }
        else 
            return false;
    }
     */
    /*
    static function activate_email($code)
    {
        $SQL="SELECT registration_time FROM users WHERE email_activation_string='".sys::$mysqli->real_escape_string($code)."' AND email_v=0";
        sys::$mysqli->real_query($SQL);
        $result = sys::$mysqli->store_result();
        if ($result->num_rows==1)
        {
            //Проверим, не прошло ли много времени с  
            $row=$result->fetch_row();
            if ($row[0]+345600>time())
            {
                //Код активации актуален, активируем емейл
                $SQL="UPDATE `users` SET email_v=1 WHERE email_activation_string='".sys::$mysqli->real_escape_string($code)."'";
                sys::$mysqli->real_query($SQL);
                return true;
            }
        }
        else
            return false;
    }
     * 
     */
}
