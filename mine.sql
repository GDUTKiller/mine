--
-- 表的结构 `user`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mobile` char(20) NOT NULL DEFAULT '',
  `password` char(32) NOT NULL DEFAULT '',
  `pid` int(11) unsigned NOT NULL DEFAULT 0,
  `name` char(20) NOT NULL DEFAULT '',
  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `salt` varchar(10) NOT NULL DEFAULT '' COMMENT '盐,保护用户密码安全',
  `avatar` varchar(100) NOT NULL DEFAULT '' COMMENT '用户头像',
  `sex` varchar(6),
  `birthday` datetime,
  `recommend_code` char(8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile` (`mobile`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- 表的结构 `captchas`
--

CREATE TABLE IF NOT EXISTS `captchas` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号码',
  `captcha` varchar(10) NOT NULL DEFAULT '' COMMENT '验证码',
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '验证码有效期',
  `status` tinyint(4) NOT NULL,
  `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile` (`mobile`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- 表的结构 `minecars`
--

CCREATE TABLE IF NOT EXISTS `minecars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '矿车类型',
  `buy_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '购买日期',
  `mine_count` int(11) NOT NULL DEFAULT 0 COMMENT '挖矿数量',
  `update_time` timestamp COMMENT '用户查询矿车挖矿情况的时间',
  `speed` int(11) NOT NULL DEFAULT 0 COMMENT '矿车挖矿速度',
  `init_count` int(11) NOT NULL DEFAULT 0 COMMENT '矿车初始挖矿数量，用户获得转赠的矿车会有初始数量',
  `stage` tinyint(4) NOT NULL DEFAULT 0 COMMENT '矿车阶段',
  PRIMARY KEY (`id`),
  FOREIGN KEY(`uid`) REFERENCES `users`(id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf-8;

--
-- 动态表
--

CREATE TABLE  IF NOT EXISTS `arts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `camp_id` int(11) unsigned NOT NULL COMMENT '所属营地id',
  `user_id` int(11) unsigned NOT NULL ,
  `content` varchar(200) NOT NULL,
  `pubtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comm` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT '评论数',
  `thumb` varchar(100) COMMENT '压缩图',
  `pic` varchar(100) COMMENT '原图',
  PRIMARY KEY (`id`),
  KEY `camp_id` (`camp_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户发表的动态'

---
--- 动态的评论表
---

CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) unsigned COMMENT '回复其它评论的id',
  `art_id` int(11) unsigned NOT NULL COMMENT '动态的id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '评论者的用户id',
  `content` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pubtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY `art_id` (`art_id`) REFERENCES arts(`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 


---
--- 手机归属地表 字段不用char
---
CREATE TABLE IF NOT EXISTS `phones` (
  `id` int(11) unsigned NOT NULL,
  `phone` mediumint unsigned NOT NULL,
  `province` char(3),
  `city` char(5),
  `provider` char(5),
  `areacode` char(4),
  `postcode` char(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone` (`phone`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8
