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
CREATE TABLE IF NOT EXISTS `arts` (
  `art_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `city` char(5) NOT NULL DEFAULT '火星',
  `user_id` int(11) unsigned NOT NULL,
  `content` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pubtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comm` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
  `pic` varchar(100) DEFAULT '' COMMENT '原图',
  PRIMARY KEY (`art_id`),
  KEY `arts_userid_users` (`user_id`),
  CONSTRAINT `arts_userid_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户发表的动态' 

---
--- 动态的评论表
---
CREATE TABLE IF NOT EXISTS `comments` (
  `comment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `response_user_id` int(11) unsigned DEFAULT NULL COMMENT '回复其他评论的用户id',
  `art_id` int(11) unsigned NOT NULL COMMENT '动态的id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '评论者的用户id',
  `content` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pubtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`),
  KEY `comments_artid_arts` (`art_id`),
  CONSTRAINT `comments_artid_arts` FOREIGN KEY (`art_id`) REFERENCES `arts` (`art_id`) ON DELETE CASCADE
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

---
--- 文章用户点赞表
---
CREATE TABLE IF NOT EXISTS `user_like_art` (
  `user_like_art_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `art_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `status` tinyint NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_like_art_id`)
)ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8

---
--- 文章点赞数表
---
CREATE TABLE IF NOT EXISTS `art_like` (
  `art_like_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `art_id` int(11) unsigned NOT NULL,
  `like_count` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`art_like_id`)
)ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 



---
--- 矿车表
---
CREATE TABLE IF NOT EXISTS `cars` (
  `car_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `gold_count` int(11) unsigned NOT NULL DEFAULT 0,
  `car_type` tinyint unsigned NOT NULL DEFAULT 1,
  `durability` tinyint unsigned NOT NULL DEFAULT 100,
  `digging` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY(`car_id`)
)AUTO_INCREMENT=1 DEFAULT CHARSET=UTF8 



---
--- 房间表
---
CREATE TABLE IF NOT EXISTS `rooms` (
  `room_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `room_type` tinyint unsigned NOT NULL DEFAULT 1,
  `room_count` int(11) unsigned NOT NULL DEFAULT 100000,
  `people_num` tinyint unsigned NOT NULL DEFAULT 0,
  `buff` tinyint unsigned NOT NULL DEFAULT 1,
  `buff_begin` timestamp NOT NULL DEFAULT 0,
  `buff_end` timestamp NOT NULL DEFAULT 0,
  PRIMARY KEY(`room_id`)
)AUTO_INCREMENT=1 DEFAULT CHARSET=UTF8


---
--- 矿车进入房间挖矿表
---
CREATE TABLE IF NOT EXISTS `digs` (
  `dig_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `dig_begin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dig_end` timestamp NOT NULL DEFAULT 0,
  `dig_count` int(11) NOT NULL DEFAULT 0,
  `car_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  PRIMARY KEY (dig_id)
)AUTO_INCREMENT=1 DEFAULT CHARSET=UTF8


--
-- 实名认证表
--

CREATE TABLE `identifications` (
  `identification_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id` varchar(18) NOT NULL,
  `mobile` char(20) NOT NULL,
  `name` char(20) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`identification_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 



--
-- 买矿车提成表
--

CREATE TABLE `commissions` (
  `commission_id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned  NOT NULL,
  `car_user_id` int unsigned NOT NULL,
  `count` int unsigned NOT NULL,
  PRIMARY KEY (`commission_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 


--
-- 转赠表
--

CREATE TABLE `presents` (
  `present_id` int unsigned NOT NULL AUTO_INCREMENT,
  `from_user_id` int unsigned NOT NULL,
  `to_user_id` int unsigned NOT NULL,
  `type` tinyint NOT NULL DEFAULT 0 COMMENT '0代表转赠金币，1,2,3代表转赠矿车',
  `count` int NOT NULL DEFAULT 1 COMMENT '转赠矿车的数量1，或者金币数量',
  `present_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`present_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
 
--
-- 收入支出表
--
CREATE TABLE IF NOT EXISTS `bills` (
  `bill_id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint unsigned NOT NULL COMMENT '0代表转赠，2代表挖矿',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `golds` int unsigned NOT NULL,
  `ref_id` int unsigned NOT NULL COMMENT '对应的转赠或者挖矿id',
  PRIMARY KEY (bill_id)
)ENGINE=InnoDB  AUTO_INCREMENT=1 DEFAULT CHARSET=UTF8

--
-- 手机充值表
--
CREATE TABLE IF NOT EXISTS `recharges` (
  `recharge_id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `type` tinyint unsigned NOT NULL COMMENT '0代表话费，1代表流量',
  `goods` smallint unsigned NOT NULL,
  `complete` tinyint unsigned NOT NULL DEFAULT 0 COMMENT '是否充值成功',
  `begin_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '用户发起充值的时间',
  `end_time` timestamp NOT NULL DEFAULT 0 COMMENT '后台受理此次充值的时间',
  PRIMARY KEY (recharge_id)
)ENGINE=InnoDB  AUTO_INCREMENT=1 DEFAULT CHARSET=UTF8


