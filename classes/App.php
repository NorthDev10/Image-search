<?php
defined('GOOGLEIMAGES') or die('Access denied');

class DownloadImgUrl {
    private $maxFileSize;
    private $maxWidthImage;
    private $maxHeightImage;
    private $limitExceeded;
    private $downloadDir;
    private $imageType;
	private $absolutePathImg;
    
    /*
    * $fsize - размер картинки в мегабайтах
    * $maxW - максимальная ширина картинки
    * $maxH - максимальная высота картинки
    */
    public function __construct($fsize = 10, $maxW = 10080, $maxH = 10080) {
        $this->maxFileSize = $fsize*1048576; // mb
        $this->maxWidthImage = $maxW; // px
        $this->maxHeightImage = $maxH; // px
        $this->overwrite = false; // перезаписывать файл
        $this->limitExceeded = false; // флаг, указывает превышен ли размер загруженного файла
        $this->downloadDir = 'images/';
        $this->absolutePathImg = 'images/';
    }
	
	/*
    * абсолютный путь к папке с картинками (ссылка, которая будет возвращать к клиенту)
    */
    public function setAbsolutePathImg($dir) {
        $this->absolutePathImg = $dir;
    }
    
    /*
    * относительный путь к папке, куда будет загружатся картинка
    */
    public function setDownloadDir($dir) {
        $this->downloadDir = $dir;
    }
    
    /*
    * устанавливает размер картинки в мегабайтах
    */
    public function setMaxFileSize($fsize) {
        $this->maxFileSize = 1048576 * $fsize;
    }
    
    /*
    * метод загружает картинки по списку
    * $imgUrlArr - массив ссылок на картинку
    * $keyword - ключевое слово
    * 
    */
    public function downloadListImages($imgUrlArr, $keyword, $folderName='') {
        if(!empty($imgUrlArr) && !empty($keyword)) {
            $newImgArr = array();
            if (get_magic_quotes_gpc()) {
                $imgUrlArr = json_decode(stripslashes($imgUrlArr), true);
            } else {
                $imgUrlArr = json_decode($imgUrlArr, true);
            }
            if($imgUrlArr != null) {
                $keywordT = $this->translit($keyword);
                $folderNameT = $this->translit($folderName);
                if(!(strlen($folderNameT) > 0)) {
                    $folderNameT = $keywordT;
                } else {
                    $keyword = $folderName;
                }
                if (!is_dir($this->downloadDir.$folderNameT)) {
                    @mkdir($this->downloadDir.$folderNameT, 0755);
                }
                if(strlen($this->absolutePathImg.$keywordT) <= 240) {
                    foreach($imgUrlArr as $val) {
                        if($this->downloadImage($val['url'], $folderNameT.'/'.$val['id'])) {
                            $image = new Images();
                            $image->setKeyWords($keyword);
                            $image->setOriginalLink($val['url']);
                            $image->setImageLink($this->absolutePathImg.$folderNameT.'/'.$val['id'].'.jpg');
                            $image->setLinkPageImg($val['oUrlSite']);
                            $image->insert();
                            $newImgArr[] = array(
                                'url' => $this->absolutePathImg.$folderNameT.'/'.$val['id'].'.jpg'
                            );
                        }
                    }
                    echo json_encode($newImgArr);
                } else {
                    echo json_encode(array('error'=>'Ключевые слова должны иметь длину не более 240 символов'));
                }
            }
        }
    }
    
    /*
    * загружает картинку по ссылке
    * $imgUrl - ссылка на картинку
    * $fileName - путь к картинки
    */
    public function downloadImage($imgUrl, $fileName) {
        if(!empty($imgUrl) && !empty($fileName)) {
            $fileSize = 0;
            $ReadFile = @fopen($imgUrl, "rb");
            if ($ReadFile) {
                $tmpFileName = tempnam("tmp", "IMG");
                $WriteFile = fopen($tmpFileName, "wb");
                if ($WriteFile){
                    while(!feof($ReadFile) && ($fileSize <= $this->maxFileSize)) {
                        $data = fread($ReadFile, 4096);
                        fwrite($WriteFile, $data);
                        $fileSize += mb_strlen($data, 'UTF-8');
                    }
                    fclose($WriteFile);
                }
                if(!feof($ReadFile)) {
                    $this->limitExceeded = true;
                }
                fclose($ReadFile);
                if(!$this->limitExceeded && $this->checkImgResolution($tmpFileName)) {
                    if($this->saveImage($tmpFileName, $fileName)) {
						unlink($tmpFileName);
						return true;
					}
                }
                unlink($tmpFileName);
            }
        }
    }
    
    /*
    * транслит
    */
    protected function translit($str) {	
    	if (preg_match('/[^A-Za-z0-9_\-]/', $str)) {
    		$tr = array(
    			"А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",
    			"Д"=>"d","Е"=>"e","Ж"=>"j","З"=>"z","И"=>"i",
    			"Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
    			"О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
    			"У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch",
    			"Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"yi","Ь"=>"",
    			"Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
    			"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
    			"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
    			"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
    			"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
    			"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
    			"ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya", 
    			" –"=> ""," -"=> "", ","=> "", " "=> "-", "."=> "",
    			"/"=> "_","-"=> "","ї"=>"i","Ї"=>"i","і"=>"i",
    			"І"=>"i","є"=>"je","Є"=>"je","Ё"=>"e","ё"=>"e"
    		);
        	$str = strtr($str, $tr);
        	return preg_replace('/[^A-Za-z0-9_\-]/', '', $str);
    	}
    	return $str;
    }
    
    /*
    * узнаем mime type и проверяем размеры картинки
    */
    protected function checkImgResolution($fileName) {
        $fileInfo = getimagesize($fileName);
        $this->imageType = $fileInfo['mime'];
        if(($this->maxWidthImage >= $fileInfo[0]) && ($this->maxHeightImage >= $fileInfo[1])) {
            return true;
        }
    }
    
    /*
    * сохраняем картинку
    */
    protected function saveImage($path, $imageName) {
        switch ($this->imageType) { 
            case 'image/gif': 
                $image = @imageCreateFromGif($path); 
            break; 
            case 'image/jpeg': 
                $image = @imageCreateFromJpeg($path); 
            break; 
            case 'image/png': 
                $bg = @imageCreateFromPng($path);
                $image = @imagecreatetruecolor(imagesx($bg), imagesy($bg));
                @imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
                @imagealphablending($image, true);
                @imagecopy($image, $bg, 0, 0, 0, 0, imagesx($bg), imagesy($bg));
                @imagedestroy($bg);
            break;
            default:
                $image = false;
        }
        if($image !== false) {
            @imageJpeg($image, $this->downloadDir.$imageName.'.jpg');
            @imageDestroy($image);
			return true;
        }
    }
}