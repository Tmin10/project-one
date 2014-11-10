<?php
if (!isset($data['error']))
{
?>
всё хорошо, регистрация почти завершена, дальнейшие инструкции высланы вам на e-mail 
<?php 
echo $data['email'];
?>
уже сейчас вы можете посмотреть на сайт изнутри, но основные функции будут недоступные до тех пор, пока вы не подтвердите e-mail, это сделано для защиты от автоматических регистраций.
<?php
}
else
{   echo $data['error'];
?>

<form action='/registration/' method='POST'>
                Имя: <input type='text' name='name' value='<?php if(isset($data['val']['name'])) echo $data['val']['name'] ?>'/><br />
                Фамилия: <input type='text' name='sname' value='<?php echo $data['val']['sname'] ?>'/><br />
                E-mail: <input type='text' name='email' value='<?php echo $data['val']['email'] ?>'/><br />
                Пароль: <input type='password' name='pass' value='<?php echo $data['val']['pass'] ?>'/><br />
                <input type='submit'/>
            </form>

<?php } ?>