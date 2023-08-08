-- ----------------------------
-- Records of t_page
-- ----------------------------
INSERT IGNORE INTO `t_page` (id, title) VALUES ('article', 'Article');
INSERT IGNORE INTO `t_page` (id, title) VALUES ('articles', 'Articles');

-- ----------------------------
-- Records of t_user_permission
-- ----------------------------
INSERT IGNORE INTO `t_user_permission` VALUES ('article-create', 'Create a new article');
INSERT IGNORE INTO `t_user_permission` VALUES ('article-delete', 'Delete an article');
INSERT IGNORE INTO `t_user_permission` VALUES ('article-read', 'Read draft article');
INSERT IGNORE INTO `t_user_permission` VALUES ('article-status', 'Update article status');
INSERT IGNORE INTO `t_user_permission` VALUES ('article-update', 'Update an article');

-- ----------------------------
-- Records of t_user_role
-- ----------------------------
INSERT IGNORE INTO `t_user_role` VALUES ('article-admin', 'Article administrator');

-- ----------------------------
-- Records of t_user_role_has_permission
-- ----------------------------
INSERT IGNORE INTO `t_user_role_has_permission` VALUES ('article-admin', 'article-create');
INSERT IGNORE INTO `t_user_role_has_permission` VALUES ('article-admin', 'article-read');
INSERT IGNORE INTO `t_user_role_has_permission` VALUES ('article-admin', 'article-update');
INSERT IGNORE INTO `t_user_role_has_permission` VALUES ('article-admin', 'article-delete');
INSERT IGNORE INTO `t_user_role_has_permission` VALUES ('article-admin', 'article-status');

INSERT IGNORE INTO `t_user_role_has_permission` VALUES ('admin', 'article-create');
INSERT IGNORE INTO `t_user_role_has_permission` VALUES ('admin', 'article-read');
INSERT IGNORE INTO `t_user_role_has_permission` VALUES ('admin', 'article-update');
INSERT IGNORE INTO `t_user_role_has_permission` VALUES ('admin', 'article-delete');
INSERT IGNORE INTO `t_user_role_has_permission` VALUES ('admin', 'article-status');