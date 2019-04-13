-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1
-- Время создания: Апр 13 2019 г., 14:48
-- Версия сервера: 5.5.44-0ubuntu0.14.04.1
-- Версия PHP: 5.5.9-1ubuntu4.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `GoogleImages`
--

-- --------------------------------------------------------

--
-- Структура таблицы `GoogleImages`
--

CREATE TABLE IF NOT EXISTS `GoogleImages` (
  `RequestKeyWordsID` int(11) unsigned NOT NULL,
  `OriginalLink` varchar(512) NOT NULL,
  `ImageLink` varchar(255) NOT NULL,
  `LinkPageImg` varchar(512) NOT NULL COMMENT 'Ссылка на страницу с картинкой',
  UNIQUE KEY `ImageLink` (`ImageLink`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `RequestKeyWords`
--

CREATE TABLE IF NOT EXISTS `RequestKeyWords` (
  `RequestID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `KeyWords` varchar(255) NOT NULL,
  `NumLink` int(11) unsigned NOT NULL COMMENT 'количество картинок ссылающийся на данную тему',
  PRIMARY KEY (`RequestID`),
  UNIQUE KEY `KeyWords` (`KeyWords`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
