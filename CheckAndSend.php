<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Подключение к amoCRM</title>
</head>
<body>
    <div align='center'>Здесь вы можете проверить текущее подключение к amoCRM.<br></div><br>
<?php
require_once "./CreateRequest.php";
$request = new CreateRequest;
if ($request->getParam('status') == "true") {
    echo "<div align='center'> Подключение активно </div>";
} else {
    echo "<div align='center'> Подключение не активно. Перейти на страницу подключения </div>";
    ?>
    <form align='center' class="back" id="back" method="post" action="./CreateConnection.html">
    <input class="backButton" name="backButton" type="submit" value="Подключить">
</form>
<?php
}
?>
<br><br>
<div align='center'>Если amoCRM подключена, то вы можете отправить новую заявку. <br><br></div>
        <form method="post" action="./GetAccess.php">
            <table width="100%" cellspacing="0" cellpadding="4">
                <tr> 
                    <td align="right">Ваше имя</td>
                    <td><input class="name" id="name" type="text" name="name" placeholder="Алексей" maxlength="100" size="30"></td>
                   </tr>
             <tr> 
                <tr> 
                    <td align="right">Ваш телефон</td>
                    <td><input class="tel" id="tel" type="tel" name="phone" placeholder="+7XXXXXXXXXX" maxlength="30" size="30"></td>
                   </tr>
             <tr> 
              <td align="right" width="100">Ваш email</td>
              <td><input class="email" type="email" name="email" placeholder="example@mail.ru" maxlength="100" size="30"></td>
             </tr>

             <tr> 
            <td align="right">Цена товара</td>
              <td><input class="price" name="price" type="text" placeholder="Введите сумму" maxlength="30" size="30"></td>
              </tr>
            <tr> 
              <td></td>
              <td><input type="submit" name="sendButton" value="Отправить заявку"></td>
             </tr>
            </table>
           </form>
           <form class="back" id="back" method="post" action="./index.html">
            <input class="backButton" name="backButton" type="submit" value="Назад">
        </form>

    <!-- Roistat Counter Start -->
<script>
    (function(w, d, s, h, id) {
        w.roistatProjectId = id; w.roistatHost = h;
        var p = d.location.protocol == "https:" ? "https://" : "http://";
        var u = /^.*roistat_visit=[^;]+(.*)?$/.test(d.cookie) ? "/dist/module.js" : "/api/site/1.0/"+id+"/init?referrer="+encodeURIComponent(d.location.href);
        var js = d.createElement(s); js.charset="UTF-8"; js.async = 1; js.src = p+h+u; var js2 = d.getElementsByTagName(s)[0]; js2.parentNode.insertBefore(js, js2);
    })(window, document, 'script', 'cloud.roistat.com', '9b194a91372d03d18684b8e774433c0f');
    </script>
    <!-- Roistat Counter End -->
</body>
</html>