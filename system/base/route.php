<?php

class Route
{

	static function start()
	{
        // контроллер и действие по умолчанию
		$controller_name = 'default';
		$action_name = 'index';
		
        //TODO переписать роутер, а что что-то не очень
        //TODO сделать так, чтобы путь делился на 3 части: имя контроллера,имя метода и дополнительные параетры, которые будут передаваться методу
        //TODO также из обрабоки стоит ислючить параметры после ? и #
        
        if (substr($_SERVER['REQUEST_URI'], 0, strlen(conf::BASE_URL))===conf::BASE_URL)
        {
            $request=substr($_SERVER['REQUEST_URI'], strlen(conf::BASE_URL));
        }
        else
        {
            $request=substr($_SERVER['REQUEST_URI'], 1);
        }
        
        /*if (preg_match("/^id[0-9]+$/", $request))
        {
            $controller_name='user';
            $action_name='show';
            $param=substr($request, 2);
        }
        else
        {*/
            if (!(strpos($request, '?')===FALSE))
            {
               $request=strstr($request, '?', true);
            }
            if (!(strpos($request, '#')===FALSE))
            {
                $request=strstr($request, '#', true);
            }
            
            $request=explode('/', $request, 3);

            if (isset($request[0])&&$request[0]!='')
            {
                $controller_name=$request[0];
            }
            if (isset($request[1])&&$request[1]!='')
            {
               $action_name=$request[1];
            }
            if (isset($request[2])&&$request[2]!='')
            {
               $param=$request[2];
            }
        //}
        
		// добавляем префиксы
		$model_name = $controller_name.'_model';
		$controller_name = $controller_name.'_controller';
		$action_name = $action_name.'_action';

		
		echo "Model: $model_name<br>\r\n";
		echo "Controller: $controller_name<br>\r\n";
		echo "Action: $action_name<br>\r\n";
        
        if (isset($param))
        {
           echo "Param: $param <br>";
        }
        //*/
        //Получим список всех файлов контроллеров и моделей, которые у нас есть, чтобы пользователь не смог подключить какой иной файл.
        $models = glob("system/models/*.php"); //модели
        $controllers = glob("system/controllers/*.php"); //представления
        
		// подцепляем файл с классом модели (файла модели может и не быть)
        //TODO нужно проверить, что мы подключаем именно файл модели и контроллера, а не левый файл.
		$model_file = strtolower($model_name).'.php';
		$model_path = "system/models/".$model_file;
        //TODO тут будет проверка на вхождение пути файла инклуда в массив
        if (in_array($model_path, $models))
        {
            if(file_exists($model_path))
            {
                include "system/models/".$model_file;
                if (!class_exists($model_name))
                {
                   throw New Exception('model_class_not_found');
                }
            }
            else
            {
                throw new Exception('model_not_found');
            }
        }
        else
        {
             throw new Exception('model_file_not_found');
        }
		// подцепляем файл с классом контроллера
		$controller_file = strtolower($controller_name).'.php';
		$controller_path = "system/controllers/".$controller_file;
		//TODO тут будет проверка на вхождение пути файла инклуда в массив
        if (in_array($controller_path, $controllers))
        {
            if (file_exists($controller_path))
            {
                include "system/controllers/".$controller_file;
                if (!class_exists($controller_name))
                {
                    throw New Exception('controller_class_not_found');
                }
            }
            else
            {
                throw new Exception('controller_not_found');
            }
        }
        else
        {
             throw new Exception('controller_file_not_found');
        }
		// создаем контроллер
		$controller = new $controller_name;
		$action = $action_name;
        
        var_dump($controller);
        var_dump($action);
        var_dump(method_exists($controller, $action));
		die();
		if (method_exists($controller, $action))
		{
			// вызываем действие контроллера
			if (isset($param))
            {
               $controller->$action($param);
            }
            else
            {
               $controller->$action();
            }
		}
		else
		{
			throw new Exception('action_not_found');
		}
	
	}
    
}