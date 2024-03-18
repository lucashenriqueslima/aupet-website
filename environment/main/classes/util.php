<?php
namespace main\classes;

class Util {
    // $add = '7days', '1 month' ...
    public static function date_add($date, $add) {
        return date('Y-m-d', strtotime("+{$add}", strtotime($date)));
    }

    public static function formatDate($date, $format) {
        return date($format, strtotime($date));
    }

    function maskToDate($data) {
        if(!$data) return 'NULL';
        $data = explode('/', $data);
        $data = $data[2] . '-' . $data[1] . '-' . $data[0];
        return $data;
    }

    public static function createHash($size = 12) {
        // return substr(hash('md5', rand(1, 100000000000)), 0, $size);
        return substr(md5(uniqid(rand(), true)), 0, $size);
    }

    public static function getFileDaraUri($uri, $maxWidth = 1300, $maxHeight = 1300) {
        $ext = explode(';base64,', $uri)[0];
        if (strpos($ext, 'image/') !== false && strpos($ext, 'image/bmp') === false) {
            $simpleImage = new \SimpleImage3();
            $simpleImage->fromDataUri($uri);
            return ($simpleImage->bestFit($maxWidth,$maxHeight)->toString());
        } else {
            return (base64_decode(explode(';base64,', $uri)[1]));
        }
    }

    public static function getFileDaraUriAws($uri, $maxWidth = 1300, $maxHeight = 1300) {
        throw new Exception("Metodo em desuso. Use getFileDaraUriAwsDecode", 1);
        $ext = explode(';base64,', $uri)[0];
        if (strpos($ext, 'image') !== false && strpos($ext, 'image/bmp') === false) {
            $simpleImage = new \SimpleImage3();
            $simpleImage->fromDataUri($uri);
            return explode(';base64,',$simpleImage->bestFit($maxWidth,$maxHeight)->toDataUri())[1];
        } else {
            return explode(';base64,', $uri)[1];
        }
    }

    public static function getFileDaraUriAwsDecode($uri, $maxWidth = 1300, $maxHeight = 1300) {
        $ext = explode(';base64,', $uri)[0];
        if (strpos($ext, 'image') !== false && strpos($ext, 'image/bmp') === false) {
            $simpleImage = new \SimpleImage3();
            $simpleImage->fromDataUri($uri);
            return base64_decode(explode(';base64,',$simpleImage->bestFit($maxWidth,$maxHeight)->toDataUri())[1]);
        } else {
            return base64_decode(explode(';base64,', $uri)[1]);
        }
    }

    public static function formataMoeda($valor, $returnZero = true) {
        if($valor === '' && $returnZero) $valor = 0;
        return number_format($valor, 2, ',', '.');
    }

    public static function toDecimal($valor) {
        if (strpos($valor, ',') > -1 && strpos($valor, '.') > -1){
            $valor = str_replace(".", "", $valor);
            $valor = str_replace(",", ".", $valor);
        } else if (strpos($valor, ',') > -1){
            $valor = str_replace(",", ".", $valor);
        } else if(strpos($valor, '.') == false){
            $valor += '.00';
        } 
        return $valor;
    }

    public static function formatBytes($size, $precision = 2) { 
        if($size == 0) return 0;
        $base = log($size, 1024);
        $suffixes = array('', 'K', 'M', 'G', 'T');
        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    }

    public static function json_decode(string $json) {
        return json_decode($json, true);
    }

    public static function json_encode($obj) {
        return json_encode($obj, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function utf8_decode($text) {
        $ret = str_replace("•","&bull;",$text);
        return utf8_decode($ret);
    }

    public static function diaAtualExtenso() {
        return strftime('%d de %B de %Y', strtotime('today'));
    }

    public static function bufferToBase64($buffer) {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
		$type = $finfo->buffer($buffer);
        $data = base64_encode($buffer);
        return "data:".$type.";base64,".$data;
    }
    
    public static function base64RemoveHeader($base64) {
        $data = explode(';base64,',$base64);
        return $data[1];
    }

    public static function echoFile($file, $cache = true) {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $type = $finfo->buffer($file);
        $size = number_format((strlen($file) * 0.000001), 2,'.','');
        if($size >= 4) {
            $ext = explode('/', $type)[1];
            $name = Util::createHash().'.'.$ext;
            header("Content-Disposition: attachment; filename=$name");
        }
        ob_start();
        echo $file;
        header("Content-type: {$type}");
        header("Content-Length: ".ob_get_length());
        if($cache) {
            header("Cache-Control: public, max-age=31536000");
            header_remove('Pragma');
            header_remove('Expires');
        }
        ob_end_flush();
    }

    public static function diasPrevisto($data_atual , $data_entrega , $differenceFormat = '%a' ){
        if(!$data_entrega) return '';
        $datetime1 = date_create($data_atual);
        $datetime2 = date_create($data_entrega);
        $interval = date_diff($datetime1, $datetime2);
        $days_end = $interval->format($differenceFormat);
        if($days_end == 1) $days_end = '1 dia';
        else if($days_end == 0) $days_end = 'Hoje';
        else $days_end = "$days_end dias";
        if($datetime1 > $datetime2) $days_end = $days_end." em atraso";
        return $days_end;
    }

    public static function diasPrevistoNumero($data_atual , $data_entrega , $differenceFormat = '%a' ){
        if(!$data_entrega) return '';
        $datetime1 = date_create($data_atual);
        $datetime2 = date_create($data_entrega);
        $interval = date_diff($datetime1, $datetime2);
        $days_end = $interval->format($differenceFormat);
        return $days_end;
    }
}