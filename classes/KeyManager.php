<?php
defined('GOOGLEIMAGES') or die('Access denied');

class KeyManager {

    private $key;
    private $arrayKeys;
    
    public function getKey() {
        if(!$this->getArrayKeysFromDB()) {
            if(!$this->addKeysToDB()) {
                die(json_encode(array('error' => 'Не найдено ни одного API ключа')));
            }
        }
        foreach($this->arrayKeys as $val) {
            if(isset($val['used'])) {
                return $val['key'];
            }
        }
        return $this->findKey();
    }
    
    /*
    *  метод выполняет поиск действующего ключа
    */
    public function findKey() {
        $this->addKeysToDB();
        if(!$this->arrayKeys) {
            if(!$this->getArrayKeysFromDB()) {
                die(json_encode(array('error' => 'Не найдено ни одного API ключа')));
            }
        }
        foreach($this->arrayKeys as $key=>$val) {
            if(isset($this->arrayKeys[$key]['used'])) {
                unset($this->arrayKeys[$key]['used']);
            }
            if((time() - $val['date']) >= 86400) {
                $this->arrayKeys[$key]['used'] = true;
                $this->arrayKeys[$key]['date'] = time();
                $this->recordArrayKeysFromDB();
                return $val['key'];
            }
        }
        
    }
    
    /*
    *  чтение сформированного списка с ключами
    */
    protected function getArrayKeysFromDB() {
        if(file_exists('api-keys.json')) {
            $this->arrayKeys = json_decode(file_get_contents('api-keys.json'), true);
            return true;
        }
    }
    
    /*
    *  запись сформированного списка с ключами
    */
    protected function recordArrayKeysFromDB() {
        file_put_contents(
            'api-keys.json',
            json_encode($this->arrayKeys)
        );
    }
    
    /*
    *  чтение пользовательского списка с ключами
    */
    protected function getArrayKeysFromTxtFile() {
        if(file_exists(USER_API_KEYS)) {
            $arrayApiKeys = array();
            $hKeysFile = fopen(USER_API_KEYS, "r");
            while(!feof($hKeysFile)) {
                $temp = trim(fgets($hKeysFile));
                if(mb_strlen($temp, 'UTF-8') > 3) {
                    $arrayApiKeys[] = $temp;
                }
            }
            fclose($hKeysFile);
            @unlink(USER_API_KEYS);
            return $arrayApiKeys;
        }
        return array();
    }
    
    /*
    *  данный метод добавляет все новые пользовательские ключи себе в список
    */
    public function addKeysToDB() {
        if(!file_exists('api-keys.json')) {
            $simplyArrayKeys = $this->getArrayKeysFromTxtFile();
            foreach($simplyArrayKeys as $val) {
                $this->arrayKeys[] = array('key' => $val, 'date' => (time()-86400));
            }
            if($simplyArrayKeys) {
                $this->recordArrayKeysFromDB();
                return true;
            }
        } else {
            $arrayKeysFromFile = $this->getArrayKeysFromTxtFile();
            if($arrayKeysFromFile) {
                $this->getArrayKeysFromDB();
                $simplyArrayKeys = array();
                foreach($this->arrayKeys as $val) {
                    $simplyArrayKeys[] = $val['key'];
                }
                // находим новые API ключи
                $simplyArrayKeys = array_diff($arrayKeysFromFile, $simplyArrayKeys);
                foreach($simplyArrayKeys as $val) {
                    $this->arrayKeys[] = array('key' => $val, 'date' => (time()-86400));
                }
                $this->recordArrayKeysFromDB();
                return true;
            }
        }
    }
}