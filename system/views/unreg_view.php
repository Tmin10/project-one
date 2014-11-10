<?php
if (isset($data['login_error']))
   echo "<p class='error'>Неверный пароль!</p>";
?>

<a href="https://connect.mail.ru/oauth/authorize?client_id=726718&response_type=code&redirect_uri=http://<?php echo conf::SITE_NAME.conf::BASE_URL ?>auth/mail">Авторизация в Mail.ru</a>

<?php 
if (isset($data['errors']))
{
    echo "<p>";
    foreach ($data['errors'] as $error)
        echo $error."<br />";
    echo "</p>";
}
?>
