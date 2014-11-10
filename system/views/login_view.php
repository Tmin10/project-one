Неверные данные входа, авторизация невозможна, пожалуйста проверьте правильность ввода информации и попробуйте снова.
            <form action='/login/' method='POST'>
                E-mail или логин: <input type='text' name='email' value='<?php if (isset($data['val']['email'])) echo $data['val']['email'] ?>'/><br />
                Пароль: <input type='password' name='pass'/><br />
                <input type='submit'/>
            </form>