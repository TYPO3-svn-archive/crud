#
# Table structure for table 'tx_crud_groups_roles_mm'
# 
#
CREATE TABLE tx_crud_groups_roles_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'tx_crud_groups'
#
CREATE TABLE tx_crud_groups (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	subtitle text NOT NULL,
	roles int(11) DEFAULT '0' NOT NULL,
	fe_groups blob NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);




#
# Table structure for table 'tx_crud_roles_allow_create_mm'
# 
#
CREATE TABLE tx_crud_roles_allow_create_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'tx_crud_roles_allow_retrieve_mm'
# 
#
CREATE TABLE tx_crud_roles_allow_retrieve_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_crud_roles_allow_update_mm'
# 
#
CREATE TABLE tx_crud_roles_allow_update_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);




#
# Table structure for table 'tx_crud_roles_allow_delete_mm'
# 
#
CREATE TABLE tx_crud_roles_allow_delete_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);




#
# Table structure for table 'tx_crud_roles_allow_controller_mm'
# 
#
CREATE TABLE tx_crud_roles_allow_controller_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);




#
# Table structure for table 'tx_crud_roles_allow_plugin_mm'
# 
#
CREATE TABLE tx_crud_roles_allow_plugin_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'tx_crud_roles'
#
CREATE TABLE tx_crud_roles (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	subtitle text NOT NULL,
	allow_create int(11) DEFAULT '0' NOT NULL,
	allow_retrieve int(11) DEFAULT '0' NOT NULL,
	allow_update int(11) DEFAULT '0' NOT NULL,
	allow_delete int(11) DEFAULT '0' NOT NULL,
	allow_controller int(11) DEFAULT '0' NOT NULL,
	allow_plugin int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_crud_options'
#
CREATE TABLE tx_crud_options (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	action int(11) DEFAULT '0' NOT NULL,
	target tinytext NOT NULL,
	value tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_crud_users'
#
CREATE TABLE tx_crud_users (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	crud_group blob NOT NULL,
	crud_role blob NOT NULL,
	feuser blob NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_crud_log'
#
CREATE TABLE tx_crud_log (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
    title tinytext NOT NULL,
	crud_action tinytext NOT NULL,
	crud_table tinytext NOT NULL,
	crud_record tinytext NOT NULL,
	crud_page tinytext NOT NULL,
	crud_user blob NOT NULL,
	crud_session tinytext NOT NULL,
	crud_username tinytext NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_crud_locks'
#
CREATE TABLE tx_crud_locks (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	crud_table tinytext NOT NULL,
	crud_record tinytext NOT NULL,
	crud_timeout int(11) DEFAULT '0' NOT NULL,
	crud_user blob NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);




#
# Table structure for table 'tx_crud_cached'
#
CREATE TABLE tx_crud_cached (
	uid int(11) NOT NULL auto_increment,
	tstamp int(11) DEFAULT '0' NOT NULL,
    uuid varchar(36) DEFAULT '0' NOT NULL,
	cached blob NOT NULL,

	PRIMARY KEY (uid),
);

#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
    tx_crud_ts blob  NOT NULL,
);