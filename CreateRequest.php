<?php
class CreateRequest
{

    public $file = "credentials.txt";

    public function setFileName($name)
    {
        $this->file = $name;
    }

    public function getParam($name)
    {
        $f = fopen($this->file, "c+") or die("Не могу открыть файл для чтения!");
        flock($f, LOCK_EX);
        $str = explode("\n", fread($f, 10000));
        foreach ($str as $k => $v) {
            if (strpos($v, $name) === 0) {
                preg_match('/\s\w\S+/', $v, $match);
                return trim($match[0]);
            }
        }
        fclose($f);
    }

    public function setParam($name, $param)
    {
        $f = fopen($this->file, "c+") or die("Не могу открыть файл для записи!");
        flock($f, LOCK_EX);
        $str = explode("\n", fread($f, 10000));
        $add = true;
        foreach ($str as $k => $v) {
            if (strpos($v, $name) === 0) {
                $str[$k] = "$name = $param";
                $add = false;
            }
        }
        if ($add != false) {
            $str[] = "$name = $param";
        }
        ftruncate($f, 0);
        fseek($f, 0, SEEK_SET);
        fwrite($f, implode("\n", $str));
        fclose($f);
    }

    public function getAccess($code)
    {
        $url = self::getParam('domain') . 'oauth2/access_token';
        $data = [
            'client_id' => self::getParam('client_id'),
            'client_secret' => self::getParam('client_secret'),
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => 'http://expressmen.temp.swtest.ru/', // переписать домен +++++++++++
        ];
        $headers = [
            "Content-Type: application/json"
        ];
        $result = self::send_curl($url, $data, $headers);
        if ($result) {
            self::setParam('access_token', $result['access_token']);
            self::setParam('refresh_token', $result['refresh_token']);
            self::setParam('expires_in', $result['expires_in'] + time());
            self::setParam('status', "true");
        }
    }

    public function getRefresh()
    {
        $url = self::getParam('domain') . 'oauth2/access_token';
        $data = [
            'client_id' => self::getParam('client_id'),
            'client_secret' => self::getParam('client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => self::getParam('refresh_token'),
            'redirect_uri' => 'http://expressmen.temp.swtest.ru/',
        ];
        $headers = [
            "Content-Type: application/json"
        ];
        $response = self::send_curl($url, $data, $headers);
        self::setParam('access_token', $response['access_token']);
        self::setParam('refresh_token', $response['refresh_token']);
        self::setParam('expires_in', $response['expires_in'] + time());
    }

    public function createUrl($requestType)
    {
        if (strtolower($requestType) == "contacts") {
            return self::getParam('domain') . 'api/v4/contacts';
        }
        if (strtolower($requestType) == "leads") {
            return self::getParam('domain') . 'api/v4/leads';
        }
    }

    public function send_curl($url, $data, $headers)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
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

        try {
            /** Если код ответа не успешный - возвращаем сообщение об ошибке  */
            if ($code < 200 || $code > 204) {
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
            }
        } catch (Exception $e) {

            self::setParam('status', 'false');
            die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
        }
        return json_decode($out, true);
    }
}
