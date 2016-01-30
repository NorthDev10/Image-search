<?php
defined('GOOGLEIMAGES') or die('Access denied');

define('ABSTRACTCLASS', __DIR__.'/abstractClasses/');
define('CLASSES', __DIR__.'/classes/');

//адрес БД
define('HOST', 'localhost');
//пользователь
define('USER', 'root');
//пароль
define('PASS', '');
//база данных
define('DB', 'GoogleImages');
//путь к списку пользовательских API ключей
define('USER_API_KEYS', __DIR__.'/api-keys.txt');
//максимальный вес картинки в Мб
define('MAX_SIZE_IMG', 10);
//максимальная ширина картинки
define('MAX_WIDTH_IMG', 10080);
//максимальная высота картинки
define('MAX_HEIGHT_IMG', 10080);
//абсолютный путь к папке с картинками (ссылка, которая будет возвращать к клиенту)
define('ABSOLUTE_PATH_IMG', '/images/');
//относительный путь к папке (для скрипта), куда будет загружатся картинка
define('DOWNLOAD_DIR_IMG', './images/');