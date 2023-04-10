SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for t_article
-- ----------------------------
CREATE TABLE `t_article` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `lang` varchar(3) NOT NULL DEFAULT '' COMMENT 'hidden',
  `user_id` int unsigned DEFAULT NULL COMMENT 'none',
  `title` varchar(128) NOT NULL DEFAULT '',
  `description` varchar(512) NOT NULL DEFAULT '' COMMENT 'textarea',
  `category_id` tinyint unsigned DEFAULT NULL COMMENT 'select',
  `content` longtext NOT NULL COMMENT 'none',
  `alias` varchar(128) NOT NULL DEFAULT '' COMMENT 'none',
  `status` enum('draft','public') NOT NULL DEFAULT 'draft' COMMENT 'none',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'none',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'none',
  `published_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'none',
  PRIMARY KEY (`id`,`lang`),
  UNIQUE KEY `alias` (`alias`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `category_id` (`category_id`) USING BTREE,
  FULLTEXT KEY `title_description` (`title`,`description`),
  CONSTRAINT `t_article_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `t_user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `t_article_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `t_article_category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Table structure for t_article_category
-- ----------------------------
CREATE TABLE `t_article_category` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `parent` tinyint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_article_category_ibfk_1` (`parent`) USING BTREE,
  CONSTRAINT `t_article_category_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `t_article_category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
-- Table structure for t_article_history
-- ----------------------------
CREATE TABLE `t_article_history` (
  `article_id` int unsigned NOT NULL,
  `article_lang` varchar(3) NOT NULL DEFAULT '',
  `article_crc32` bigint NOT NULL,
  `article_content` longtext NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`article_id`,`article_lang`,`article_crc32`),
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `article_id` (`article_id`,`article_lang`) USING BTREE,
  CONSTRAINT `t_article_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `t_user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `t_article_history_ibfk_2` FOREIGN KEY (`article_id`, `article_lang`) REFERENCES `t_article` (`id`, `lang`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;
DROP TRIGGER IF EXISTS `tg_article_insert`;
DELIMITER ;;
CREATE TRIGGER `tg_article_insert` BEFORE INSERT ON `t_article` FOR EACH ROW IF (NEW.alias IS NULL) THEN
SET NEW.alias = CONCAT(NEW.lang, '-', NEW.id);
END IF
;;
DELIMITER ;

-- ----------------------------
-- Records of t_user_permission
-- ----------------------------
INSERT INTO `t_user_permission` VALUES ('article-create', 'Create a new article');
INSERT INTO `t_user_permission` VALUES ('article-delete', 'Delete an article');
INSERT INTO `t_user_permission` VALUES ('article-read', 'Read draft article');
INSERT INTO `t_user_permission` VALUES ('article-status', 'Update article status');
INSERT INTO `t_user_permission` VALUES ('article-update', 'Update an article');

-- ----------------------------
-- Records of t_user_role_has_permission
-- ----------------------------
INSERT INTO `t_user_role_has_permission` VALUES ('super-admin', 'article-create');
INSERT INTO `t_user_role_has_permission` VALUES ('super-admin', 'article-delete');
INSERT INTO `t_user_role_has_permission` VALUES ('super-admin', 'article-read');
INSERT INTO `t_user_role_has_permission` VALUES ('super-admin', 'article-status');
INSERT INTO `t_user_role_has_permission` VALUES ('super-admin', 'article-update');

-- ----------------------------
-- Records of t_page
-- ----------------------------
INSERT INTO `t_page` (id, title) VALUES ('article', 'Article');
INSERT INTO `t_page` (id, title) VALUES ('articles', 'Articles');

-- ----------------------------
-- View structure for v_article
-- ----------------------------
DROP VIEW IF EXISTS `v_article`;
CREATE VIEW `v_article` AS select `t_article`.`id` AS `id`,`t_article`.`lang` AS `lang`,`t_article`.`user_id` AS `user_id`,`t_article`.`title` AS `title`,`t_article`.`description` AS `description`,`t_article`.`category_id` AS `category_id`,`t_article`.`content` AS `content`,`t_article`.`alias` AS `alias`,`t_article`.`status` AS `status`,`t_article`.`created_at` AS `created_at`,`t_article`.`updated_at` AS `updated_at`,`t_article`.`published_at` AS `published_at`,`user`.`email` AS `user_email`,`user`.`firstname` AS `user_firstname`,`user`.`lastname` AS `user_lastname`,`category`.`name` AS `category` from ((`t_article` left join `t_user` `user` on((`t_article`.`user_id` = `user`.`id`))) left join `t_article_category` `category` on((`t_article`.`category_id` = `category`.`id`))) ;

SET FOREIGN_KEY_CHECKS=1;
