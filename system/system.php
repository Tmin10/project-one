<?php
//Начинаем сессию
session_start();
//Запчасти MVC
require_once 'base/model.php';
require_once 'base/view.php';
require_once 'base/controller.php';
require_once 'base/route.php';
//Подключение конфигурации сайта
require_once 'site_configuration.php';
//Подключаем вспомогательный класс с методами
require_once 'system_functions.php';
//Подключение языка
if (file_exists("system/langs/".conf::SITE_LANG.".php"))
{
    require_once 'system/langs/'.conf::SITE_LANG.'.php';
}
else
{
    require_once 'system/langs/ru.php';
}
try {
    //Подключаемся к БД
    sys::db_connect(conf::DB_SERVER_NAME, conf::DB_USER_NAME, conf::DB_PASSWORD, conf::DB_NAME);
    //Авторизация по кукам 
    //sys::cookie_password();
    //Роутинг запроса
    Route::start();
}
catch (Exception $e) {
    //Обработка исключений
    echo "<br />\r\n".lang::$lang['errors']['error'].": ".$e->getMessage();
}