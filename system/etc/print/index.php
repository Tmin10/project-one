<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>

        <fieldset style="width:300px;  margin: 100px auto;">

            <legend>Печать диплома</legend>
            <form action="generator.php" method="post" target="_blank">

                Бланк:<br/>
                <select name="blank" onchange="blch();">
                    <option value="titulA4">Диплом А4</option>
                    <option value="enclosureA3front">Приложение к диплому А3 (страницы 4 и 1)</option>
                    <option value="enclosureA3back">Приложение к диплому А3 (страницы 2 и 3)</option>
                    <option value="enclosureA4">Отдельная страница приложения А4</option>
                </select>

                <div id="pageSelector" style="display:none;">
                    Номер страницы:
                    <select name="page">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                    </select>
                </div>
                
                <input type="checkbox" name="duplicate" id="duplicate" style="margin-top:12px;"/>
                <label for="duplicate">Дубликат</label>

                <br/>
                <input type="checkbox" name="example" id="example" />
                <label for="example">С надписью &QUOT;ОБРАЗЕЦ&QUOT;</label>
                
                <input type="hidden" name="diplomNumber" value="1" />

                <br/><br/>
                <input type="submit" value="Сформировать" />

            </form>

        </fieldset>
    </body>
    <script>
        function blch() {
            list = document.getElementsByName("blank")[0].options;
            pageSelector = document.getElementById("pageSelector");
            if (list[3].selected)
                pageSelector.style.display = "block";
            else
                pageSelector.style.display = "none";
        }
    </script>

</html>
