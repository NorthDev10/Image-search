<?php
define('GOOGLEIMAGES', TRUE);

require_once './config.php';
require_once CLASSES . 'DataBase.php';
require_once CLASSES . 'Images.php';
require_once CLASSES . 'App.php';
require_once CLASSES . 'KeyManager.php';

switch($_GET['q']) {
  case 'send-download':
    $DownloadImgUrl = new DownloadImgUrl(MAX_SIZE_IMG, MAX_WIDTH_IMG, MAX_HEIGHT_IMG);
    $DownloadImgUrl->setMaxFileSize(MAX_SIZE_IMG);
    $DownloadImgUrl->setDownloadDir(DOWNLOAD_DIR_IMG);
    $DownloadImgUrl->setAbsolutePathImg(ABSOLUTE_PATH_IMG);
    $DownloadImgUrl->downloadListImages($_POST['imgUrl'], $_POST['keyword'], $_POST['folderName']);
  break;
  case 'get-json-imgs':
    $image = new Images();
    echo $image->getJSONImages($_POST['page'], $_POST['number-imgs']);
  break;
  case 'show-all-img':
    require_once './view/downloaded-pictures.html';
  break;
  case 'removes-img':
    if(!empty($_POST['request-key-words-id']) && !empty($_POST['img-url'])) {
      $image = new Images();
      if($image->removeImg($_POST['request-key-words-id'], $_POST['img-url'])) {
        echo json_encode(array('status'=>true));
      } else {
        echo json_encode(array('status'=>false));
      }
    }
  break;
  case 'add-keys-db':
    $KeyManager = new KeyManager();
    $KeyManager->addKeysToDB();
  break;
  default:
    require_once './view/picture-gallery.html';
}