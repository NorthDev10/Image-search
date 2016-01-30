<?php
define('GOOGLEIMAGES', TRUE);

require_once './config.php';
require_once CLASSES . 'KeyManager.php';

$KeyManager = new KeyManager();

function getRequest($url) {
   $ch = curl_init();
   curl_setopt($ch,CURLOPT_URL, $url);
   curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
   $result = curl_exec($ch);
   curl_close($ch);
   return $result;
}

function formingRequest($API_KEY, $KeyManager) {
if(!empty($_GET['q'])) {
    $formingLink = 'https://www.googleapis.com/customsearch/v1?cx=001428079235406036206:yvfup4iyigy&key='.$API_KEY.'&cref&searchType=image&num=10';
    if(is_numeric($_GET['start'])) {
        $formingLink .= '&start='.$_GET['start'];
    }
    if(!empty($_GET['imgColorType'])) {
        $formingLink .= '&imgColorType='.$_GET['imgColorType'];
    }
    if(!empty($_GET['imgSize'])) {
        $formingLink .= '&imgSize='.$_GET['imgSize'];
    }
    if(!empty($_GET['fileType'])) {
        $formingLink .= '&fileType='.$_GET['fileType'];
    }
    if(!empty($_GET['siteSearch'])) {
        $formingLink .= '&siteSearch='.urlencode($_GET['siteSearch']);
    }
    if(!empty($_GET['cr'])) {
        $formingLink .= '&cr='.urlencode($_GET['cr']);
    }
    $formingLink .= '&q='.urlencode($_GET['q']);
    $json = getRequest($formingLink);
    $DataArray = json_decode($json, true);
    if(isset($DataArray['error'])) {
     
       switch($DataArray['error']['code']) {
          case 400:
             echo json_encode(array('error' => 'Неверный запрос'));
          break;
          case 403:
             $API_KEY = $KeyManager->findKey();
             if($API_KEY) {
                formingRequest($API_KEY, $KeyManager);
             } else {
                echo json_encode(array('error' => 'Лимит всех API ключей исчерпан'));
             }
          break;
          default:
             echo json_encode(array('error' => $DataArray['error']['message']));
       }
    } else {
       echo $json;
    }
}
}

formingRequest($KeyManager->getKey(), $KeyManager);