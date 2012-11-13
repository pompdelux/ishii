ishii
=====

facebook app(s) for pompdelux

Database
=====
Pompdelux tables are prefixed with "gallery_"
--
-- Table structure for table `gallery_galleries`
--

CREATE TABLE IF NOT EXISTS `gallery_galleries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `top_image` VARCHAR( 255 ) NULL DEFAULT NULL,
  `bottom_image` VARCHAR( 255 ) NULL DEFAULT NULL,
  `fangate_image` VARCHAR( 255 ) NULL DEFAULT NULL,
  `uploadform_image` VARCHAR( 255 ) NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `app_id` bigint(20) DEFAULT NULL,
  `secret` varchar(255) DEFAULT NULL,
  `created_date` datetime NOT NULL,
  `updated_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `gallery_pictures`
--

CREATE TABLE IF NOT EXISTS `gallery_pictures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gallery_id` int(11) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `url` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_date` datetime NOT NULL,
  `updated_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gallery_id` (`gallery_id`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `gallery_users`
--

CREATE TABLE IF NOT EXISTS `gallery_users` (
  `uid` bigint(20) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `ip` varchar(15) NOT NULL,
  `is_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `geo_lat` decimal(10,0) DEFAULT NULL,
  `geo_lng` decimal(10,0) DEFAULT NULL,
  `authorized_date` datetime NOT NULL,
  `last_seen_date` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;