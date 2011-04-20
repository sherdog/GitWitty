CREATE TABLE IF NOT EXISTS `#__jaccounts` (
`id` int(11) NOT NULL auto_increment,
`name` varchar(255) NOT NULL,
`site` varchar(255) NOT NULL,
`parent_account_id` int(11) default NULL,
`account_number` varchar(255) NOT NULL,
`type` varchar(255) NOT NULL,
`industry` varchar(255) NOT NULL,
`annual_revenue` varchar(255) NOT NULL,
`rating` varchar(255) NOT NULL,
`phone` varchar(255) NOT NULL,
`fax` varchar(255) NOT NULL,
`website` varchar(255) NOT NULL,
`ticker_symbol` varchar(255) NOT NULL,
`ownership` varchar(255) NOT NULL,
`employees` varchar(255) NOT NULL,
`sic_code` varchar(255) NOT NULL,
`billing_street` text NOT NULL,
`billing_city` varchar(255) NOT NULL,
`billing_state` varchar(255) NOT NULL,
`billing_zip` varchar(255) NOT NULL,
`billing_country` varchar(255) NOT NULL,
`shipping_street` varchar(255) NOT NULL,
`shipping_city` varchar(255) NOT NULL,
`shipping_state` varchar(255) NOT NULL,
`shipping_zip` varchar(255) NOT NULL,
`shipping_country` varchar(255) NOT NULL,
`notes` text NOT NULL,
`created` datetime NOT NULL,
`modified` datetime NOT NULL,
`published` int(11) default NULL,
`manager_id` int(11) default NULL,
PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS `#__jcontacts` (
`id` int(11) NOT NULL auto_increment,
`jid` int(11) default NULL,
`title_name` varchar(255) NOT NULL,
`first_name` varchar(255) NOT NULL,
`last_name` varchar(255) NOT NULL,
`account_id` int(11) default NULL,
`title` varchar(255) NOT NULL,
`department` varchar(255) NOT NULL,
`birthdate` date default NULL,
`reports_to` int(11) default NULL,
`lead_source` varchar(255) NOT NULL,
`phone` varchar(255) NOT NULL,
`home_phone` varchar(255) NOT NULL,
`mobile_phone` varchar(255) NOT NULL,
`other_phone` varchar(255) NOT NULL,
`fax` varchar(255) NOT NULL,
`assistant` varchar(255) NOT NULL,
`asst_phone` varchar(255) NOT NULL,
`email` varchar(255) default NULL,
`email_opt_out` int(11) default NULL,
`mailing_street` text NOT NULL,
`mailing_city` varchar(255) NOT NULL,
`mailing_state` varchar(255) NOT NULL,
`mailing_zip` varchar(255) NOT NULL,
`mailing_country` varchar(255) NOT NULL,
`other_street` text NOT NULL,
`other_city` varchar(255) NOT NULL,
`other_state` varchar(255) NOT NULL,
`other_zip` varchar(255) NOT NULL,
`other_country` varchar(255) NOT NULL,
`lat` varchar(255) NOT NULL,
`lng` varchar(255) NOT NULL,
`other_lat` varchar(255) NOT NULL,
`other_lng` varchar(255) NOT NULL,
`notes` text NOT NULL,
`created` datetime NOT NULL,
`modified` datetime NOT NULL,
`published` int(11) default NULL,
`manager_id` int(11) default NULL,
PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS `#__jleads` (
`id` int(11) NOT NULL auto_increment,
`first_name` varchar(255) NOT NULL,
`last_name` varchar(255) NOT NULL,
`company_name` varchar(255) NOT NULL,
`email` varchar(255) NOT NULL,
`phone` varchar(255) NOT NULL,
`status` varchar(255) default NULL,
`message` text,
`notes` text NOT NULL,
`converted` int(11) NOT NULL,
`created` datetime NOT NULL,
`modified` datetime NOT NULL,
`published` int(11) default NULL,
`manager_id` int(11) default NULL,
PRIMARY KEY  (`id`)
);

INSERT INTO `#__menu` (`id`, `menutype`, `name`, `link`, `type`, `published`, `parent`, `componentid`, `sublevel`, `ordering`, `checked_out`, `checked_out_time`, `pollid`, `browserNav`, `access`, `utaccess`, `params`) VALUES
('', 'usermenu', 'View My Contact Details', 'index.php?option=com_jcontacts&task=viewMyDetails', 'url', 1, 0, 0, 0, 6, 0, '0000-00-00 00:00:00', 0, 0, 1, 2, 'menu_image=-1'),
('', 'usermenu', 'My Contacts', 'index.php?option=com_jcontacts&task=myContacts', 'url', 1, 0, 0, 0, 7, 0, '0000-00-00 00:00:00', 0, 0, 2, 2, 'menu_image=-1'),
('', 'usermenu', 'My Accounts', 'index.php?option=com_jcontacts&task=myAccounts', 'url', 1, 0, 0, 0, 8, 0, '0000-00-00 00:00:00', 0, 0, 2, 2, 'menu_image=-1');

UPDATE `#__menu` SET `published` = '0' WHERE `link` = 'index.php?option=com_user&view=user&task=edit';
