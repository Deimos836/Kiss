<?php

class kissData{
    private $data;

    public function __construct($data){
        $this->data = $data;
        return true;
    }

    public function safe_size($size=0, $execute=false){ // Check the size and if the file is executatble
        if($execute=false){
            if(is_executable($execute)){
                return false;
            }
        }

        if(is_string($this->data)){
            if(strlen($this->data) <= $size){
                return true;
            }return false;

        }elseif(is_file($this->data)){
            if(filesize($this->data) <= $size){
                return true;
            }return false;

        }elseif(is_numeric($this->data)){
            if($this->data < $size){
                return true;
            }return false;

        }return false;
    }

    public function hash($function=PASSWORD_BCRYPT, $cost=12){ // Hash string (to protect password for example ...)
        if(is_string($this->data)){
            return password_hash($this->data, $function, ['cost' => $cost]);
        }return false;
    }

    public function is_image(){ // Check if the file is an image
        try{
            getimagesize($this->data);
            return true;
        }catch(Exception $e){
            return false;
        }
    }

    public function image($size, $option=['jpg', 'jpeg', 'png', 'gif']){ // Check size and extention of image
        if($this->is_image($this->data) and in_array(pathinfo($this->data)['extention'], $option) and filesize($this->data) <= $size){
            return true;
        }return false;
    }

    public function image_reduction($max_size, $dst, $quality=70){ // Reduct image size by selecting compression quality and max border size (don't change gif size)
        if($this->is_image($this->data)){
            $imageSize = getimagesize($this->data);
            list($W, $H) = getimagesize($this->data);
            if($W <= $max_size and $H <= $max_size){
                if($imageSize['mime'] == 'image/jpeg' or $imageSize['mime'] == 'image/jpg' or $imageSize['mime'] == 'image/png'){
                    if($imageSize['mime'] == 'image/jpeg' or $imageSize['mime'] == 'image/jpg'){
                        $imageRessource = imagecreatefromjpeg($this->data);

                    }elseif($imageSize['mime'] == 'image/png'){
                        $imageRessource = imagecreatefrompng($this->data);
                    }
                    imagejpeg($imageRessource, $dst, $quality);

                }elseif ($imageSize['mime'] == 'image/gif'){
                    move_uploaded_file($this->data, $dst);
                }

            }else{
                if ($W != $H){
                    $min = min($W, $H);
                    $max = max($W, $H);
                    $ratio = $min/$max;
                }else{
                    $min = $H;
                    $max = $H;
                    $ratio = 1;
                }
                
                if ($max == $W){
                    $width = $max_size;
                    $height = $max_size * $ratio;
                }elseif ($max == $H){
                    $height = $max_size;
                    $width = $max_size * $ratio;
                }
                
                if($imageSize['mime'] == 'image/jpeg' or $imageSize['mime'] == 'image/jpg' or $imageSize['mime'] == 'image/png'){
                    
                    if($imageSize['mime'] == 'image/jpeg' or $imageSize['mime'] == 'image/jpg'){
                        $imageRessource = imagecreatefromjpeg($this->data);
                    }elseif($imageSize['mime'] == 'image/png'){
                        $imageRessource = imagecreatefrompng($this->data);
                    }

                    $imageFinal = imagecreatetruecolor($width, $height);
                    $final = imagecopyresampled($imageFinal, $imageRessource, 0,0,0,0, $width, $height, $imageSize[0], $imageSize[1]) ;
                    imagejpeg($imageFinal, $dst, $quality);

                }elseif ($imageSize['mime'] == 'image/gif'){
                    move_uploaded_file($this->data, $dst);
                }
            }
            if(isset($imageRessource)){
                imagedestroy($imageRessource);
            }return true;
        }else{
            return false;
        }
    }

    public function image_thumbnail($dst, $size=512, $quality=70){ // Make thumbnail from an image
        if($this->is_image($this->data)){

            $taille = getimagesize($this->data);

            if ($taille['mime']=='image/jpeg') {
                $im = imagecreatefromjpeg($this->data);

            }elseif ($taille['mime']=='image/png') {
                $im = imagecreatefrompng($this->data);

            }elseif ($taille['mime']=='image/gif') {
                $im = imagecreatefromgif($this->data);
            }

            $size = min(imagesx($im), imagesy($im));
            list($widht, $height) = getimagesize($this->data);

            if ($size == $widht){
                $x = 0;
                $y = ($height - $size)/2;

            }elseif ($size == $height){
                $x = ($widht - $size)/2;
                $y = 0;
            }

            $ratio = $widht / $height;
            $im2 = imagecrop($im, ['x' => $x, 'y' => $y, 'width' => $size, 'height' => $size]);
            $img_petite = imagecreatetruecolor($quality, $quality) or $img_petite = imagecreate($quality, $quality);
            imagecopyresized($img_petite, $im2, 0, 0, 0, 0, $quality*$ratio, $quality, $taille[0], $taille[1]);

            if ($im2 !== false) {
                imagepng($img_petite, $dst);
                imagedestroy($im2);
            }
            imagedestroy($im);

        }else{
            return false;
        }
    }

    public function str_replace_array($array, $text){ // str_replace but using array
        return(str_replace(array_keys($array), array_values($array), $text));
    }
}

class kissSQL{ // Safe sql request 
    private $_bdd;
    private $_donnees;
    private $_reponse;

    public function __construct($database) // PDO connexion
    {
        $options = [
            PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
        ];
        try{
            $this->_bdd = new PDO('mysql:host='.$database[0].';dbname='.$database[1].';charset=utf8', ''.$database[2].'', ''.$database[3].'', $options);
        }
        catch (Exception $e){
            return 'Erreur : ' . $e->getMessage();
        }
    }

    public function PDO(){ // Return PDO object
        return $this->_bdd;
    }

    public function select($table, $colonum = '*', $where = [], $limit = '', $desc = false){ // SQL select request (retunr array)
        if($desc == true){
            $desc = 'DESC';
        }else{
            $desc = '';
        }

        if($limit != ''){
            $limit = 'LIMIT '.$limit;
        }

        if(!empty($where)){
            $str_where = '';

            foreach ($where as $cle => $element) {
                $str_where .= ' '.$cle.' = ? AND';
            }
            $str_where = substr($str_where, 0, -4);
            $str_where = 'WHERE'.$str_where;

            $sql = "SELECT {$colonum} FROM {$table} {$str_where} {$desc} {$limit}";

            $this->_stmt = $this->_bdd->prepare($sql);           
            $this->_stmt->execute(array_values($where));

        }else{
            $sql = "SELECT {$colonum} FROM {$table} {$desc} {$limit}";
            $this->_stmt = $this->_bdd->query($sql);
        }
        
        $this->_reponse = $this->_stmt;
        $this->_donnees = $this->_stmt->fetch();
        
        return $this->_donnees;
    }

    public function insert($table, $value) // SQL insert request 
    {
        $str_interrogation = '';
        $str_key = '';

        foreach ($value as $cle => $element) {
            $str_key .= ''.$cle.', ';
            $str_interrogation .= '?, ';
        }

        $str_key = substr($str_key, 0, -2);
        $str_interrogation = substr($str_interrogation, 0, -2);

        $sql = "INSERT INTO {$table} ({$str_key}) VALUES ({$str_interrogation})";
        $this->_stmt = $this->_bdd->prepare($sql);
        $this->_stmt->execute(array_values($value));
    }

    public function count($table, $where) // SQL count request (return int)
    {
        if(!empty($where)){
            $str_where = '';
            foreach ($where as $cle => $element) {
                $str_where .= ' '.$cle.' = ? AND';
            }
            $str_where = substr($str_where, 0, -4);

            $sql = "SELECT COUNT(*) as num_rows FROM {$table} WHERE{$str_where}";
            $this->_stmt = $this->_bdd->prepare($sql);
            $this->_stmt->execute(array_values($where));
        }else{
            $stmt = $this->_bdd->query("SELECT COUNT(*) as num_rows FROM {$table}");
        }

        $this->_stmt = $this->_stmt->fetch();
        return $this->_stmt['num_rows'];
    }

    public function update($table, $where, $value){ // SQL update request
        $str_where = '';
        foreach ($where as $cle => $element) {
            $str_where .= ' '.$cle.' = ? AND';
        }
        $str_where = substr($str_where, 0, -4);

        $str_values = '';
        foreach ($value as $cle => $element) {
            $str_values .= ' '.$cle.' = ?,';
        }
        $str_values = substr($str_values, 0, -1);

        $sql = "UPDATE {$table} SET{$str_values} WHERE{$str_where}";
        $this->_stmt = $this->_bdd->prepare($sql);
        $ar = array_values($value);
        foreach($where as $cle => $element){
            array_push($ar, $element);
        }
        $this->_stmt->execute($ar);
    }

    public function delete($table, $where){ // SQL delete request
        $str_where = '';
        foreach ($where as $cle => $element) {
            $str_where .= ' '.$cle.' = ? AND';
        }
        $str_where = substr($str_where, 0, -4);
        $str_where = 'WHERE'.$str_where;
        
        $sql = "DELETE FROM {$table} {$str_where}";

        $this->_stmt = $this->_bdd->prepare($sql);           
        $this->_stmt->execute(array_values($where));
    }

    public function increment($table, $colonum){
        $this->_bdd->prepare("UPDATE {$table} SET {$colonum} = {$colonum} + 1")->execute();
    }

    public function decrement($table, $colonum){
        $this->_bdd->prepare("UPDATE {$table} SET {$colonum} = {$colonum} - 1")->execute();
    }
}

?>