<?php

/**
 *
 *
 * @author jan
 */
class Img_Model {

    function __construct() {
        global $settings;

        $this->afbeeldingendir = $settings['afbeeldingendir'];
    }

    private $afbeeldingendir;// = 'S:\Pictures\Movies'; //'C:\xampp\htdocs\saturnus\Pictures\Movies';

    /*
     * Afbeeldingenpad zonder einde slash
     */
    protected function getAfbeeldingenDir() {
        global $settings;

        return $settings['afbeeldingendir'];
    }

    protected function getFolderBySize($folder, $w, $h) {
        if (empty($w) && empty($h)) {
            //Util::debug_log('Geen folder voor ledige maten');
            return null;
        }
        $folderBySize = $folder . '/' . $w . '_' . $h;
        if (!file_exists($folderBySize)) {
            if (!mkdir($folderBySize)) {
                Util::debug_log('error: ' . $folderBySize);
            }
        }
        return $folderBySize;
    }

    /*
     * Als een dimensie weggelaten is, bereken die dan naar rato van de andere.
     * @param type $srcW
     * @param type $srcH
     * @param type $destW
     * @param type $destH
     * @return type
     */
    protected function getScaledDimensions($srcW, $srcH, $destW, $destH) {
        if ($destW == 0) {
            $destW = ($destH / $srcH) * $srcW;
        }
        if ($destH == 0) {
            $destH = ($destW / $srcW) * $srcH;
        }
        return array('w' => $destW, 'h' => $destH);
    }

    protected function getScaledDimensions2($srcW, $srcH, $destW, $destH) {
        // http://stackoverflow.com/questions/4590441/php-thumbnail-image-resizing-with-proportions
        $destX = $destY = 0;
        if ($srcW > $destW) {
            // Afbeelding is te breed.
            $nDestW = $destW;
            $nDestH = ($destW / $srcW) * $srcH;
            $destX = 0;
            $destY = ($destH - $nDestH) / 2;
            // Aangepast, nu te hoog?
            if ($nDestH > $destH) {
                $nDestH = $destH;
                $nDestW = ($destH / $srcH) * $srcW;
                $destX = ($destW - $nDestW) / 2;
                $destY = 0;
            }
        }
        else if ($srcH > $destH) {
            // Afbeelding is te hoog.
            $nDestH = $destH;
            $nDestW = ($destH / $srcH) * $srcW;
            $destX = ($destW - $nDestW) / 2;
            $destY = 0;
            // Aangepast, nu te breed?
            if ($nDestW > $destW) {
                $nDestW = $destW;
                $nDestH = ($destW / $srcW) * $srcH;
                $destX = 0;
                $destY = ($destH - $nDestH) / 2;
            }
        }
        else {
            if ($srcH > $srcW) {
                // Vergroot de hoogte en dan proportioneel de breedte.
                $ratio = $destH / $srcH;
                $nDestH = $destH;
                $nDestW = $srcW * $ratio;
                $delta = $nDestW - $destW;
                if ($delta > 0) {
                    $destX = $delta / 2;
                }
                $destY = 0;
            }
            else {
                // Vergroot de breedte en dan proportioneel de hoogte.
                $ratio = $destW / $srcW;
                $nDestW = $destW;
                $nDestH = $srcH * $ratio;
                $delta = $nDestH - $destH;
                if ($delta > 0) {
                    $destY = $delta / 2;
                }
                $destX = 0;
            }
        }
        /*
        else {
            // Afbeelding is te klein.
            if ($srcW < $destW) { // te smal
                $nDdestH = ($destW / $srcW) * $srcH;
                $destY = ($destH - $nDestH) / 2;
            }
            if ($srcH < $nDestH) { // (nog) te laag
                $nDestW = ($nDestH / $srcH) * $srcW;
                $destX = ($destW - $nDestW) / 2;
            }
        }
         */
        return array(
            'w' => $nDestW,
            'h' => $nDestH,
            'x' => $destX,
            'y' => $destY,
        );
    }

    protected function deletePic($path, $id) {
        $exts = explode(';', 'jpg;jpeg;png;gif;JPG;JPEG;PNG;GIF');
        foreach ($exts as $ext) {
            //chdir($path);
            $pic = $path . '/' . $id . '.' . $ext;
            //Util::debug_log($pic);

            if (file_exists($pic)) {
                Util::debug_log($pic);
                //Util::debug_log($path);
                if (!unlink($pic)) {
                    Util::debug_log('er ging iets mis bij het verwijderen van ' . $path);
                }
            }

        }
    }

    protected function cacheImage($path, $destPath, $destWidth, $destHeight) {
        // Maak nieuw in cache en geef deze terug.
        $src = $this->imageCreate($path);   //imagecreatefromjpeg($path);
        if (!$src) {
//            Util::debug_log($path);
            return null;
        }
        $dim = $this->getScaledDimensions(
            imagesx($src), imagesy($src),
            $destWidth, $destHeight
        );

        // Maak image met doelafmetingen.
        $dest = imagecreatetruecolor($dim['w'], $dim['h']);

        $white = imagecolorallocate($dest, 255, 255, 255);
        imagefill($dest, 0, 0, $white);

        if ($destWidth != 0 && $destHeight != 0) {
            // Niet bijgeschaald, dan positioneer image bijgeschaald binnen aangegeven maten.
            $dim2 = $this->getScaledDimensions2(
                imagesx($src), imagesy($src),
                $destWidth, $destHeight
            );
            //Util::debug_log($dim2);
            imagecopyresampled(
                $dest, $src,
                $dim2['x'], $dim2['y'], // destination x, destination y
                0, 0,   // source x, source y
                $dim2['w'], $dim2['h'], // destinationw, destination h
                imagesx($src), imagesy($src) // source w, source h
            );
        }
        else {
            imagecopyresampled(
                $dest, $src,
                0, 0,
                0, 0,
                $dim['w'], $dim['h'],
                imagesx($src), imagesy($src)
            );
        }
        $this->imageSave($dest, $destPath);
        //imagejpeg($dest, $destPath, 80); // 0 .. 100
        return $dest;
    }

    protected function imageSave($image, $path) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (empty($ext)) {
            //Util::debug_log('empty path for image');
            return null;
        }


        switch($ext) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($image, $path);
                break;
            case 'gif':
                imagegif($image, $path);
                break;
            case 'png':
                imagepng($image, $path);
                break;
            default:
                Util::debug_log('unknown extension: ' . $ext);
                break;
        }
    }

    protected function imageCreate($path) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (empty($ext)) {
            //Util::debug_log('empty path for image');
            return null;
        }
        //Util::debug_log($path);

        switch($ext) {
            case 'jpg':
            case 'jpeg':
                return imagecreatefromjpeg($path);
            case 'gif':
                return imagecreatefromgif($path);
            case 'png':
                return imagecreatefrompng($path);
            default:
                Util::debug_log('unknown extension: ' . $ext);
                return null;
        }
    }

    protected function getFileName($dir, $id) {
        $extensions = array('jpg', 'jpeg', 'gif', 'png');
        foreach($extensions as $extension) {
            $path = $dir . '/' . $id . '.' . $extension;
            if (file_exists($path)) {
                return $path;
            }
        }
        return null;
    }

    /**
     * Haal een bijgeschaalde afbeelding uit de cache als hij al bestaat, of maak hem,
     * afgeleid van het origineel (als dat al bestaat).
     * Als geen origineel bestaat, stuur dan een bijgeschaalde 'not-found' afbeelding terug.
     */
    public function getScaledImage($id, $w=0, $h=0) {
        $dir = $this->getAfbeeldingenDir();
        /*
        clearstatcache(true, $dir);
        if (!file_exists($dir)) {
            //Util::debug_log('Kan afbeeldingendir niet vinden (' . $dir . ')');
            //return;
        }*/
        $fileName = $this->getFileName($dir, $id);
        if (!$fileName) {  // File does not exist
            $fileName = 'notfound.jpg';
            $path = $dir . '/' . $fileName;
        }
        else {
            $path = $fileName;
            $fileName = pathinfo($path, PATHINFO_FILENAME);
        }

        $destWidth = $w;
        $destHeight = $h;

        $folderBySize = $this->getFolderBySize(
            $dir,
            $destWidth,
            $destHeight
        );

        // Niet bijschalen.
        if ($w == 0 && $h == 0) {
            return $this->imageCreate($path);
        }
        // Iets niet gelukt met schaalfolder.
        if (!$folderBySize) {
            Util::debug_log('Foutje met folderbysize');
            return null;
        }
        $destPath = $folderBySize . '/' . $fileName;
        //Util::debug_log($destPath);
        if (file_exists($destPath)) {
            // Haal uit cache.
            //return imagecreatefromjpeg($destPath);
            return $this->imagecreate($destPath);
        }
        else {
            return $this->cacheImage($path, $destPath, $destWidth, $destHeight);
        }
    }

    public function deleteScaledPics($id) {
        // Verwijder afbeeldingen met deze id in de root.
        $this->deletePic($this->afbeeldingendir, $id);

        // Idem in alle subdirectories.
        foreach (new DirectoryIterator($this->afbeeldingendir) as $fileInfo) {
            if ($fileInfo->isDir() && !$fileInfo->isDot()) {
                $this->deletePic($fileInfo->getPathname(), $id);
            }
        }
    }

    private function debug_https() {
        $w = stream_get_wrappers();
        Util::debug_log('openssl: '.  extension_loaded  ('openssl') ? 'yes':'no');
        Util::debug_log('http wrapper: '. in_array('http', $w) ? 'yes':'no');
        Util::debug_log('https wrapper: '. in_array('https', $w) ? 'yes':'no');
        Util::debug_log('wrappers: '. var_export($w));
    }

    public function saveAfbeelding($path, $id) {
        if (empty($path) || $path === 'undefined') {
            //Util::debug_log('undefined path for id=' . $id);
            return;
        }
        if (!$id) {
            Util::debug_log('undefined image for id=0');
            return;
        }

        $npath = Util::stripQueryString(str_replace(' ', '%20', $path));
        $ext = strtolower(pathinfo($npath, PATHINFO_EXTENSION));
        //Util::debug_log($npath);
//        debug_https();
        /*
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $npath);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $file = curl_exec($ch);
        curl_close($ch);
*/
        $file = file_get_contents($npath);
        if (!$file){
            Util::debug_log('Niet opgehaald: ' . $npath);
        }
        else {
            $this->deleteScaledPics($id);
        }
        //Util::debug_log(count($file));

        $fileName = $this->afbeeldingendir . '/' . $id . '.' . $ext; //'.jpg';
//        Util::debug_log($fileName);
        file_put_contents($fileName, $file);
    }
/*
    public function moveAfbeelding($tmp_name, $id, $extension) {
        $targetName = $id . '.' . $extension;
        $target = $this->getAfbeeldingenDir() . '\\' . $targetName;

        Util::debug_log($target);
        Util::debug_log($tmp_name);

        if (move_uploaded_file($tmp_name, $target)){
            Util::debug_log('upload: success');
        }
    }
*/
}
