<?php

/**
 * nmovies local
 */
$minfiles = array(
    'js' => array(
        'list' => array(
            '/js/movies.js',
            '/js/keyevent.js',
            '/js/movies.columnsort.js',
            '/js/movies.beheer.js',
            '/js/movies.scrollfilms.js',
            '/js/jquery.dialog.js',
            '/js/jquery.droppable.js',
            '/js/jquery.fullscreen.js',
            '/js/jquery.keycontrol.js',
            '/js/jquery.scrollTo.js',
            '/js/jquery.sortelements.js',
            //'/jquery/jquery-1.8.3.min.js',
            //'/jquery/jquery.fancybox.pack.js',
            '/js/mvc/v_dialog.js',
            '/js/mvc/m_dialog.js',
            '/js/mvc/c_dialog.js',
            '/js/mvc/v_editfilm.js',
            '/js/mvc/m_editfilm.js',
            '/js/mvc/c_editfilm.js',
            '/js/mvc/v_clipboard.js',
            '/js/mvc/m_clipboard.js',
            '/js/mvc/c_clipboard.js',
            '/js/mvc/v_details.js',
            '/js/mvc/m_details.js',
            '/js/mvc/c_details.js',
            '/js/mvc/v_films.js',
            '/js/mvc/m_films.js',
            '/js/mvc/c_films.js',
        ),
        'path' => '/js/nmovies4.min.js',
    ),
    'css' => array(
        'list' => array(
            '/css/main.css',
            '/css/dialog.css',
            '/css/editfilm.css',
            '/css/filmdetails.css',
        //'/jquery/jquery.fancybox.css',
        ),
        'path' => '/css/nmovies4.min.css',
    ),
);

class Util {

    public static function minify($item) {
        global $minfiles;
        $cnt = count($minfiles[$item]['list']);

        $code = '';
        $path = $_SERVER['DOCUMENT_ROOT'] . $minfiles[$item]['path'];
        foreach ($minfiles[$item]['list'] as $minfile) {
            $code .= '/* ' . $minfile . ' */' . "\n";
            $code .= file_get_contents($_SERVER['DOCUMENT_ROOT'] . $minfile);
        }
        file_put_contents($path, $code);
        return $cnt . ' ' . $item . '-bestanden gecombineerd in: <br>' . $path;
    }

    /**
     * Als op het eind geen slash staat, slash op het eind toevoegen.
     * @param type $url
     * @return type
     */
    public static function addEndSlash($url) {
        if (substr($url, strlen($url) - 1, 1) != '/') {
            return $url . '/';
        } else {
            return $url;
        }
    }

    public static function cleanQuerystring($s) {
        $pos = strpos($s, '?');
        if ($pos === false) {
            return $s;
        } else {
            return substr($s, 0, $pos);
        }
    }

    public static function sanatizeString($s) {
        // //real_escape($s); //htmlentities($s, ENT_QUOTES);
        $s = str_replace('--', '', $s);
        $s = str_replace(';', '', $s);
        $s = str_replace('<script', '', $s);
        //str_replace("'", "''", $s));  // Deze vervanging gebeurt al aan de clientkant.
        return $s;
    }

    public static function escapeFilePath($s) {
        return str_replace(
                array(':', '/', '\\', '?'), array('_', '_', '_', '_'), $s
        );
    }

    /**
     * query with "delete"/"update" without "where" is not allowed
     * @param type $sql
     * @return boolean
     */
    public static function isHarmfulQuery($sql) {

        $sqlU = strtoupper($sql);
        $pos = strpos($sqlU, 'DELETE');
        if ($pos === false) {
            $pos = strpos($sqlU, 'UPDATE');
        }
        if ($pos !== false) {
            $pos = strpos($sqlU, 'WHERE');
            if ($pos === false) {
                return true;
            }
        }
        return false;
    }

    public static function passParametersToSql($sql, $params) {
        $pos = -1;
        for ($i = 0; $i < count($params); $i++) {
            $param = trim($params[$i]);
            $pos = strpos($sql, '?', $pos + 1);
            if ($pos == -1) {
                Util::debug_log(
                        "Wrong number of parameters in sql statement.");
                return null;
            }
            if (!is_numeric($param)) {
                $param = self::sanatizeString($param);
                $param = "'$param'";
            }
            $sql = preg_replace('|\?|', $param, $sql, 1);
        }
        return $sql;
    }

    public static function passMyParametersToSql($sql, $params) {
        $pos = -1;
        for ($i = 0; $i < count($params); $i++) {
            $param = trim($params[$i]);
            $pos = strpos($sql, '?', $pos + 1);
            if ($pos == -1) {
                Util::debug_log(
                        "Wrong number of parameters in sql statement.");
                return null;
            }
            if (is_string($param)) {
                $param = "\"" . self::sanatizeString($param) . "\"";
                //$param = self::escapeMySqlQuote($param);
            }
            $sql = preg_replace('|\?|', $param, $sql, 1);
        }
        //self::debug_log($sql);
        return $sql;
    }

    public static function stripHttp($s) {
        return self::stripHeading("http://", $s);
    }

    public static function getSecondWord($s) {
        $w = explode(' ', $s);
        if (count($w) < 2)
            return $s;
        return $w[1];
    }

    public static function stripHeading($heading, $s) {
        if (strlen($s) <= strlen($heading))
            return $s;
        if (substr($s, 0, strlen($heading)) != $heading)
            return $s;

        return substr($s, strlen($heading));
    }

    /**
     * Haal (meegegeven) staartstuk weg.
     * @param type $trailing
     * @param type $s
     * @return type
     */
    public static function stripTrailing($trailing, $s) {
        if (strlen($s) <= strlen($trailing))
            return $s;
        $pos = strripos($s, $trailing);
        if ($pos === false)
            return $s;
        if (substr($s, $pos) != $trailing)
            return $s;
        return substr($s, 0, strlen($s) - strlen($trailing));
    }

    public static function getQueryString($url) {
        $pos = strpos($url, '?');
        if ($pos !== false) {
            return substr($url, $pos + 1);
        } else {
            return $url;
        }
    }

    public static function startsWith($s, $pre) {
        return substr($s, 0, strlen($pre)) == $pre;
    }

    public static function stripQueryString($url) {
        $pos = strpos($url, '?');
        if ($pos !== false) {
            return substr($url, 0, $pos);
        } else {
            return $url;
        }
    }

    public static function strStartsWith($str, $start) {
        return (substr($str, 0, strlen($start)) == $start);
    }

    protected static $mediumstrings = array(
        'dvd5', //0
        'dvd9', //1
        'bd', //2
        'avi', //3
        'mkv', //4
        'overig', //5
        'avchd', //6
        'mpeg', //7
        'wmv', //8
        '[..]'//9
    );

    public static function getAllMedia() {
        return self::$mediumstrings;
    }

    public static function escapeMsSqlQuote($s) {/*
      global $config;
      if ($config->settings['mssql'] != 'ms') {
      trigger_error('geen ms');
      }
      else */
        if (is_string($s)) {
            return str_replace("'", "''", $s);
        } else {
            return $s;
        }
    }

    public static function escapeMySqlQuote($s, $utf8 = false) {
        
        if (is_string($s)) {
            $t = str_replace("\"", "\\\"", $s);
        } else {
            $t = $s;
        }
        if ($utf8) {
            return utf8_encode($t);
        }
        else {
            return $t;
        }
    }

    public static function getMediumFromString($mediumstring) {
        for ($i = 0; $i < count(self::$mediumstrings); $i++) {
            if ($mediumstring == self::$mediumstrings[$i]) {
                return $i;
            }
        }
        return 9;
    }

    public static function getMediumString($medium) {
        if (!isset($medium)) {
            return '';  //self::$mediumstrings[9];
        } else {
            return self::$mediumstrings[$medium];
        }
    }

    protected static function getShortFilePath($file) {
        $fname = $file;
        // try backslash
        $words = explode('\\', $file);
        $n = count($words);
        if ($n > 2) {
            $fname = $words[$n - 2] . '\\' . $words[$n - 1];
        } else {
            // try slash
            $words = explode('\/', $file);
            $n = count($words);
            if ($n > 2) {
                $fname = $words[$n - 2] . '\/' . $words[$n - 1];
            }
        }
        return $fname;
    }

    protected static function getShortPath($path) {

        $path = str_replace('\\', '/', $path);
        //$w = explode(' ', $path);
        //$last = $w[count($w)-1];
        $doc_root = $_SERVER['DOCUMENT_ROOT'];  //C:/xampp/htdocs/movies13/public
        $pos = strpos($doc_root, '/public');
        if ($pos === false)
            return $path;

        $app_root = substr($doc_root, 0, $pos) . '/app'; //C:/xampp/htdocs/movies13/app
        //error_log('app_root=$' . $app_root);

        return str_replace($app_root, '_APP_', $path);
    }

    /**
     * Print array to debug log.
     * @param array $arr
     */
    public static function debug_log($arr, $limit = false) {
        if ($limit) {// not false and not zero
            $arr = array_slice($arr, 0, $limit);
        }
        $backtrace = debug_backtrace();
        $trace = $backtrace[0];
        $file = self::getShortPath($trace['file']);
        error_log($file . '::' . $trace['line'] . '::' . print_r($arr, true));
    }

    public static function getToday() {
        return date('Y-m-d H:i:s');
    }

    public static function addHttp($s) {
        if (empty($s))
            return $s;

        if (substr($s, 0, strlen('http')) != 'http') {
            $s = 'http://' . $s;
        }
        return $s;
    }

    public static function getDate($fmt, $datetime) {
        if (empty($datetime)) {
            return '';
        } else if (is_object($datetime)) {
            //return date('d m Y ', $datetime);    //strtotime($datetime));
            return $datetime->format($fmt);
        } else {
            $w = explode(" ", $datetime);
            if (count($w) > 2)
                return $w[1] . '-' . $w[0] . '-' . $w[2];
            else
                return $datetime;
        }
    }

    public static function reverseDate($s) {
        $ww = explode(' ', $s);
        if (count($ww) == 1)
            return $s;

        $d = $ww[0];
        $t = $ww[1];
        $w = explode("-", $d);
        if (count($w) > 2)
            return $w[2] . '-' . $w[1] . '-' . $w[0] . ' ' . $t;
        else
            return $s;
    }

    public static function addLocalIo($id, $width = 0, $height = 0) {
        global $config;
        
        $imgurl = $config->settings['imgurl'];
        //return "$imgurl?img&id=$id&w=$width&h=$height";
        return "$imgurl/image/$id/$width/$height";
    }

    public static function getThumbUrl($isNormal, $url, $id) {
        global $config;

        if ($config->settings['local_images']) {
            if ($isNormal) {
                return self::addLocalIo($id, 
                        $config->settings['normal-thumb-w'], 
                        $config->settings['normal-thumb-h']);
            } else {
                return self::addLocalIo($id, 
                        $config->settings['small-thumb-w'], 
                        $config->settings['small-thumb-h']);
            }
        } else {
            if ($isNormal) {
                return self::addSenchaIo($url, 
                        $config->settings['normal-thumb-w'], 
                        $config->settings['normal-thumb-h']);
            } else {
                return self::addSenchaIo($url, 
                        $config->settings['small-thumb-w'], 
                        $config->settings['small-thumb-h']);
            }
        }
    }

    /**
     * http://www.sencha.com/learn/how-to-use-src-sencha-io/
     * @global type $config
     * @staticvar string $prefix
     * @param String $url
     * @param Numeric $width
     * @param Numeric $height default null
     * @return String aangepaste url
     */
    public static function addSenchaIo($url, $width = null, $height = null) {
        global $config;
        static $prefix = '';  // Domain sharding

        switch ($prefix) {
            case '':
                $prefix = '1';
                break;
            case '1':
                $prefix = '2';
                break;
            case '2':
                $prefix = '3';
                break;
            case '3':
                $prefix = '4';
                break;
            case '4':
                $prefix = '';
                break;
        }

        $url = self::addHttp($url);
        if ($config->settings['senchaproxy'] && strlen($url) > 0) {
            return "http://src$prefix.sencha.io/$width/$height/$url";
        } else {
            return $url;
        }
    }

    /**
     * Zet lidwoord vooraan, en maakt de gehele titel utf8_encoded.
     * @param String $s Bijv. Flying doctors, The
     * @return String Normale titel
     */
    public static function getNormalTitel($s) {
        return utf8_encode(self::zetLidwoordVooraan($s));
    }

    /**
     * Hoofdletterongevoelige test of meegegeven string een lidwoord is.
     * Onderzocht in lijstjes in de talen EN, NL, GE, FR, ITA, ESP.
     * @param String $s Te onderzoeken string.
     * @return boolean Geeft aan of de string een lidwoord is.
     */
    protected static function isLidwoord($s) {
        $lidwoorden = array(
            "the", "a", "an", // Engels
            "het", "de", "een", // Nederlands
            "der", "die", "ein", "das", // Duits
            "la", "le", "les", "l'", "une", "un", // Frans
            "il", "i", // Italiaans
            "las", "los", "el", // Spaans
            "det"   // Zweeds
        );
        foreach ($lidwoorden as $lidwoord) {
            if ($lidwoord == strtolower($s))
                return true;
        }
        return false;
    }

    /**
     * Hier wordt apostroph verdubbeld, maar dat moet niet meer, omdat
     * escapen van apostrophs al gebeurt in de placeholder afhandeling in de sql-string.
     *
     * @param type $s
     * @return type
     */
    public static function sql_utf8decode($s) {
        return str_replace("'", "''", utf8_decode($s));
    }

    public static function mysql_utf8decode($s) {
        return str_replace("'", "\'", utf8_decode($s));
    }

    public static function mysql_escape($s) {
        return str_replace("'", "\'", $s);
    }

    /**
     * Vervang dubbele enkele quote door enkele.
     * Pas bovendien html-entity-decode toe.
     * @param type $s
     * @return type
     */
    public static function sql_htmldecode($s) {
        return str_replace("'", "''", html_entity_decode($s, ENT_QUOTES));
    }

    /**
     * Valideer of invoer niet te lang is.
     * Indien te lang, geef lege string terug & meld fout.
     * @param type $s
     * @param type $len
     * @return string, empty if source was too long
     */
    public static function valLen($s, $len) {
        if (strlen($s) <= $len) {
            return $s;
        } else {
            $backtrace = debug_backtrace();
            $trace = $backtrace[0];
            $message = 'Input too long: ' . $s . ' is longer than ' . $len;
            error_log($trace['file'] . '::' . $trace['line'] . '::' . $message);

//          self::error_log('Input too long: ' . $s . ' is longer than ' . $len);
            return '';
        }
    }

    /**
     * Zet lidwoord achteraan, na een komma.
     * @param String $s
     * @return String
     */
    public static function getDbTitel($s) {
        if (empty($s))
            return $s;

        $w = explode(" ", $s);
        if (count($w) < 2)
            return $s;

        $firstword = $w[0];
        if (self::isLidwoord($firstword)) {
            array_shift($w);
            return implode(' ', $w) . ', ' . $firstword;
        }
        return $s;
    }

    public static function skipLidwoord($s) {
        if (empty($s))
            return $s;

        // Speciaal geval: l' zoals in l'argent
        $pos = strpos($s, 'l\'');
        if ($pos === 0) {
            return substr($s, 2);
        }

        $w = explode(" ", $s);
        if (count($w) < 2)
            return $s;

        $firstword = $w[0];
        if (self::isLidwoord($firstword)) {
            array_shift($w);
            return implode(' ', $w);
        }
        return $s;
    }

    /**
     * Zet lidwoord vooraan.
     * @param String $s
     * @return String
     */
    public static function zetLidwoordVooraan($s) {
        if (empty($s))
            return $s;

        // vervang double quote door single quote (obsolete?)
        if (substr($s, 0, 1) != "\"") {
            $s = str_replace("\"", "'", $s);
        }

        $w = explode(", ", $s);
        if (count($w) == 2) {

            $ww = explode(' ', $w[1]);

            // Alphaville, une etrange...: niet swappen
            if (count($ww) == 1)
                return $w[1] . ' ' . $w[0];
        }
        return trim($s);
    }

    public static function getAppPath() {
        global $config;
        return $config->app_path;
    }

    /**
     * Haal waarde uit veld. 
     * Als de waarde een string is, geef de trimmed versie, utf8-geencodeerd.
     * @param type $row
     * @param type $field
     * @return null
     */
    public static function getFieldFromRow($row, $field) {
        if (isset($row[$field])) {
            $value = $row[$field];
            if (is_string($value)) {
                return /* utf8_encode */(trim($row[$field]));
            } else {
                return $row[$field];
            }
        } else {
            return null;
        }
    }

    public static function getUtf8EncodedFieldFromRow($row, $field) {
        if (isset($row[$field])) {
            $value = $row[$field];
            if (is_string($value)) {
                return utf8_encode(trim($row[$field]));
            } else {
                return $row[$field];
            }
        } else {
            return null;
        }
    }

    /**
     * Lever het pad naar het xml bestand voor een speler.
     * @global type $config
     * @param type $speler
     * @return type
     */
    public static function getSpelerPath($speler) {
        global $config;

        $outdir = $config->settings['spelerdir'] . '\\';
        return $outdir . strtolower(str_replace(' ', '_', trim($speler))) . '.xml';
    }

    public static function getRegisseurPath($id) {
        global $config;

        $outdir = $config->settings['regisseurdir'] . '/';
        return $outdir . $id . '.xml';
    }

}

?>
