<?php
if (isset($data['login_error']))
   echo "<p class='error'>Неверный пароль!</p>";
?>

<?php 
if (isset($data['errors']))
{
    echo "<p>";
    foreach ($data['errors'] as $error)
        echo $error."<br />";
    echo "</p>";
}
?>
