-- jgive
-- Copyright © 2012 - All rights reserved.
-- License: GNU/GPL
--
-- jgive table(s) definition
--
--

--
-- Table structure for table `#__jg_campaigns`
--

CREATE TABLE IF NOT EXISTS `#__jg_campaigns` (
  `id` int(11) NOT NULL auto_increment,
  `category_id` int(11) NOT NULL,
  `org_ind_type` varchar(250) NOT NULL,
  `creator_id` int(11) NOT NULL COMMENT 'userid of user who created this capmpaign',
  `title` varchar(250) NOT NULL,
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `type` varchar(50) NOT NULL default 'donation',
  `max_donors` int(11) NOT NULL default '0',
  `minimum_amount` int( 11 ) NOT NULL default  '0' COMMENT  'minimum amount for transaction',
  `short_description` text NOT NULL,
  `long_description` text NOT NULL,
  `goal_amount` float(10,2) NOT NULL,
  `paypal_email` varchar(250) NOT NULL,
  `first_name` varchar(250) NOT NULL,
  `last_name` varchar(250) NOT NULL,
  `address` text NOT NULL,
  `address2` text NOT NULL,
  `city` varchar(250) NOT NULL,
  `other_city` tinyint(1) NOT NULL,
  `state` varchar(250) NOT NULL,
  `country` varchar(250) NOT NULL,
  `zip` varchar(250) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `group_name` varchar(250) NOT NULL,
  `website_address` varchar(250) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `allow_exceed` tinyint(1) NOT NULL,
  `allow_view_donations` tinyint(1) NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `internal_use` text NOT NULL COMMENT 'use internally',
  `featured` tinyint(3) NOT NULL default '0' COMMENT 'Set if campaign is featured.',
  `js_groupid` int(11) NOT NULL,
  `success_status` int(1) NOT NULL DEFAULT '0' COMMENT '0 - Ongoing, 1 - Successful, -1 - Failed',
  `processed_flag` varchar(50) DEFAULT 'NA' COMMENT 'NA - NA, SP - Success Processed, RF - Refunded',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
-- --------------------------------------------------------

--
-- Table structure for table `#__jg_campaigns_givebacks`
--

CREATE TABLE IF NOT EXISTS `#__jg_campaigns_givebacks` (
  `id` int(11) NOT NULL auto_increment COMMENT 'primary key',
  `campaign_id` int(11) NOT NULL COMMENT 'fk - primary key of table#__jg_campaigns',
  `amount` int(11) NOT NULL COMMENT 'giveback amount',
  `description` text NOT NULL COMMENT 'giveback details',
  `order` int(5) NOT NULL COMMENT 'ordering',
  `quantity` int(11) NOT NULL,
  `total_quantity` int(11) NOT NULL,
  `image_path` varchar(400) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__jg_campaigns_images`
--

CREATE TABLE IF NOT EXISTS `#__jg_campaigns_images` (
  `id` int(11) NOT NULL auto_increment COMMENT 'primary key',
  `campaign_id` int(11) NOT NULL COMMENT 'fk - primary key of table#__jg_campaigns',
  `path` varchar(400) NOT NULL COMMENT 'image path',
  `video_provider` varchar(50) NOT NULL,
  `video_url` text NOT NULL,
  `video_img` tinyint(1) NOT NULL,
  `gallery_image` tinyint(1) NOT NULL,
  `order` int(5) NOT NULL COMMENT 'ordering',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__jg_donations`
--

CREATE TABLE IF NOT EXISTS `#__jg_donations` (
  `id` int(11) NOT NULL auto_increment COMMENT 'primary key',
  `campaign_id` int(11) NOT NULL COMMENT 'fk - primary key of table #__jg_campaigns',
  `donor_id` int(11) NOT NULL COMMENT 'fk - primary key of table #__jg_donors',
  `order_id` int(11) NOT NULL COMMENT 'fk - primary key of table #__jg_orders',
  `giveback_id` int(11) NOT NULL COMMENT 'id of jg_campaigns_givebacks',
  `annonymous_donation` tinyint(1) NOT NULL,
  `is_recurring` tinyint(1) NOT NULL default '0',
  `recurring_frequency` varchar(100) default NULL,
  `recurring_count` int(11) default NULL,
  `subscr_id` varchar(100) default NULL,
  `comment` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__jg_donors`
--

CREATE TABLE IF NOT EXISTS `#__jg_donors` (
  `id` int(11) NOT NULL auto_increment COMMENT 'primary key',
  `user_id` int(11) NOT NULL COMMENT 'fk - primary key of table #__users',
  `campaign_id` int(11) NOT NULL COMMENT 'fk - primary key of table #__jg_donors',
  `email` varchar(255) NOT NULL,
  `first_name` varchar(250) NOT NULL,
  `last_name` varchar(250) NOT NULL,
  `address` text NOT NULL,
  `address2` text NOT NULL,
  `city` varchar(250) NOT NULL,
  `state` varchar(250) NOT NULL,
  `country` varchar(250) NOT NULL,
  `zip` varchar(250) NOT NULL,
  `phone` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__jg_orders`
--

CREATE TABLE IF NOT EXISTS `#__jg_orders` (
  `id` int(11) NOT NULL auto_increment COMMENT 'primary key',
  `order_id` VARCHAR( 23 ) NOT NULL,
  `campaign_id` int(11) NOT NULL COMMENT 'fk - primary key of table#__jg_campaigns',
  `donor_id` int(11) NOT NULL COMMENT 'fk - primary key of table#__jg_donors',
  `donation_id` int(11) NOT NULL COMMENT 'fk - primary key of table#__jg_donations',
  `fund_holder` tinyint( 1 ) NOT NULL DEFAULT '0' COMMENT 'To whose account money was originally transferred to: 0-admin, 1-campaign promoter',
  `cdate` datetime NOT NULL COMMENT 'creation date',
  `mdate` datetime NOT NULL COMMENT 'modification date',
  `transaction_id` varchar(100) NOT NULL COMMENT 'transaction id given by payment processor',
  `original_amount` float(10,2) NOT NULL COMMENT 'original amount with no fee applied',
  `amount` float(10,2) NOT NULL COMMENT 'amount after applying fee',
  `fee` float(10,2) NOT NULL COMMENT 'processing fee',
  `vat_number` varchar(100) NOT NULL COMMENT 'VAT number',
  `status` varchar(100) NOT NULL COMMENT 'payment status',
  `processor` varchar(100) NOT NULL COMMENT 'payment gateway used',
  `ip_address` varchar(50) NOT NULL COMMENT 'IP address of payer',
  `extra` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `#__jg_payouts`
--

CREATE TABLE IF NOT EXISTS `#__jg_payouts` (
  `id` int(15) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `payee_name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `transaction_id` varchar(15) NOT NULL,
  `email_id` varchar(55) NOT NULL,
  `amount` float(10,2) NOT NULL,
  `status` varchar(100) character set utf8 collate utf8_unicode_ci NOT NULL,
  `ip_address` varchar(50) character set utf8 collate utf8_unicode_ci NOT NULL,
  `type` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
