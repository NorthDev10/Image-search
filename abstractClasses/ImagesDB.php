<?php
defined('GOOGLEIMAGES') or die('Access denied');

abstract class ImagesDB {
    
    private static $_numImages;

    protected function fetchAll($currentPage=1, $numberImages=10) {
        $sqlCode = 'SELECT * FROM ';
        $sqlCode .= 'RequestKeyWords, GoogleImages';
        $sqlCode .= ' WHERE RequestID=RequestKeyWordsID';
        $sqlCode .= ' LIMIT %1$d, %2$d';
        $page = ($currentPage-1) * $numberImages;
        $sqlCode = sprintf($sqlCode, $page, $numberImages);
        if($result = $this->mysqli->query($sqlCode)) {
            $records = array();
            while ($row = $result->fetch_assoc()) {
                $temp = new $this($this->mysqli);
                $temp->setOptions($row);
                $records[] = $temp;
            }
            $result->close();
            return $records;
        }
    }

    public function insert() {
        $newKeywords = true;
        $sqlCode = "INSERT INTO RequestKeyWords (KeyWords, NumLink) VALUES (
        '%s', '1')";
        $sqlCode = sprintf(
            $sqlCode,
            $this->mysqli->real_escape_string($this->getKeyWords())
        );
        
        if($this->mysqli->query($sqlCode)) {
            $this->setRequestID($this->mysqli->insert_id);
        } else {
            $newKeywords = false;
            $sqlCode = "SELECT RequestID FROM RequestKeyWords WHERE KeyWords='%s'";
            $sqlCode = sprintf(
                $sqlCode,
                $this->mysqli->real_escape_string($this->getKeyWords())
            );
            if($result = $this->mysqli->query($sqlCode)) {
                $this->setRequestID($result->fetch_row()[0]);
                $result->close();
            }
        }
        
        $sqlCode = "INSERT INTO GoogleImages (RequestKeyWordsID, OriginalLink,
        	ImageLink, LinkPageImg) VALUES ('%d', '%s', '%s', '%s')";
        $sqlCode = sprintf(
            $sqlCode,
            $this->getRequestID(),
            $this->mysqli->real_escape_string($this->getOriginalLink()),
            $this->mysqli->real_escape_string($this->getImageLink()),
            $this->mysqli->real_escape_string($this->getLinkPageImg())
        );
        if($this->mysqli->query($sqlCode) && !$newKeywords) {
            $sqlCode = "UPDATE RequestKeyWords SET NumLink = NumLink + 1
                WHERE RequestID = '%d'";
            $sqlCode = sprintf($sqlCode, $this->getRequestID());
            $this->mysqli->query($sqlCode);
        }
    }
    
    // заполнят объект данными из БД
    protected function setOptions(array $options) {
        $methods = get_class_methods($this);
        foreach($options as $key => $value) {
            $method = 'set'.ucfirst($key);
            if(in_array($method, $methods)) {
                $this->$method($value);
            }
        }
    }
    
    public function countImages() {
        if(!self::$_numImages) {
            $sqlCode = "SELECT COUNT(RequestKeyWordsID) FROM GoogleImages";
            if($result = $this->mysqli->query($sqlCode)) {
                self::$_numImages = $result->fetch_row()[0];
            }
        }
        return self::$_numImages;
    }

    public function removeImg($RequestKeyWordsID, $ImageLink) {
        if(is_numeric($RequestKeyWordsID)) {
            if(preg_match('/\/images\/([A-Za-z0-9\_\-]+)\/(\d+\.jpg)$/', $ImageLink, $matches)) {
                if(file_exists(DOWNLOAD_DIR_IMG.$matches[1].'/'.$matches[2])) {
                    $sqlCode = "DELETE FROM GoogleImages WHERE RequestKeyWordsID='%d' AND ImageLink='%s'";
                    $sqlCode = sprintf(
                        $sqlCode,
                        $RequestKeyWordsID,
                        $this->mysqli->real_escape_string($ImageLink)
                    );
                    $this->mysqli->query($sqlCode);
                    if($this->mysqli->affected_rows > 0) {
                        $sqlCode = "DELETE FROM RequestKeyWords WHERE RequestID='%d' AND (NumLink-1) = 0";
                        $sqlCode = sprintf($sqlCode, $RequestKeyWordsID);
                        $this->mysqli->query($sqlCode);
                        if($this->mysqli->affected_rows == 0) {
                            $sqlCode = "UPDATE RequestKeyWords SET NumLink = NumLink - 1
                            WHERE RequestID = '%d'";
                            $sqlCode = sprintf($sqlCode, $RequestKeyWordsID);
                            $this->mysqli->query($sqlCode);
                            @unlink(DOWNLOAD_DIR_IMG.$matches[1].'/'.$matches[2]);
                        } else {
                            if (is_dir(DOWNLOAD_DIR_IMG.$matches[1])) {
                                @unlink(DOWNLOAD_DIR_IMG.$matches[1].'/'.$matches[2]);
                                @rmdir(DOWNLOAD_DIR_IMG.$matches[1]);
                            }
                        }
                        return true;
                    }
                }
            }
        }
    }
}