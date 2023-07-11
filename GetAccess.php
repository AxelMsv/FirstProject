<?php
class CreateRequest
{
    public $link = 'https://somova.amocrm.ru/';
function send_curl($url, $data, $headers) {
    $curl = curl_init(); //Сохраняем дескриптор сеанса cURL
    curl_setopt($curl,CURLOPT_RETURNTRANSFER, true); //возвращаем ответ в виде строки
    curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0'); //идентифицируем себя
    curl_setopt($curl,CURLOPT_URL, $url); // используем ранее сформированный URL
    curl_setopt($curl,CURLOPT_HTTPHEADER, $headers); // сообщаем о передаче json
    curl_setopt($curl,CURLOPT_HEADER, false); //выключаем в ответе заголовок
    curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST'); //устанавливаем метод POST
    curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data)); //отправляем авторизационные данные, закодированные в json
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1); //проверка сертификата включена 
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2); //проверка принадлежит ли сертификат этому домену
    $out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);// записываем посдений полученный код в переменную
    curl_close($curl); // закрываем сеанс 
    $code = (int)$code; // преобразуем код в целое число
$errors = [ //создаем список ошибок и их описания
    400 => 'Bad request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Not found',
    500 => 'Internal server error',
    502 => 'Bad gateway',
    503 => 'Service unavailable',
];

try
{
    /** Если код ответа не успешный - возвращаем сообщение об ошибке  */
    if ($code < 200 || $code > 204) {
        throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
    }
}
catch(Exception $e)
{
    die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
}
return json_decode($out, true);
}
function save_files($data){
    $access_token = $data['access_token']; //Access токен
    $refresh_token = $data['refresh_token']; //Refresh токен
    $expires_in = $data['expires_in'] + time(); //Указываем когда токен станет невалидным

    $file_token = __DIR__ . '/Token.txt';
    $file_tokentime = __DIR__."/TokenTime.txt";
    $file_refresh_token = __DIR__.'/TokenRefresh.txt';
    file_put_contents($file_token, $access_token);
    file_put_contents($file_tokentime, $expires_in);
    file_put_contents($file_refresh_token, $refresh_token);
}
}

//проверка авторизации
$is_access = file_get_contents(__DIR__ .'/Token.txt');
if ($is_access==null) {//если токена нет, то запрашиваю его
$access = new CreateRequest;
$url = $access->link.'oauth2/access_token'; //Формируем URL для запроса

$data = [
    'client_id' => 'e200f1c0-8d9e-4843-bf9c-07abe5effa52',
    'client_secret' => 'da48kT5FQsYNbJAqE2A77dOoavONHqEtaQUV0y4ica37n6oCEAWDJjrBOfQihYqW',
    'grant_type' => 'authorization_code',
    'code' => 'def502007936a509c1b0f466bb20e8b9e1bb30a8c680c4480c0c4491a8fdc2fae5c6476e9e3edd36ba97c9184894a73b34d392e328314087cb27f14391a7099218a17af8ebcf4a1def723afbb05de18ca1af398c9b64f37c9c2f0c5088e06267c902aa59b1132515547858da3955de8c8bf056cedbc7bc1a683a84136ed35ec44e702e467a8b51a2285935181d6724072ab8614362d53e7eea617b777e165b0a867510b45ae574239102768bf67b8620485b4a763330eb39d0d27daec16f29bb62a874ead9296e764bcdc20ccad9e649b7e3536931ce121ee246b6fde42836d48ebb2696ba931503ae2ffe5857d8eb5e5bac92c3511c80522ae2d017edb25ed190599d75530d39eea405a6af0ac8695cddc9a45ff20f2503b7b0d3cf7e6bb6e12213cc60b93e4c785b6b89a146fad56acffd197b9adec2ee3e8480ff36a72e6e089f1271c5f4ca5a887fa518556ad306a37fbc8169da8432d2c6a53df96cf53d57a7f3ace02aa9a7624a96d1582cbbcaa6adbc9fc550d0f44dd60b75b2e278be0182542b9708e2d3aa3b11c8fecc76984bb1188fa3273e9ba0dbee1019c5b118f2689ad0b4398cfce4fd932ca8f583fba1b20fa89f815e5f5910d47dbecca22eb49bba02e2c7bce33efb1c4260e0fb988d08da6dc11a4cb8b0ef47dd979dfe9ed7a458',
    'redirect_uri' => 'http://expressmen.temp.swtest.ru/',
];

$headers = [
    "Content-Type: application/json"
];
$responseAccess = $access->send_curl($url,$data,$headers);
$access->save_files($responseAccess);
}

//если токен уже просрочен, то запрашиваем новый
$is_token_expired = file_get_contents(__DIR__ .'/TokenTime.txt');
$current_time = time();

if  ($is_token_expired < $current_time) {
$refresh_token = file_get_contents(__DIR__ .'/TokenRefresh.txt');

$refresh = new CreateRequest;
$url = $refresh->link.'oauth2/access_token';

$data = [
    'client_id' => 'e200f1c0-8d9e-4843-bf9c-07abe5effa52',
    'client_secret' => 'da48kT5FQsYNbJAqE2A77dOoavONHqEtaQUV0y4ica37n6oCEAWDJjrBOfQihYqW',
    'grant_type' => 'refresh_token',
    'refresh_token' => $refresh_token,
    'redirect_uri' => 'http://expressmen.temp.swtest.ru/',
];

$headers = [
    "Content-Type: application/json"
];

$responseRefresh = $refresh->send_curl($url,$data,$headers);
$refresh->save_files($responseRefresh);
};
//собираем данные с формы 
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $price = $_POST['price'];
}

//отправление контакта
$contact = new CreateRequest;
$url = $contact->link."api/v4/contacts";

$access_token = file_get_contents(__DIR__ .'/Token.txt');
$contact_array = array(
    [
        "name"=> $name,
        "first_name" => $name,
        "custom_fields_values" => [
            [
            'field_id'=> 904621,
            "values" => [
                [
                'value' => $phone
            ]
            ]
            ],
            [
                'field_id'=> 904623,
                "values" => [[
                    'value' => $email
                ]]
            ]
    ]]
);

$headers = [
    "Accept: application/json",
    'Authorization: Bearer ' . $access_token
];

$Response = $contact->send_curl($url,$contact_array,$headers); // {"_links":{"self":{"href":"https://somova.amocrm.ru/api/v4/contacts"}},"_embedded":{"contacts":[{"id":3575785,"is_deleted":false,"is_unsorted":false,"request_id":"0","_links": {"self":{"href":"https://somova.amocrm.ru/api/v4/contacts/3575785"}}}]}}
$contact_id = $Response["_embedded"]["contacts"][0]['id'];
echo "Создан контакт: {$contact_id}</br>";

//отправляем заявку
$lead = new CreateRequest;
$url = $lead->link.'api/v4/leads';

$access_token = file_get_contents(__DIR__ .'/Token.txt');

$lead_array = array(
    [
        "price"=> (int)$price,
        "_embedded" => [ 
            "contacts"=>[
            [
                "id"=>$contact_id
            ]
        ]
    ]
    ]
);

$headers = [
    "Accept: application/json",
    'Authorization: Bearer ' . $access_token
];

$Response = $lead->send_curl($url,$lead_array,$headers);
$lead_id = $Response["_embedded"]["leads"][0]['id'];
echo "Создана заявка: {$lead_id}";
?>