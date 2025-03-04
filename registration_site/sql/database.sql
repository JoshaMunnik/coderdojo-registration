-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 02, 2025 at 11:02 AM
-- Server version: 11.2.3-MariaDB
-- PHP Version: 8.2.17

SET
SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET
time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Table structure for table `cd_events`
--

CREATE TABLE `cd_events`
(
  `id`               binary(16) NOT NULL,
  `created`          datetime NOT NULL,
  `modified`         datetime NOT NULL,
  `signup_date`      datetime NOT NULL,
  `event_date`       datetime NOT NULL,
  `participant_type` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `cd_event_workshops`
--

CREATE TABLE `cd_event_workshops`
(
  `id`          binary(16) NOT NULL,
  `created`     datetime NOT NULL,
  `event_id`    binary(16) NOT NULL,
  `workshop_id` binary(16) NOT NULL,
  `place_count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cd_languages`
--

CREATE TABLE `cd_languages`
(
  `id`   int(11) NOT NULL,
  `name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cd_languages`
--

INSERT INTO `cd_languages` (`id`, `name`)
VALUES (1, 'English'),
  (2, 'Dutch');

-- --------------------------------------------------------

--
-- Table structure for table `cd_participants`
--

CREATE TABLE `cd_participants`
(
  `id`                           binary(16) NOT NULL,
  `created`                      datetime NOT NULL,
  `modified`                     datetime NOT NULL,
  `user_id`                      binary(16) DEFAULT NULL,
  `event_id`                     binary(16) NOT NULL,
  `name`                         varchar(128) DEFAULT NULL,
  `event_workshop_1_id`          binary(16) DEFAULT NULL,
  `event_workshop_1_join_date`   datetime     DEFAULT NULL,
  `event_workshop_1_notify_date` datetime     DEFAULT NULL,
  `event_workshop_2_id`          binary(16) DEFAULT NULL,
  `event_workshop_2_join_date`   datetime     DEFAULT NULL,
  `event_workshop_2_notify_date` datetime     DEFAULT NULL,
  `can_leave`                    tinyint(1) NOT NULL DEFAULT 0,
  `has_laptop`                   tinyint(1) NOT NULL DEFAULT 0,
  `checkin_date`                 datetime     DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `cd_participants`
--
DELIMITER
$$
CREATE TRIGGER `clear_name_on_null_user`
  BEFORE UPDATE
  ON `cd_participants`
  FOR EACH ROW
BEGIN
  IF NEW.user_id IS NULL THEN
        SET NEW.name = '';
END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cd_participant_types`
--

CREATE TABLE `cd_participant_types`
(
  `id`   int(11) NOT NULL,
  `name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cd_participant_types`
--

INSERT INTO `cd_participant_types` (`id`, `name`)
VALUES (1, 'Children'),
  (2, 'All');

-- --------------------------------------------------------

--
-- Table structure for table `cd_users`
--

CREATE TABLE `cd_users`
(
  `id`                   binary(16) NOT NULL,
  `created`              datetime     NOT NULL,
  `modified`             datetime     NOT NULL,
  `email`                varchar(511) NOT NULL,
  `password`             varchar(100) NOT NULL,
  `password_date`        datetime     NOT NULL,
  `name`                 varchar(128) NOT NULL,
  `phone`                varchar(32) DEFAULT NULL,
  `administrator`        tinyint(1) NOT NULL,
  `password_reset_date`  datetime    DEFAULT NULL,
  `password_reset_token` datetime    DEFAULT NULL,
  `last_visit_date`      datetime    DEFAULT NULL,
  `mailing_list`         tinyint(1) NOT NULL DEFAULT 0,
  `language_id`          int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cd_workshops`
--

CREATE TABLE `cd_workshops`
(
  `id`       binary(16) NOT NULL,
  `created`  datetime NOT NULL,
  `modified` datetime NOT NULL,
  `laptop`   tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cd_workshop_texts`
--

CREATE TABLE `cd_workshop_texts`
(
  `id`          binary(16) NOT NULL,
  `created`     datetime     NOT NULL,
  `modified`    datetime     NOT NULL,
  `workshop_id` binary(16) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name`        varchar(511) NOT NULL DEFAULT '',
  `description` text         NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cd_absent_participants`
--

CREATE TABLE `cd_absent_participants`
(
  `id`       binary(16) NOT NULL,
  `created`  datetime NOT NULL,
  `user_id`  binary(16) NOT NULL,
  `event_id` binary(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Indexes for dumped tables
--

--
-- Indexes for table `cd_events`
--
ALTER TABLE `cd_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coderdojo_event_participant` (`participant_type`);

--
-- Indexes for table `cd_event_workshops`
--
ALTER TABLE `cd_event_workshops`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_event_workshop` (`event_id`,`workshop_id`),
  ADD KEY `coderdojo_event_workshop_workshop` (`workshop_id`);

--
-- Indexes for table `cd_languages`
--
ALTER TABLE `cd_languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cd_participants`
--
ALTER TABLE `cd_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `participant_event` (`event_id`),
  ADD KEY `participant_user` (`user_id`),
  ADD KEY `participant_workshop_1` (`event_workshop_1_id`),
  ADD KEY `participant_workshop_2` (`event_workshop_2_id`);

--
-- Indexes for table `cd_participant_types`
--
ALTER TABLE `cd_participant_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cd_users`
--
ALTER TABLE `cd_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `user_language` (`language_id`);

--
-- Indexes for table `cd_workshops`
--
ALTER TABLE `cd_workshops`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cd_workshop_texts`
--
ALTER TABLE `cd_workshop_texts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `workshop_text_language` (`language_id`),
  ADD KEY `workshop_text_workshop` (`workshop_id`);

--
-- Indexes for table `cd_absent_participants`
--
ALTER TABLE `cd_absent_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_event` (`user_id`,`event_id`),
  ADD KEY `absent_participant_event` (`event_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cd_events`
--
ALTER TABLE `cd_events`
  ADD CONSTRAINT `coderdojo_event_participant` FOREIGN KEY (`participant_type`) REFERENCES `cd_participant_types` (`id`);

--
-- Constraints for table `cd_event_workshops`
--
ALTER TABLE `cd_event_workshops`
  ADD CONSTRAINT `coderdojo_event_workshop_event` FOREIGN KEY (`event_id`) REFERENCES `cd_events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coderdojo_event_workshop_workshop` FOREIGN KEY (`workshop_id`) REFERENCES `cd_workshops` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cd_participants`
--
ALTER TABLE `cd_participants`
  ADD CONSTRAINT `participant_event` FOREIGN KEY (`event_id`) REFERENCES `cd_events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participant_user` FOREIGN KEY (`user_id`) REFERENCES `cd_users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `participant_workshop_1` FOREIGN KEY (`event_workshop_1_id`) REFERENCES `cd_event_workshops` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `participant_workshop_2` FOREIGN KEY (`event_workshop_2_id`) REFERENCES `cd_event_workshops` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cd_users`
--
ALTER TABLE `cd_users`
  ADD CONSTRAINT `user_language` FOREIGN KEY (`language_id`) REFERENCES `cd_languages` (`id`);

--
-- Constraints for table `cd_workshop_texts`
--
ALTER TABLE `cd_workshop_texts`
  ADD CONSTRAINT `workshop_text_language` FOREIGN KEY (`language_id`) REFERENCES `cd_languages` (`id`),
  ADD CONSTRAINT `workshop_text_workshop` FOREIGN KEY (`workshop_id`) REFERENCES `cd_workshops` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cd_absent_participants`
--
ALTER TABLE `cd_absent_participants`
  ADD CONSTRAINT `absent_participant_event` FOREIGN KEY (`event_id`) REFERENCES `cd_events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `absent_participant_user` FOREIGN KEY (`user_id`) REFERENCES `cd_users` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
