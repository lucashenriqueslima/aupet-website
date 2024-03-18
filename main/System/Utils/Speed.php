<?php

namespace System\Utils;

/*
 * Rafael Ramos <si.rafael.analista@gmail.com>
 */

class Speed {

    protected static function UploadFile($_file, $_fileTemp, $_path) {
        $out = move_uploaded_file($_fileTemp, $_path . $_file);
        return $out;
    }

    public static function Upload($_client, $_file, $_fileTemp, $_size, $_path, $_locale) {

        $path = null;
        if (empty($_client))
            throw new \Exception('o nome do cliente deve ser informado.');
        if (empty($_file))
            throw new \Exception('arquivo vazio.');

        $dir = strripos($_path, '/');
        $path = self::CheckDir($_client, substr($_path, $dir + 1), $_locale);

        if (file_exists($path . $_file))
            @unlink($path . $_file);
        if (!empty($_size))
            $out = self::ResizeImage($_file, $_fileTemp, $_size, $path);
        else
            $out = self::UploadFile($_file, $_fileTemp, $path);
        return $out;
    }

    private static function CheckDir($_client, $_dir, $locale = 'client') {
        if (empty($_dir))
            return false;
        $path = null;
        if ($locale == 'client')
            $path = _PATH_RESOURCES . 'clients/' . $_client . '/' . $_dir;
        if ($locale == 'user')
            $path = _PATH_RESOURCES . 'clients/' . $_client . '/user/' . $_dir;
        if ($locale == 'product')
            $path = _PATH_RESOURCES . 'clients/' . $_client . '/product';
        if ($locale == 'document')
            $path = _PATH_RESOURCES . 'clients/' . $_client . '/document';
        if ($locale == 'partner')
            $path = _PATH_RESOURCES . 'clients/' . $_client . '/partner';
        if ($locale == 'receipt')
            $path = _PATH_RESOURCES . 'clients/' . $_client . '/receipt';
        if ($locale == 'store')
            $path = _PATH_RESOURCES . 'clients/' . $_client . '/store';
        if ($locale == 'package')
            $path = _PATH_RESOURCES . 'clients/' . $_client . '/package';

        if (!is_dir($path)) {
            mkdir($path);
        }
        return $path . '/';
    }

    public static function GetPost($_name) {
        return self::GetParam($_name, 3);
    }

    public static function GetParameter($_name) {
        return self::GetParam($_name, 1);
    }

    public static function GetFile($_name) {
        return self::GetParam($_name, 4);
    }

    private static function GetParam($_name, $_type) {
        try {
            $request = unserialize(_REQUEST);
            $type = (int) $_type;
            $out = Array();
            if (!is_int($type) || empty($type))
                return -1;
            if (empty($request))
                return -2;
            if (strlen($_name) < 0)
                return -3;
            switch ($type) {
                case 1://parameters
                    $out = isset($request['parameters'][$_name]) ? $request['parameters'][$_name] : false;
                    break;
                case 2://GET
                    $out = $request['GET'];
                    break;
                case 3://POST
                    $out = isset($request['POST'][$_name]) ? $request['POST'][$_name] : false;
                    break;
                case 4://FILE
                    $out = isset($request['FILE'][$_name]) ? $request['FILE'][$_name] : false;
                    break;
                    break;
            }

            return $out;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public static function PrintPage($_data, $_type) {
        switch ($_type) {
            case 'json':header('Content-Type: application/json');
                break;
        }
        echo $_data;
    }

    /**
     * Metodo que imprime qualquer variável de uma forma mais legível.
     * 
     * @param type $_data
     * @param String $_type
     * @return String
     */
    public static function PrintVar($_data, $_type = false) {
        try {
            if ($_type === false) {
                echo "<pre>";
                print_r($_data);
                echo "</pre>";
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public static function GetUrl($_type = 'base') {
        $protocol = ($_SERVER["SERVER_PORT"] == '443') ? ('https') : ('http');
        $port = ($_SERVER["SERVER_PORT"] == "81" || $_SERVER["SERVER_PORT"] == "80" || $_SERVER["SERVER_PORT"] == "443") ? "" : (":" . $_SERVER["SERVER_PORT"]);
        $client = explode('/', $_SERVER['REQUEST_URI']);
        array_shift($client);
        switch ($_type) {
            case 'full': $ret = $protocol . "://" . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI'] . '/';
                break;
            case 'client': $ret = $protocol . "://" . $_SERVER['HTTP_HOST'] . $port . '/' . $client[0] . '/';
                break;
            case 'base': $ret = $protocol . "://" . $_SERVER['HTTP_HOST'] . $port . '/';
                break;
            case 'param': $ret = implode('/', $client);
                break;
            case 'www': $ret = $_SERVER['HTTP_HOST'] . $port;
                break;
        }
        return $ret;
    }

    private static function strleft($s1, $s2) {
        return substr($s1, 0, strpos($s1, $s2));
    }

    public static function RedirectUrl($_url, $_time = 0, $_type = false) {
        if ($_type) {
            $_url = self::GetUrl($_type) . $_url;
        } else {
            $_url = self::GetUrl('client') . $_url;
        }
        if ($_time > 0) {
            header("refresh:5; url={$_url}");
        } else {
            header('Location: ' . $_url);
        }
    }

    public static function ByteToMegaByte($size, $precision = 2) {
        $base = $size / 1024 / 1024;
        $megabyte = round($base, $precision);
        if ($megabyte > 0)
            return $megabyte;
        else
            return 0;
    }

    public static function FormatBytes($size, $precision = 2) {
        $base = log($size) / log(1024);
        $suffixes = array('', 'k', 'M', 'G', 'T');
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    public static function DecimalToMoney($_number, $_locale = "R$") {
        try {
            return ($_locale . ' ' . number_format($_number, 2, ',', '.'));
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public static function MoneyToDecimal($_money, $_symbol) {
        try {
            $formated = 0;
            if (!empty($_money) && !empty($_symbol)) {
                $formated = str_replace($_symbol, "", $_money);
                $formated = str_replace(" ", "", $formated);
                $formated = str_replace(".", "", $formated);
                $formated = str_replace(",", ".", $formated);
            }
            return $formated;
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public static function ParseUrl($_url) {
        try {
            $arrUrl = explode('&', $_url);
            $url = Array();
            $merge = Array();
            $merge_uniq = Array();
            foreach ($arrUrl as $val) {
                $v = explode('=', $val);
                if (array_key_exists($v[0], $url)) {
                    if (!array_key_exists($v[0], $merge))
                        $merge[$v[0]][] = $url[$v[0]];
                    $merge[$v[0]][] = urldecode($v[1]);
                } else
                    $url[$v[0]] = urldecode($v[1]);
            }
            $result = array_merge($url, $merge);
            return $result;
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public static function FormatString($_string, $_type = "") {
        try {
            $_string = preg_replace("[^0-9]", "", $_string);
            if (!$_type) {
                switch (strlen($_string)) {
                    case 10: $_type = 'fone';
                        break;
                    case 8: $_type = 'cep';
                        break;
                    case 11: $_type = 'cpf';
                        break;
                    case 14: $_type = 'cnpj';
                        break;
                }
            }
            switch ($_type) {
                case 'fone':
                    $_string = '(' . substr($_string, 0, 2) . ') ' . substr($_string, 2, 4) .
                            '-' . substr($_string, 6);
                    break;
                case 'cep':
                    $_string = substr($_string, 0, 5) . '-' . substr($_string, 5, 3);
                    break;
                case 'cpf':
                    $_string = substr($_string, 0, 3) . '.' . substr($_string, 3, 3) .
                            '.' . substr($_string, 6, 3) . '-' . substr($_string, 9, 2);
                    break;
                case 'cnpj':
                    $_string = substr($_string, 0, 2) . '.' . substr($_string, 2, 3) .
                            '.' . substr($_string, 5, 3) . '/' .
                            substr($_string, 8, 4) . '-' . substr($_string, 12, 2);
                    break;
                case 'rg':
                    $_string = substr($_string, 0, 2) . '.' . substr($_string, 2, 3) .
                            '.' . substr($_string, 5, 3);
                    break;
            }
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            return false;
        }
        return $_string;
    }

    public static function FormatDate($dt, $_db = false) {
        $d = Array();
        $out = '--';
        try {
            $data = substr($dt, 0, 10);
            if ($_db === true) {
                $d = explode('/', $data);
                if (count($d) >= 2)
                    $out = $d[2] . '-' . $d[1] . '-' . $d[0];
            }else {
                $d = explode('-', $data);
                if (count($d) >= 2)
                    $out = $d[2] . '/' . $d[1] . '/' . $d[0];
            }

            return $out;
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            return false;
        }
    }

    public static function FormatCurrency($_data) {
        try {
            $clean = '';
            $currency = '';
            $int = 0;
            $dec = 0;
            if (!empty($_data)) {
                $clean = str_replace('.', '', $_data);
                $currency = str_replace(',', '', $clean);
                $int = substr($currency, 0, -2);
                $dec = substr($currency, -2);
                $currency = $int . '.' . $dec;
            }
            return (double) $currency;
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            return false;
        }
    }

    public static function FormatDecimalToPercent($_number, $_decimals = 0, $_point = null) {
        $result = false;
        try {
            if (!empty($_number)) {
                if ($_number > 999)
                    $_number = 999;
                if ($_point !== null)
                    $result = number_format($_number, $_decimals, $_point);
                else
                    $result = number_format($_number, $_decimals);
            }
            if ($result === false)
                $result = 0;
            unset($_number, $_point, $_decimals);
            return $result;
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            return false;
        }
    }

    public static function FormatDecimal($_number, $_decimal = 2) {
        try {
            $clean = '';
            $currency = '';
            $num = '';
            $int = 0;
            $dec = 0;
            if (!empty($_number)) {
                //$decimal = substr($_number, strpos($_number, '.'));
                if (!strpos($_number, '.') && !strpos($_number, ',')) {
                    $currency = "{$_number}.00";
                } else {
                    $clean = str_replace('.', '', $_number);
                    $currency = str_replace(',', '', $clean);
                    $int = substr($currency, 0, -$_decimal);
                    $dec = substr($currency, -$_decimal);
                    $currency = $int . '.' . $dec;
                }
            }
            return (double) $currency;
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            return false;
        }
    }

    public static function MakePassword($value, array $options = []) {
        $cost = isset($options['rounds']) ? $options['rounds'] : 10;

        $hash = password_hash($value, PASSWORD_BCRYPT, ['cost' => $cost]);

        if ($hash === false) {
            throw new RuntimeException('Bcrypt hashing not supported.');
        }

        return $hash;
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @param  array   $options
     * @return bool
     */
    public static function CheckPassword($value, $hashedValue, array $options = []) {
        if (strlen($hashedValue) === 0) {
            return false;
        }

        return password_verify($value, $hashedValue);
    }

    /**
     * Método que encripta uma string usando como padrão base64 concatenado com uma Chave interna do sistema.
     * Este método é para ocultar mensagens transportadas por GET ou POST.
     * 
     * <code>
     *     $hash = General::hashStirng('minha_string'); //Encode a string
     *     $out = General::hashStirng($hash, false); //Decode a string
     *     echo $out; //Imprime minha_string
     * </code>
     * 
     * 
     * @param string $_string Recebe como Argumento uma string
     * @param boolean $_encode Define se vai fazer Encoder ou Decoder de uma string
     * @return string Retorna uma string que é o Encoder de uma string ou o Decoder de um Hash
     */
    public static function hashString($_string, $_encode = true) {
        $encode = '';
        try {
            if ($_encode === true)
                $encode = base64_encode((_HASH . $_string));
            elseif ($_encode === false)
                $encode = substr(base64_decode($_string), 40);

            return $encode;
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            return false;
        }
    }

    public static function log($_file, $_line, $_method, $_trace) {
        $data = Array();
        try {
            if (empty($_file) || empty($_line) || empty($_method))
                throw new \Exception('Parâmetros inválidos', '#000001');
            $data['file'] = $_file;
            $data['line'] = $_line;
            $data['method'] = $_method;
            $data['trace'] = $_trace;
            $txt = '[FILE:'
                    . $_file . ']'
                    . ' [LINE:' . $_line . ']'
                    . ' [METHOD:' . $_method . ']';
            $data['show_erro'] = $txt;

            return $data;
        } catch (\Excelption $ex) {
            echo 'CODE: ' . $ex->getCode() . ' ' . $ex->getMessage();
            return false;
        }
    }

    public static function FormatPhone($numero) {
        $pattern = '/(\d{2})(\d{4})(\d*)/';
        $novo = preg_replace($pattern, '($1) $2-$3', $numero);
        return $novo;
    }

    public static function NumberFormat($_number, $_type, $_hide = 0) {
        $number = 0.00;
        try {
            if (!empty($_number) && !is_numeric($_number))
                throw new \Exception("Número inválido", 0000001);
            switch ($_type) {
                case '%':
                    $number = $_type . ' ' . number_format($_number, 2, '.', '');
                    break;
                case 'R$':
                    $number = $_type . ' ' . number_format($_number, 2, ',', '.');
                    break;
                default:
                    $number = number_format($_number, 2, ',', '.');
                    break;
            }
            if ($_hide != 0) {

                $minus = strlen($number) - $_hide;
                $number = substr($number, 0, $minus);
                if (substr($number, -1) == ',' || substr($number, -1) == '.') {
                    $number = substr($number, 0, -1);
                }
            }
            return $number;
        } catch (\Exception $ex) {
            $out = General::log($ex->getFile(), $ex->getLine(), __METHOD__, $ex->getTrace());
            echo $out['show_erro'];
            return false;
        }
    }

    public static function Unicode($_str, $_format = "UTF-8") {
        try {
            $text = null;
            if (!empty($_str)) {
                $check = mb_detect_encoding($_str, $_format, true);
                if ($check == $_format)
                    $text = $_str;
                else
                    $text = utf8_encode($_str);
                return $text;
            } else
                return $text;
        } catch (\Exception $ex) {
            $ex->getMessage();
            return false;
        }
    }

    /**
     * Método que verifica se o parâmetro é um objeto do tipo Exception.
     * 
     * @param type $_obj
     * @return boolean
     */
    public static function IsException($_obj) {
        try {
            if (empty($_obj) || !is_object($_obj))
                return false;
            else {
                if (strpos(get_class($_obj), "Exception") || get_class($_obj) == "Exception")
                    return true;
                else
                    return false;
            }
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * Método recursivo que varre um Array em busca de caractéres com erros de 
     * acentuação e converte de forma transparente para o formato correto.
     * 
     * Pode passar como parâmetro uma string ou um Array.
     * 
     * @param Array|String $_string
     * @param String $_format
     * @return boolean|Array
     */
    public static function RecCleanString($_data, $_format = "UTF-8") {
        try {
            if (empty($_data))
                throw new \Exception("dados não posse ser vazio!");
            if (is_array($_data)) {
                $out = Array();
                foreach ($_data as $key => $value) {
                    if (is_array($value)) {
                        $out[$key] = self::RecCleanString($value, $_format);
                    } else
                        $out[$key] = self::Unicode($value, $_format);
                }
                return $out;
            } else
                return self::Unicode($_data, $_format);
        } catch (\Exception $ex) {
            return Array("status" => 0, "msg" => $ex->getMessage());
        }
    }

    public static function EmailIsValid($_email) {
        $status = false;
        try {
            $email = trim($_email);

            if (empty($email))
                throw new \Exception("não foi passado um e-mail.");

            $validator = new EmailAddress(
                    array(
                'allow' => Hostname::ALLOW_DNS,
                'useMxCheck' => true,
                'useDeepMxCheck' => true
            ));
            if ($validator->isValid($email)) {
                $status = true;
            }

            return $status;
        } catch (Exception $ex) {

            return $status;
        }
    }

    public static function GenerateHash($_length = 8) {
        try {
            return substr(md5(uniqid(rand(), true)), 0, $_length);
        } catch (\Exception $ex) {
            
        }
    }

    public static function GenerateSerial($_string) {
        try {
            $a = hash('crc32', _HASH);
            $b = hash('crc32', sprintf('%s%s', md5($_string), md5($a)));
            $c = sscanf(sprintf('%s%s', $a, $b), '%4s%4s%4s%4s');
            $d = 1;

            for ($i = 0; $i < 4; $i++)
                for ($j = 0; $j < 4; $d += pow(ord($c[$i]{ $j }), $i), $j++)
                    ;

            $c[4] = $d;

            return vsprintf('%s-%s-%s-%s-%05x', $c);
        } catch (\Exception $ex) {
            
        }
    }

    public static function CheckSerial($_key) {
        try {
            $c = sscanf($_key, '%4s-%4s-%4s-%4s');
            $d = 1;

            for ($i = 0; $i < 4; $i++)
                for ($j = 0; $j < 4; $d += pow(ord($c[$i]{ $j }), $i), $j++)
                    ;

            $c[4] = $d;

            return !strcmp($_key, vsprintf('%s-%s-%s-%s-%05x', $c));
        } catch (\Exception $ex) {
            
        }
    }

    public static function Authority($_profile, $_level) {
        $state = false;
        try {
            $level = Array();
            if (empty($_profile))
                throw new \Exception('Não foi definido o perfil.');
            $profileCode = $_profile;

            if ($profileCode !== 0) {
                $adapter = new Profile();
                $levels = $adapter->GetProfileByCode($profileCode);
                if (is_array($levels)) {
                    foreach ($levels as $key => $val) {
                        if ($val['level_code'] == $_level)
                            $state == true;
                    }
                }
            } else
                $state = true;
        } catch (\Exception $ex) {
            $state = false;
        }
        return $state;
    }

    public static function SetCookie($_name, $_value, $_time = 1800) {
        try {
            $secure = true;
            $domain = self::GetUrl('www');
            $time = time() + $_time;
            $value = null;
            $position = strripos(self::GetUrl('param'), '/');
            $path = substr(self::GetUrl('param'), 0, ($position - 1));
            $stats = false;
            if (empty($_name))
                throw new \Exception("Nome do Cookie não definido.");
            if (empty($_value))
                throw new \Exception("Valor do Cookie não definido");
            if (is_array($_value))
                $value = serialize($_value);
            else
                $value = $_value;

            $stats = setcookie($_name, $value, $time, $path, $domain, $secure);
            return $stats;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public static function GetCookie($_name) {
        try {
            $stats = false;
            $cookie = $_COOKIE;
            if (isset($cookie[$_name]))
                $stats = $cookie[$_name];
            return $stats;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public static function CleanCookie($_name) {
        try {
            $secure = true;
            $domain = self::GetUrl('www');
            $time = time() - 3600;
            $value = null;
            $position = strripos(self::GetUrl('param'), '/');
            $path = substr(self::GetUrl('param'), 0, ($position - 1));
            $stats = false;
            if (empty($_name))
                throw new \Exception("Nome do Cookie não definido.");

            $stats = setcookie($_name, $value, $time, $path, $domain, $secure);
            return $stats;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Método para formatar um código postal ou CEP quando não existir o traço 
     * separador Ex: 0000-000
     * 
     * @param String $_code
     * @return string|Boolean
     * @throws \Exception
     */
    public static function FormatCodePost($_code) {
        try {
            $code = false;
            //Verifica se foi passado algum valor
            if (empty($_code))
                throw new \Exception('Não existe código postal válido.');
            //Verifica se existe o traço no código postal, caso exista é retirado
            //para que possa ser validado.
            if (strrpos($_code, '-') !== false) {
                $_code = str_replace('-', '', $_code);
            }
            //Verifica se existe a quantidade correta de números
            if (strlen($_code) != 8) {
                throw new \Exception("Código Postal inválido, verifique se contém 8 números.");
            }
            $code = substr($_code, 0, -3) . '-' . substr($_code, -3);

            return $code;
        } catch (\Exception $ex) {
            return false;
        }
    }

    static function ResizeImage($_imgNewName, $_image, $_size, $_path) {
        $status = false;
        try {
            $isTrueColor = false;
            $imagemjpg = $_image;
            $newWidth = $_size;
            $ext = explode('.', $_imgNewName);
            if ($ext[1] == 'jpg' || $ext[1] == 'jpeg')
                $img = imagecreatefromjpeg($imagemjpg);
            else {
                $img = imagecreatefrompng($imagemjpg);
                $isTrueColor = imageistruecolor($img);
            }

            $largura_original = imagesX($img);
            $altura_original = imagesY($img);

            $newHeight = (int) ($altura_original * $newWidth) / $largura_original;
            $nova = imagecreatetruecolor($newWidth, $newHeight);
            if ($isTrueColor) {
                imagealphablending($nova, false);
                imagesavealpha($nova, true);
            }

            imagecopyresampled($nova, $img, 0, 0, 0, 0, $newWidth, $newHeight, $largura_original, $altura_original);
            if ($ext[1] == 'jpg' || $ext[1] == 'jpeg')
                $status = imagejpeg($nova, $_path . $_imgNewName);
            else {
                $status = imagepng($nova, $_path . $_imgNewName);
            }
            imagedestroy($nova);
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            $status = false;
        }
        return $status;
    }

    public static function CheckUsername($_username) {
        $state = false;
        try {
            if (!empty($_username)) {
                $regex = '[\sáàâãäªéèêëíìîïóòôõöºúùûüçñ]+';
                $state = (bool) preg_match('/' . $regex . '/i', $_username);
            } else {
                $state = false;
            }
        } catch (\Exception $ex) {
            $state = false;
        }
        return $state;
    }

    public static function CreatePass($_pass) {
        $status = false;
        try {
            $rand = '';
            for ($i = 0; $i < 16; $i++) {
                $rand .= chr(mt_rand(0, 255));
            }
            $salt = $rand;
            $salt64 = substr(str_replace('+', '.', base64_encode($salt)), 0, 22);
            /**
             * Check for security flaw in the bcrypt implementation used by crypt()
             * @see http://php.net/security/crypt_blowfish.php
             */
            if (version_compare(PHP_VERSION, '5.3.7') >= 0) {
                $prefix = '$2y$';
            } else {
                $prefix = '$2a$';
                // check if the password contains 8-bit character
                if (preg_match('/[\x80-\xFF]/', $_pass)) {
                    throw new Exception\RuntimeException(
                    'The bcrypt implementation used by PHP can contains a security flaw ' .
                    'using password with 8-bit character. ' .
                    'We suggest to upgrade to PHP 5.3.7+ or use passwords with only 7-bit characters'
                    );
                }
            }
            $hash = crypt($_pass, $prefix . 14 . '$' . $salt64);
            if (strlen($hash) <= 13) {
                throw new \Exception('Error during the bcrypt generation');
            }
            $status = $hash;
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            $status = false;
        }
        return $status;
    }

    public static function validarCPF($cpf) {
        // Verifica se um número foi informado
        if (empty($cpf)) {
            return false;
        }

        // Elimina possivel mascara
        $cpf = preg_replace('/[^0-9]/', '', (string) $cpf);
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

        // Verifica se o numero de digitos informados é igual a 11 
        if (strlen($cpf) != 11) {
            return false;
        }
        // Verifica se nenhuma das sequências invalidas abaixo 
        // foi digitada. Caso afirmativo, retorna falso
        else if ($cpf == '00000000000' ||
                $cpf == '11111111111' ||
                $cpf == '22222222222' ||
                $cpf == '33333333333' ||
                $cpf == '44444444444' ||
                $cpf == '55555555555' ||
                $cpf == '66666666666' ||
                $cpf == '77777777777' ||
                $cpf == '88888888888' ||
                $cpf == '99999999999') {
            return false;
            // Calcula os digitos verificadores para verificar se o
            // CPF é válido
        } else {

            for ($t = 9; $t < 11; $t++) {

                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf{$c} != $d) {
                    return false;
                }
            }

            return true;
        }
    }

    public static function validarCnpj($cnpj) {
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);
        // Valida tamanho
        if (strlen($cnpj) != 14) {
            return false;
        }
        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj{$i} * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj{12} != ($resto < 2 ? 0 : 11 - $resto)) {
            return false;
        }
        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj{$i} * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj{13} == ($resto < 2 ? 0 : 11 - $resto);
    }

    public static function removerAcentos($acentos) {
        return strtr($acentos, 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    }

    public static function moeda($valor, $moeda = 'R$') {
        if ($moeda == 'R$') {
            $valorFormatado = 'R$ ' . number_format($valor, 2, ',', '.');
        } else if ($moeda == 'U$') {
            $valorFormatado = 'U$ ' . number_format($valor, 2, '.', ',');
        }
        return $valorFormatado;
    }

    public static function validarEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Email inválido.");
        }
    }

    public static function onlyNumber($_value) {
        return preg_replace("/[^0-9]/", "", $_value);
    }

}
