-- phpMyAdmin SQL Dump
-- version 4.2.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 15, 2014 at 02:24 PM
-- Server version: 5.5.37-log
-- PHP Version: 5.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `innothing`
--

-- --------------------------------------------------------

--
-- Table structure for table `datapoint`
--

CREATE TABLE IF NOT EXISTS `datapoint` (
`dat_id` bigint(19) unsigned NOT NULL,
  `sen_id` int(10) unsigned NOT NULL,
  `dat_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dat_value` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `dat_type` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `dat_created_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dat_modified_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `datapoint`
--

INSERT INTO `datapoint` (`dat_id`, `sen_id`, `dat_time`, `dat_value`, `dat_type`, `dat_created_time`, `dat_modified_time`) VALUES
(4, 14, '2012-03-15 08:13:14', '294.34', 'value', '2014-01-06 14:59:25', '2014-01-06 14:59:25'),
(5, 14, '2012-03-15 08:13:14', '9994.34', 'value', '2014-01-06 15:00:04', '2014-01-15 14:30:07'),
(6, 14, '2012-03-15 08:13:14', '94.34', 'value', '2014-01-15 14:24:19', '2014-01-15 14:29:40');

--
-- Triggers `datapoint`
--
DELIMITER //
CREATE TRIGGER `datapoint_ADEL` AFTER DELETE ON `datapoint`
 FOR EACH ROW -- Edit trigger body code below this line. Do not edit lines above this one
update datapoint_all set dat_deleted_time = current_TIMESTAMP, dat_del = 1
where datapoint_all.dat_id = OLD.dat_id
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `datapoint_all`
--

CREATE TABLE IF NOT EXISTS `datapoint_all` (
  `dat_id` bigint(20) NOT NULL,
  `sen_id` int(11) NOT NULL,
  `dat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dat_value` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `dat_type` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `dat_created_time` timestamp NULL DEFAULT NULL,
  `dat_modified_time` timestamp NULL DEFAULT NULL,
  `dat_deleted_time` timestamp NULL DEFAULT NULL,
  `dat_del` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device`
--

CREATE TABLE IF NOT EXISTS `device` (
`dev_id` int(10) unsigned NOT NULL,
  `dev_name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `dev_sn` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `dev_desc` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dev_lat` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dev_lon` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dev_created_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dev_modified_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `grp_id` int(10) unsigned NOT NULL,
  `dev_ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Dumping data for table `device`
--

INSERT INTO `device` (`dev_id`, `dev_name`, `dev_sn`, `dev_desc`, `dev_lat`, `dev_lon`, `dev_created_time`, `dev_modified_time`, `grp_id`, `dev_ip`) VALUES
(4, 'test1', 'test_sn_ssssss', 'no desc test 1', '0.444', '0.555', '2014-01-06 13:34:36', '2014-01-06 13:34:36', 1, NULL);

--
-- Triggers `device`
--
DELIMITER //
CREATE TRIGGER `device_ADEL` AFTER DELETE ON `device`
 FOR EACH ROW -- Edit trigger body code below this line. Do not edit lines above this one
update device_all set dev_deleted_time = current_TIMESTAMP, dev_del = 1
where device_all.dev_id = OLD.dev_id
//
DELIMITER ;
DELIMITER //
CREATE TRIGGER `device_BDEL` BEFORE DELETE ON `device`
 FOR EACH ROW -- Edit trigger body code below this line. Do not edit lines above this one
delete from sensor where dev_id = OLD.dev_id
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `device_all`
--

CREATE TABLE IF NOT EXISTS `device_all` (
  `dev_id` int(11) NOT NULL,
  `usr_id` int(11) NOT NULL,
  `dev_name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dev_sn` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dev_desc` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dev_lat` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dev_lon` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dev_created_time` timestamp NULL DEFAULT NULL,
  `dev_modified_time` timestamp NULL DEFAULT NULL,
  `dev_deleted_time` timestamp NULL DEFAULT NULL,
  `dev_del` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device_has_setting`
--

CREATE TABLE IF NOT EXISTS `device_has_setting` (
  `dev_id` int(10) unsigned NOT NULL,
  `set_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_device`
--

CREATE TABLE IF NOT EXISTS `personal_device` (
`pdev_id` int(10) unsigned NOT NULL,
  `pdev_imei` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `pdev_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pdev_modified_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pdev_name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `usr_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sensor`
--

CREATE TABLE IF NOT EXISTS `sensor` (
`sen_id` int(10) unsigned NOT NULL,
  `dev_id` int(10) unsigned NOT NULL,
  `sen_name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `sen_desc` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sen_unit` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sen_unit_symbol` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sen_created_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sen_modified_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sen_ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15 ;

--
-- Dumping data for table `sensor`
--

INSERT INTO `sensor` (`sen_id`, `dev_id`, `sen_name`, `sen_desc`, `sen_unit`, `sen_unit_symbol`, `sen_created_time`, `sen_modified_time`, `sen_ip`) VALUES
(14, 4, 'sensor1', 'test sensor', 'Temp', 'C', '2014-01-06 14:54:12', '2014-01-06 14:54:12', NULL);

--
-- Triggers `sensor`
--
DELIMITER //
CREATE TRIGGER `sensor_ADEL` AFTER DELETE ON `sensor`
 FOR EACH ROW -- Edit trigger body code below this line. Do not edit lines above this one
update sensor_all set sen_deleted_time = current_TIMESTAMP, sen_del = 1
where sensor_all.sen_id = OLD.sen_id
//
DELIMITER ;
DELIMITER //
CREATE TRIGGER `sensor_AUPD` AFTER UPDATE ON `sensor`
 FOR EACH ROW -- Edit trigger body code below this line. Do not edit lines above this one
update sensor_all set 
sen_id = new.sen_id, 
dev_id = new.dev_id,
sen_name = new.sen_name, 
sen_desc = new.sen_desc, 
sen_unit = new.sen_unit, 
sen_unit_symbol = new.sen_unit_symbol, 
sen_created_time = new.sen_created_time, 
sen_modified_time = new.sen_modified_time
where sen_id = OLD.sen_id
//
DELIMITER ;
DELIMITER //
CREATE TRIGGER `sensor_BDEL` BEFORE DELETE ON `sensor`
 FOR EACH ROW -- Edit trigger body code below this line. Do not edit lines above this one
delete from datapoint where sen_id = OLD.sen_id
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `sensor_all`
--

CREATE TABLE IF NOT EXISTS `sensor_all` (
  `sen_id` int(11) NOT NULL,
  `dev_id` int(11) NOT NULL,
  `sen_name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `sen_desc` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sen_unit` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sen_unit_symbol` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sen_created_time` timestamp NULL DEFAULT NULL,
  `sen_modified_time` timestamp NULL DEFAULT NULL,
  `sen_deleted_time` timestamp NULL DEFAULT NULL,
  `sen_del` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sensor_has_setting`
--

CREATE TABLE IF NOT EXISTS `sensor_has_setting` (
  `sen_id` int(10) unsigned NOT NULL,
  `set_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `setting`
--

CREATE TABLE IF NOT EXISTS `setting` (
`set_id` int(10) unsigned NOT NULL,
  `set_parameter` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `set_value` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `set_created_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `set_modified_time` timestamp NULL DEFAULT NULL,
  `styp_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `setting_type`
--

CREATE TABLE IF NOT EXISTS `setting_type` (
`styp_id` int(10) unsigned NOT NULL,
  `styp_name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `styp_created_tiime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `styp_modified_time` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
`usr_id` int(10) unsigned NOT NULL,
  `usr_name` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `usr_pwd` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `usr_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `usr_key` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `usr_created_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `usr_modified_time` timestamp NULL DEFAULT NULL,
  `usr_tel` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `utyp_id` int(10) unsigned NOT NULL,
  `usr_weixin` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `usr_weibo` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`usr_id`, `usr_name`, `usr_pwd`, `usr_email`, `usr_key`, `usr_created_time`, `usr_modified_time`, `usr_tel`, `utyp_id`, `usr_weixin`, `usr_weibo`) VALUES
(1, 'test1', 'test1', 'test@test', '49a98af413554b98775a3a30931da4fd', '2014-01-06 13:02:45', '2014-01-06 13:02:45', NULL, 1, NULL, NULL),
(2, 'test2', 'test1', 'test@test', '21ffdcd90f29ab3ba346cb476e5d5507', '2014-01-06 13:03:24', '2014-01-06 13:03:24', NULL, 1, NULL, NULL);

--
-- Triggers `user`
--
DELIMITER //
CREATE TRIGGER `user_ADEL` AFTER DELETE ON `user`
 FOR EACH ROW -- Edit trigger body code below this line. Do not edit lines above this one
update user_all set usr_deleted_time = current_TIMESTAMP, usr_del = 1
where user_all.usr_id = OLD.usr_id
//
DELIMITER ;
DELIMITER //
CREATE TRIGGER `user_AINS` AFTER INSERT ON `user`
 FOR EACH ROW begin
-- Edit trigger body code below this line. Do not edit lines above this one
-- update the user_all table at the same time.
insert into user_all (usr_id, usr_name, usr_pwd, usr_email, usr_key, usr_created_time, usr_modified_time)
	select usr_id, usr_name, usr_pwd, usr_email, usr_key, usr_created_time, usr_modified_time
	from user where user.usr_id = NEW.usr_id;
-- this is the creating the normal user, so create the group at the same time
-- If it is creating guest, then no group is created for this user.
set @guest_type = (select utyp_id from user_type where utyp_name = 'normal');
	IF new.utyp_id = @guest_type then
		insert into user_group(usr_id, grp_name) values (NEW.usr_id, NEW.usr_name);
	END IF;
END
//
DELIMITER ;
DELIMITER //
CREATE TRIGGER `user_AUPD` AFTER UPDATE ON `user`
 FOR EACH ROW -- Edit trigger body code below this line. Do not edit lines above this one
update user_all set
usr_id = new.usr_id, 
usr_name = new.usr_name, 
usr_pwd = new.usr_pwd, 
usr_email = new.usr_email, 
usr_key = new.usr_key, 
usr_created_time = new.usr_created_time, 
usr_modified_time = new.usr_modified_time
where usr_id = OLD.usr_id
//
DELIMITER ;
DELIMITER //
CREATE TRIGGER `user_BDEL` BEFORE DELETE ON `user`
 FOR EACH ROW -- Edit trigger body code below this line. Do not edit lines above this one
delete from device where usr_id = OLD.usr_id
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_all`
--

CREATE TABLE IF NOT EXISTS `user_all` (
  `usr_id` int(11) NOT NULL,
  `usr_name` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `usr_pwd` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `usr_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `usr_key` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `usr_created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usr_modified_time` timestamp NULL DEFAULT NULL,
  `usr_deleted_time` timestamp NULL DEFAULT NULL,
  `usr_del` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user_all`
--

INSERT INTO `user_all` (`usr_id`, `usr_name`, `usr_pwd`, `usr_email`, `usr_key`, `usr_created_time`, `usr_modified_time`, `usr_deleted_time`, `usr_del`) VALUES
(1, 'test1', 'test1', 'test@test', '49a98af413554b98775a3a30931da4fd', '2014-01-06 13:02:45', '2014-01-06 13:02:45', NULL, 0),
(2, 'test2', 'test1', 'test@test', '21ffdcd90f29ab3ba346cb476e5d5507', '2014-01-06 13:03:24', '2014-01-06 13:03:24', NULL, 0);

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_datapoint`
--
CREATE TABLE IF NOT EXISTS `user_datapoint` (
`dat_id` bigint(19) unsigned
,`sen_id` int(10) unsigned
,`dat_time` timestamp
,`dat_value` mediumtext
,`dat_type` varchar(45)
,`dat_created_time` timestamp
,`dat_modified_time` timestamp
,`dev_id` int(10) unsigned
,`usr_id` int(10) unsigned
,`grp_id` int(10) unsigned
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `user_device`
--
CREATE TABLE IF NOT EXISTS `user_device` (
`usr_id` int(10) unsigned
,`usr_name` varchar(16)
,`dev_id` int(10) unsigned
,`dev_name` varchar(45)
,`dev_sn` varchar(45)
,`dev_desc` varchar(45)
,`dev_lat` varchar(45)
,`dev_lon` varchar(45)
,`dev_created_time` timestamp
,`dev_modified_time` timestamp
,`grp_id` int(10) unsigned
,`dev_ip` varchar(45)
);
-- --------------------------------------------------------

--
-- Table structure for table `user_group`
--

CREATE TABLE IF NOT EXISTS `user_group` (
`grp_id` int(10) unsigned NOT NULL,
  `grp_created_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `grp_modified_time` timestamp NULL DEFAULT NULL,
  `usr_id` int(10) unsigned NOT NULL,
  `grp_name` varchar(45) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `user_group`
--

INSERT INTO `user_group` (`grp_id`, `grp_created_time`, `grp_modified_time`, `usr_id`, `grp_name`) VALUES
(1, '2014-01-06 13:02:45', '2014-01-06 13:02:45', 1, 'test1'),
(2, '2014-01-06 13:03:24', '2014-01-06 13:03:24', 2, 'test2');

--
-- Triggers `user_group`
--
DELIMITER //
CREATE TRIGGER `user_group_AINS` AFTER INSERT ON `user_group`
 FOR EACH ROW -- Edit trigger body code below this line. Do not edit lines above this one
insert into user_group_has_user(usr_id, grp_id) values (NEW.usr_id, NEW.grp_id)
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_group_has_user`
--

CREATE TABLE IF NOT EXISTS `user_group_has_user` (
  `grp_id` int(10) unsigned NOT NULL,
  `usr_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user_group_has_user`
--

INSERT INTO `user_group_has_user` (`grp_id`, `usr_id`) VALUES
(1, 1),
(1, 2),
(2, 2);

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_sensor`
--
CREATE TABLE IF NOT EXISTS `user_sensor` (
`sen_id` int(10) unsigned
,`dev_id` int(10) unsigned
,`sen_name` varchar(45)
,`sen_desc` varchar(45)
,`sen_unit` varchar(45)
,`sen_unit_symbol` varchar(45)
,`sen_created_time` timestamp
,`sen_modified_time` timestamp
,`sen_ip` varchar(45)
,`usr_id` int(10) unsigned
,`grp_id` int(10) unsigned
);
-- --------------------------------------------------------

--
-- Table structure for table `user_type`
--

CREATE TABLE IF NOT EXISTS `user_type` (
`utyp_id` int(10) unsigned NOT NULL,
  `utyp_created_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `utyp_modified_time` timestamp NULL DEFAULT NULL,
  `utyp_name` varchar(45) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `user_type`
--

INSERT INTO `user_type` (`utyp_id`, `utyp_created_time`, `utyp_modified_time`, `utyp_name`) VALUES
(1, '2013-12-31 16:00:00', '2013-12-31 16:00:00', 'normal'),
(2, '2013-12-31 16:00:00', '2013-12-31 16:00:00', 'guest');

-- --------------------------------------------------------

--
-- Structure for view `user_datapoint`
--
DROP TABLE IF EXISTS `user_datapoint`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_datapoint` AS select `datapoint`.`dat_id` AS `dat_id`,`datapoint`.`sen_id` AS `sen_id`,`datapoint`.`dat_time` AS `dat_time`,`datapoint`.`dat_value` AS `dat_value`,`datapoint`.`dat_type` AS `dat_type`,`datapoint`.`dat_created_time` AS `dat_created_time`,`datapoint`.`dat_modified_time` AS `dat_modified_time`,`user_sensor`.`dev_id` AS `dev_id`,`user_sensor`.`usr_id` AS `usr_id`,`user_sensor`.`grp_id` AS `grp_id` from (`datapoint` join `user_sensor`) where (`datapoint`.`sen_id` = `user_sensor`.`sen_id`);

-- --------------------------------------------------------

--
-- Structure for view `user_device`
--
DROP TABLE IF EXISTS `user_device`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_device` AS select `user`.`usr_id` AS `usr_id`,`user`.`usr_name` AS `usr_name`,`device`.`dev_id` AS `dev_id`,`device`.`dev_name` AS `dev_name`,`device`.`dev_sn` AS `dev_sn`,`device`.`dev_desc` AS `dev_desc`,`device`.`dev_lat` AS `dev_lat`,`device`.`dev_lon` AS `dev_lon`,`device`.`dev_created_time` AS `dev_created_time`,`device`.`dev_modified_time` AS `dev_modified_time`,`device`.`grp_id` AS `grp_id`,`device`.`dev_ip` AS `dev_ip` from (((`user` join `user_group_has_user`) join `user_group`) join `device`) where ((`user`.`usr_id` = `user_group_has_user`.`usr_id`) and (`user_group_has_user`.`grp_id` = `user_group`.`grp_id`) and (`user_group`.`grp_id` = `device`.`grp_id`));

-- --------------------------------------------------------

--
-- Structure for view `user_sensor`
--
DROP TABLE IF EXISTS `user_sensor`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_sensor` AS select `sensor`.`sen_id` AS `sen_id`,`sensor`.`dev_id` AS `dev_id`,`sensor`.`sen_name` AS `sen_name`,`sensor`.`sen_desc` AS `sen_desc`,`sensor`.`sen_unit` AS `sen_unit`,`sensor`.`sen_unit_symbol` AS `sen_unit_symbol`,`sensor`.`sen_created_time` AS `sen_created_time`,`sensor`.`sen_modified_time` AS `sen_modified_time`,`sensor`.`sen_ip` AS `sen_ip`,`user_device`.`usr_id` AS `usr_id`,`user_device`.`grp_id` AS `grp_id` from (`sensor` join `user_device`) where (`sensor`.`dev_id` = `user_device`.`dev_id`);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `datapoint`
--
ALTER TABLE `datapoint`
 ADD PRIMARY KEY (`dat_id`), ADD UNIQUE KEY `dat_id_UNIQUE` (`dat_id`), ADD KEY `fk_datapoints_sensors1_idx` (`sen_id`);

--
-- Indexes for table `datapoint_all`
--
ALTER TABLE `datapoint_all`
 ADD PRIMARY KEY (`dat_id`), ADD UNIQUE KEY `dat_id_UNIQUE` (`dat_id`), ADD KEY `fk_datapoint_del_sensor_del1_idx` (`sen_id`);

--
-- Indexes for table `device`
--
ALTER TABLE `device`
 ADD PRIMARY KEY (`dev_id`), ADD UNIQUE KEY `device_id_UNIQUE` (`dev_id`), ADD UNIQUE KEY `dev_sn_UNIQUE` (`dev_sn`), ADD KEY `fk_device_usergroup1_idx` (`grp_id`);

--
-- Indexes for table `device_all`
--
ALTER TABLE `device_all`
 ADD PRIMARY KEY (`dev_id`), ADD UNIQUE KEY `device_id_UNIQUE` (`dev_id`), ADD KEY `fk_device_all_user_all1_idx` (`usr_id`);

--
-- Indexes for table `device_has_setting`
--
ALTER TABLE `device_has_setting`
 ADD PRIMARY KEY (`dev_id`,`set_id`), ADD KEY `fk_device_has_setting_setting1_idx` (`set_id`), ADD KEY `fk_device_has_setting_device1_idx` (`dev_id`);

--
-- Indexes for table `personal_device`
--
ALTER TABLE `personal_device`
 ADD PRIMARY KEY (`pdev_id`), ADD UNIQUE KEY `pdev_id_UNIQUE` (`pdev_id`), ADD KEY `fk_personal_device_user1_idx` (`usr_id`);

--
-- Indexes for table `sensor`
--
ALTER TABLE `sensor`
 ADD PRIMARY KEY (`sen_id`), ADD UNIQUE KEY `sen_id_UNIQUE` (`sen_id`), ADD KEY `fk_sensors_devices1_idx` (`dev_id`);

--
-- Indexes for table `sensor_all`
--
ALTER TABLE `sensor_all`
 ADD PRIMARY KEY (`sen_id`), ADD UNIQUE KEY `sen_id_UNIQUE` (`sen_id`), ADD KEY `fk_sensor_del_device_del1_idx` (`dev_id`);

--
-- Indexes for table `sensor_has_setting`
--
ALTER TABLE `sensor_has_setting`
 ADD PRIMARY KEY (`sen_id`,`set_id`), ADD KEY `fk_sensor_has_setting_setting1_idx` (`set_id`), ADD KEY `fk_sensor_has_setting_sensor1_idx` (`sen_id`);

--
-- Indexes for table `setting`
--
ALTER TABLE `setting`
 ADD PRIMARY KEY (`set_id`), ADD UNIQUE KEY `set_id_UNIQUE` (`set_id`), ADD KEY `fk_setting_setting_type1_idx` (`styp_id`);

--
-- Indexes for table `setting_type`
--
ALTER TABLE `setting_type`
 ADD PRIMARY KEY (`styp_id`), ADD UNIQUE KEY `styp_id_UNIQUE` (`styp_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
 ADD PRIMARY KEY (`usr_id`), ADD UNIQUE KEY `api_key_UNIQUE` (`usr_key`), ADD UNIQUE KEY `user_id_UNIQUE` (`usr_id`), ADD UNIQUE KEY `usr_name_UNIQUE` (`usr_name`), ADD KEY `fk_user_user_type1_idx` (`utyp_id`);

--
-- Indexes for table `user_all`
--
ALTER TABLE `user_all`
 ADD PRIMARY KEY (`usr_id`), ADD UNIQUE KEY `user_id_UNIQUE` (`usr_id`);

--
-- Indexes for table `user_group`
--
ALTER TABLE `user_group`
 ADD PRIMARY KEY (`grp_id`), ADD UNIQUE KEY `grp_id_UNIQUE` (`grp_id`), ADD KEY `fk_user_group_user1_idx` (`usr_id`);

--
-- Indexes for table `user_group_has_user`
--
ALTER TABLE `user_group_has_user`
 ADD PRIMARY KEY (`grp_id`,`usr_id`), ADD KEY `fk_user_group_has_user_user1_idx` (`usr_id`), ADD KEY `fk_user_group_has_user_user_group1_idx` (`grp_id`);

--
-- Indexes for table `user_type`
--
ALTER TABLE `user_type`
 ADD PRIMARY KEY (`utyp_id`), ADD UNIQUE KEY `utyp_id_UNIQUE` (`utyp_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `datapoint`
--
ALTER TABLE `datapoint`
MODIFY `dat_id` bigint(19) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `device`
--
ALTER TABLE `device`
MODIFY `dev_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `personal_device`
--
ALTER TABLE `personal_device`
MODIFY `pdev_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sensor`
--
ALTER TABLE `sensor`
MODIFY `sen_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `setting`
--
ALTER TABLE `setting`
MODIFY `set_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `setting_type`
--
ALTER TABLE `setting_type`
MODIFY `styp_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
MODIFY `usr_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `user_group`
--
ALTER TABLE `user_group`
MODIFY `grp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `user_type`
--
ALTER TABLE `user_type`
MODIFY `utyp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `datapoint`
--
ALTER TABLE `datapoint`
ADD CONSTRAINT `fk_datapoints_sensors` FOREIGN KEY (`sen_id`) REFERENCES `sensor` (`sen_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `datapoint_all`
--
ALTER TABLE `datapoint_all`
ADD CONSTRAINT `fk_datapoint_all_sensor_all` FOREIGN KEY (`sen_id`) REFERENCES `sensor_all` (`sen_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `device`
--
ALTER TABLE `device`
ADD CONSTRAINT `fk_device_usergroup1` FOREIGN KEY (`grp_id`) REFERENCES `user_group` (`grp_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `device_all`
--
ALTER TABLE `device_all`
ADD CONSTRAINT `fk_device_all_user_all` FOREIGN KEY (`usr_id`) REFERENCES `user_all` (`usr_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `device_has_setting`
--
ALTER TABLE `device_has_setting`
ADD CONSTRAINT `fk_device_has_setting_device1` FOREIGN KEY (`dev_id`) REFERENCES `device` (`dev_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_device_has_setting_setting1` FOREIGN KEY (`set_id`) REFERENCES `setting` (`set_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `personal_device`
--
ALTER TABLE `personal_device`
ADD CONSTRAINT `fk_personal_device_user1` FOREIGN KEY (`usr_id`) REFERENCES `user` (`usr_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `sensor`
--
ALTER TABLE `sensor`
ADD CONSTRAINT `fk_sensors_devices` FOREIGN KEY (`dev_id`) REFERENCES `device` (`dev_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `sensor_all`
--
ALTER TABLE `sensor_all`
ADD CONSTRAINT `fk_sensor_all_device_all` FOREIGN KEY (`dev_id`) REFERENCES `device_all` (`dev_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `sensor_has_setting`
--
ALTER TABLE `sensor_has_setting`
ADD CONSTRAINT `fk_sensor_has_setting_sensor1` FOREIGN KEY (`sen_id`) REFERENCES `sensor` (`sen_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_sensor_has_setting_setting1` FOREIGN KEY (`set_id`) REFERENCES `setting` (`set_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `setting`
--
ALTER TABLE `setting`
ADD CONSTRAINT `fk_setting_setting_type1` FOREIGN KEY (`styp_id`) REFERENCES `setting_type` (`styp_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
ADD CONSTRAINT `fk_user_user_type1` FOREIGN KEY (`utyp_id`) REFERENCES `user_type` (`utyp_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `user_group`
--
ALTER TABLE `user_group`
ADD CONSTRAINT `fk_user_group_user1` FOREIGN KEY (`usr_id`) REFERENCES `user` (`usr_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `user_group_has_user`
--
ALTER TABLE `user_group_has_user`
ADD CONSTRAINT `fk_user_group_has_user_user1` FOREIGN KEY (`usr_id`) REFERENCES `user` (`usr_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_user_group_has_user_user_group1` FOREIGN KEY (`grp_id`) REFERENCES `user_group` (`grp_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
