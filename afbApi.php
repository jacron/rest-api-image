<?php

/*
 * Author: jan
 * Date: Nov 7, 2013
 */

/**
 * afbApi = API voor afbeeldingen
 *
 * @author jan
 *
 * Voorbeeld-aanroepen:
 * cUrl -i -H "Accept: Content-type: image/jpeg" http://imgApi.local/image/3/120/320
 *
 *         $url = $config->settings['imgurl'] . '/newafb/' . $id . '/' . $afburl;
 *
 * Nog niet echt RESTful, want ook het opslaan of verwijderen van een afbeelding gaat via GET.
 *
 * Verder is er nog geen enkelke controle op cross site toegang of toegangssleutels.
 *
 */
require_once 'API.php';

class afbApi extends API{

    protected function verifyKey($key, $origin) {
        return true;
        //return $key === 'SuDUf6ep';
    }

    public function __construct($request, $origin) {
        parent::__construct($request);
        /*
        if (!array_key_exists('apiKey', $this->request)) {
            throw new Exception('No API Key provided');
        } else if (!$this->verifyKey($this->request['apiKey'], $origin)) {
            throw new Exception('Invalid API Key');
        }
         */
    }

    /**
     * endpoint: image
     * get scaled image
     * @param type $args containing id[,w][,h]
     */
    public function image($args, $status = 200) {

        $imgModel = new Img_Model();
        $id = $args[0];
        $w = isset($args[1])? $args[1] : null;
        $h = isset($args[2])? $args[2] : null;
        $img = $imgModel->getScaledImage($id, $w, $h);
        if ($img) {
            $this->_response('', $status);

            // Content:  imaage
            header("Content-Type: image/jpeg");

            // Caching: one year.
            /*
            $now = time( );
            $then = gmstrftime("%a, %d %b %Y %H:%M:%S GMT", $now + 365*86440);
            header("Expires: $then");
             */

            imagejpeg($img);
        }
        else {
            $this->_response('', 500);
        }
    }

    public function newafb($args, $status = 200) {
        //Util::debug_log($args);
        $imgModel = new Img_Model();
        $id = $args[0];
        $url = implode(array_slice($args, 1), '/'); //$args[1];
        $imgModel->saveAfbeelding($url, $id);
    }
/*
    public function moveafb($args, $status = 200) {
        $imgModel = new Img_Model();
        $id = $args[0];
        $tmp_name = $args[2];
        $ext = $args[1];
        $imgModel->moveAfbeelding($tmp_name, $id, $ext);
    }
*/
    public function delete($args, $status = 200) {
        $imgModel = new Img_Model();
        $id = $args[0];
        $imgModel->deleteScaledPics($id);
    }

}
