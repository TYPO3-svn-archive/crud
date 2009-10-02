<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TCA["tx_crud_groups"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:crud/locallang_db.xml:tx_crud_groups',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_crud_groups.gif',
	),
	"feIntercrude" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, title, subtitle, roles, fe_groups, allow_type",
	)
);

$TCA["tx_crud_roles"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:crud/locallang_db.xml:tx_crud_roles',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_crud_roles.gif',
	),
	"feIntercrude" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, title, subtitle, allow_create, allow_retrieve, allow_update, allow_delete, allow_controller, allow_type",
	)
);

$TCA["tx_crud_options"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:crud/locallang_db.xml:tx_crud_options',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_crud_options.gif',
	),
	"feIntercrude" => array (
		"fe_admin_fieldList" => "hidden, title, action, target, value",
	)
);

$TCA["tx_crud_users"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:crud/locallang_db.xml:tx_crud_users',		
		'label'     => 'feuser',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_crud_users.gif',
	),
	"feIntercrude" => array (
		"fe_admin_fieldList" => "hidden, crud_group, crud_role, feuser",
	)
);

$TCA["tx_crud_log"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:crud/locallang_db.xml:tx_crud_log',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_crud_log.gif',
	),
	"feIntercrude" => array (
		"fe_admin_fieldList" => "title,crud_action, crud_table, crud_record, crud_page, crud_user, crud_session, crud _username, crud_cardinality",
	)
);



$TCA["tx_crud_locks"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:crud/locallang_db.xml:tx_crud_locks',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_crud_locks.gif',
	),
	"feIntercrude" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, crud_table, crud_record, crud_timeout, crud_user",
	)
);

$tempColumns = Array (
    "tx_crud_ts" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:crud/locallang_db.xml:tt_content.tx_crud_ts",        
        "config" => Array (
            "type" => "group",    
            "internal_type" => "db",    
            "allowed" => "sys_template",    
            "size" => 1,    
            "minitems" => 0,
            "maxitems" => 1,
        )
    ),
);


t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_content","tx_crud_ts;;;;1-1-1");
t3lib_extMgm::addStaticFile('crud', 'configurations', 'CRUD library');  
t3lib_extMgm::addStaticFile('crud', 'doc/examples/tt_news/configurations', 'CRUD TT_NEWS Example');  
if (TYPO3_MODE == 'BE')	{
	t3lib_extMgm::addModule('web','txcrudM1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
}
?>