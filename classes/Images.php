<?php
defined('GOOGLEIMAGES') or die('Access denied');

require_once ABSTRACTCLASS . 'ImagesDB.php';

class Images extends ImagesDB {

    protected $mysqli;
    private $RequestID;
    private $KeyWords;
    private $NumLink; // количество картинок ссылающийся на данную тему
    private $OriginalLink;
    private $ImageLink;
    private $LinkPageImg; // Ссылка на страницу с картинкой
    
    public function __construct() {
        $db = DataBase::getDBObj();
        $this->mysqli = $db->getConnection();
    }
    
    public function getJSONImages($currentPage = 1, $numberImages = 10) {
        if(!is_numeric($currentPage) || !is_numeric($numberImages) || ($numberImages < 0)) {
            $currentPage = 1;
            $numberImages = 10;
        }
        $records = $this->fetchAll($currentPage, $numberImages);
        if($records) {
            $json = array();
            foreach($records as $key=>$val) {
                $json['img'][$key]['RequestID'] = $val->getRequestID();
                $json['img'][$key]['KeyWords'] = $val->getKeyWords();
                $json['img'][$key]['OriginalLink'] = $val->getOriginalLink();
                $json['img'][$key]['ImageLink'] = $val->getImageLink();
                $json['img'][$key]['LinkPageImg'] = $val->getLinkPageImg();
            }
            $json['countPages'] = ceil($this->countImages() / $numberImages);
            return json_encode($json);
        } else {
            return json_encode(['error'=>'Нe одна картинка еще не загружена']);
        }
    }
    
    public function getRequestID() {
        return $this->RequestID;
    }

    public function setRequestID($RequestID) {
        $this->RequestID = $RequestID;
    }
    
    public function getKeyWords() {
        return $this->KeyWords;
    }

    public function setKeyWords($KeyWords) {
        $this->KeyWords = $KeyWords;
    }
    
    public function getNumLink() {
        return $this->KeyWords;
    }

    public function setNumLink($NumLink) {
        $this->NumLink = $NumLink;
    }
    
    public function getOriginalLink() {
        return $this->OriginalLink;
    }

    public function setOriginalLink($OriginalLink) {
        $this->OriginalLink = $OriginalLink;
    }
    
    public function getImageLink() {
        return $this->ImageLink;
    }

    public function setImageLink($ImageLink) {
        $this->ImageLink = $ImageLink;
    }
    
    public function getLinkPageImg() {
        return $this->LinkPageImg;
    }

    public function setLinkPageImg($LinkPageImg) {
        $this->LinkPageImg = $LinkPageImg;
    }
}