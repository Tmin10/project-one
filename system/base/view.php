<?php
class View
{
    function render($content_view, $template_view, $data = null)
    {
        /*
        if(is_array($data)) {
            // преобразуем элементы массива в переменные
            extract($data);
        }
        */
        
        include 'system/views/'.$template_view;
    }
}
?>