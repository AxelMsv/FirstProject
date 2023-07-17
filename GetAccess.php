<?php
require_once "CreateRequest.php";

// если нужно создать новое подключение, то перевожу на соответствующую страницу
if (isset($_REQUEST['newButton'])) header("Location: ./CreateConnection.html");

// выполняю действия ниже, если нужно создать новое подключение
if (isset($_REQUEST['createRequestButton'])) {

    $request = new CreateRequest; // сохраняю данные о подключении в файл
    $request->setParam('domain', $_POST['domain']);
    $request->setParam('client_id', $_POST['client_id']);
    $request->setParam('client_secret', $_POST['client_secret']);

    // создаю запрос на подключение и получение токенов
    $url = $request->createUrl('access');
    $data = [
        'client_id' => $request->getParam('client_id'),
        'client_secret' => $request->getParam('client_secret'),
        'grant_type' => 'authorization_code',
        'code' => $_POST['code'],
        'redirect_uri' => 'http://expressmen.temp.swtest.ru/', // переписать домен +++++++++++
    ];
    $headers = [
        "Content-Type: application/json"
    ];
    $result = $request->send_curl($url, $data, $headers);

    // если подключение успешно, то данные записываются в файл для будущих подключений
    if ($result) {
        $request->setParam('access_token', $result['access_token']);
        $request->setParam('refresh_token', $result['refresh_token']);
        $request->setParam('expires_in', $result['expires_in'] + time());
        $request->setParam('status', "true");
    }
    header("Location: ./CheckAndSend.php"); // перехожу на страницу отправки заявок
}

//переход на страницу проверки подключения и отправки заявок
if (isset($_REQUEST['checkButton'])) header("Location: ./CheckAndSend.php");

// обработка страницы с отправкой заявки
if (isset($_REQUEST['sendButton'])) {

    $request = new CreateRequest;
    $url = $request->createUrl('contacts');
    // обновляю токен, если просрочен
    if (time() >= $request->getParam('expires_in')) {
        $url = $request->createUrl('access');
        $data = [
            'client_id' => $request->getParam('client_id'),
            'client_secret' => $request->getParam('client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->getParam('refresh_token'),
            'redirect_uri' => 'http://expressmen.temp.swtest.ru/',
        ];
        
        $headers = [
            "Content-Type: application/json"
        ];
        
        $response = $request->send_curl($url, $data, $headers);
        
        $request->setParam('access_token', $response['access_token']);
        $request->setParam('refresh_token', $response['refresh_token']); //Refresh токен
        $request->setParam('expires_in', $response['expires_in'] + time());
    }

//собираем данные с формы 
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $price = $_POST['price'];
}

//отправление контакта
$url = $request->createUrl('contacts');

$contact_array = array(
    [
        "name"=> $name,
        "first_name" => $name,
        "custom_fields_values" => [
            [
            'field_id'=> 1402705,
            "values" => [
                [
                'value' => $phone
            ]
            ]
            ],
            [
                'field_id'=> 1402707,
                "values" => [[
                    'value' => $email
                ]]
            ]
    ]]
);

$headers = [
    "Accept: application/json",
    'Authorization: Bearer ' . $request->getParam('access_token')
];

$Response = $request->send_curl($url,$contact_array,$headers);
$contact_id = $Response["_embedded"]["contacts"][0]['id'];
echo "Создан контакт: {$contact_id}</br>";

//отправляем заявку
$url = $request->createUrl('leads');
$roistat = isset($_COOKIE['roistat_visit']) ? $_COOKIE['roistat_visit'] : 'nocookie';
$lead_array = array(
    [
        "price" => (int)$price,
        "custom_fields_values" => [
            [
                "field_id" => 1402725,
                "values" => [
                    [
                        "value" => $roistat
                    ]
                ]
            ]
        ],
        "_embedded" => [ 
            "contacts"=>[
            [
                "id" => $contact_id
            ]
            ]
    ]
    ]
);

$headers = [
    "Accept: application/json",
    'Authorization: Bearer ' . $request->getParam('access_token')
];

$Response = $request->send_curl($url,$lead_array,$headers);
$lead_id = $Response["_embedded"]["leads"][0]['id'];
echo "Создана заявка: {$lead_id}<br>";
echo "Номер визита: {$roistat}";
}
?>

<form class="back" id="back" method="post" action="./CheckAndSend.php">
<input class="backButton" name="backButton" type="submit" value="Назад">
</form>