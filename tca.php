<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_crud_groups"] = array (
	"ctrl" => $TCA["tx_crud_groups"]["ctrl"],
	"intercrude" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,title,subtitle,roles,fe_groups,allow_type"
	),
	"feIntercrude" => $TCA["tx_crud_groups"]["feIntercrude"],
	"columns" => array (
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_crud_groups',
				'foreign_table_where' => 'AND tx_crud_groups.pid=###CURRENT_PID### AND tx_crud_groups.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_groups.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"subtitle" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_groups.subtitle",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"roles" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_groups.roles",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_crud_roles",	
				"foreign_table_where" => "ORDER BY tx_crud_roles.uid",	
				"size" => 5,	
				"minitems" => 1,
				"maxitems" => 100,	
				"MM" => "tx_crud_groups_roles_mm",
			)
		),
		"fe_groups" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_groups.fe_groups",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_groups",	
				"size" => 5,	
				"minitems" => 1,
				"maxitems" => 100,
			)
		),
		"allow_type" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_roles.allow_type",		
			'config' => array (
				'type'                => 'select',
				'items' => array(
					array("OWNER",1),
					array('GROUP',2),
					array('GLOBAL',3)
				)
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, title;;;;2-2-2, subtitle;;;;3-3-3, roles, fe_groups, allow_type")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);

$TCA["tx_crud_redirects"] = array (
	"ctrl" => $TCA["tx_crud_redirects"]["ctrl"],
	"intercrude" => array (
	"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,title,description,redirect,target,login,page"
	),
	"feIntercrude" => $TCA["tx_crud_redirects"]["feIntercrude"],
	"columns" => array (
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_crud_redirects',
				'foreign_table_where' => 'AND tx_crud_redirects.pid=###CURRENT_PID### AND tx_crud_redirects.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_redirects.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_redirects.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"redirect" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_redirects.redirect",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
			)
		),
		"target" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_redirects.target",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
			)
		),
		'login' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:crud/locallang_db.xml:tx_crud_redirects.login',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"page" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_redirects.page",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
			)
		),
	),
	"types" => array (
	"0" => array("showitem" => "sys_language_uid;;3;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, title;;;;2-2-2,description,redirect,target,login,page")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);


$TCA["tx_crud_roles"] = array (
	"ctrl" => $TCA["tx_crud_roles"]["ctrl"],
	"intercrude" => array (
	"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,title,subtitle,allow_create,allow_retrieve,allow_update,allow_delete,allow_controller,allow_type"
	),
	"feIntercrude" => $TCA["tx_crud_roles"]["feIntercrude"],
	"columns" => array (
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_crud_roles',
				'foreign_table_where' => 'AND tx_crud_roles.pid=###CURRENT_PID### AND tx_crud_roles.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_roles.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "uniqueInPid",
			)
		),
		"subtitle" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_roles.subtitle",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"allow_create" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_roles.allow_create",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_crud_options",	
				"foreign_table_where" => "AND tx_crud_options.action=0 AND tx_crud_options.pid=###CURRENT_PID### ORDER BY tx_crud_options.uid",
				"size" => 10,	
				"minitems" => 0,
				"maxitems" => 100,	
				"MM" => "tx_crud_roles_allow_create_mm",
			)
		),
		"allow_retrieve" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_roles.allow_retrieve",
			"config" => Array (
				"type" => "select",
				"foreign_table" => "tx_crud_options",
				"foreign_table_where" => "AND tx_crud_options.action=1 AND tx_crud_options.pid=###CURRENT_PID### ORDER BY tx_crud_options.uid",
				"size" => 10,
				"minitems" => 0,
				"maxitems" => 100,
				"MM" => "tx_crud_roles_allow_retrieve_mm",
			)
		),
		"allow_update" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_roles.allow_update",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_crud_options",	
				"foreign_table_where" => "AND tx_crud_options.action=2 AND tx_crud_options.pid=###CURRENT_PID### ORDER BY tx_crud_options.uid",
				"size" => 10,	
				"minitems" => 0,
				"maxitems" => 100,	
				"MM" => "tx_crud_roles_allow_update_mm",
			)
		),
		"allow_delete" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_roles.allow_delete",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_crud_options",	
				"foreign_table_where" => "AND tx_crud_options.action=3 AND tx_crud_options.pid=###CURRENT_PID### ORDER BY tx_crud_options.uid",
				"size" => 10,	
				"minitems" => 0,
				"maxitems" => 100,	
				"MM" => "tx_crud_roles_allow_delete_mm",
			)
		),
		"allow_controller" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_roles.allow_controller",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_crud_options",	
				"foreign_table_where" => "AND tx_crud_options.action=4 AND tx_crud_options.pid=###CURRENT_PID### ORDER BY tx_crud_options.uid",
				"size" => 10,	
				"minitems" => 0,
				"maxitems" => 100,	
				"MM" => "tx_crud_roles_allow_controller_mm",
			)
		),
		"allow_type" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_roles.allow_type",		
			'config' => array (
				'type'                => 'select',
				'items' => array(
					array("OWNER",1),
					array('GROUP',2),
					array('GLOBAL',3)
				)
			)
		),
	),
	"types" => array (
	"0" => array("showitem" => "sys_language_uid;;3;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, title;;;;2-2-2, subtitle;;;;3-3-3, allow_create, allow_retrieve,allow_update, allow_delete, allow_controller, allow_type")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_crud_options"] = array (
	"ctrl" => $TCA["tx_crud_options"]["ctrl"],
	"intercrude" => array (
		"showRecordFieldList" => "hidden,title,action,target,value"
	),
	"feIntercrude" => $TCA["tx_crud_options"]["feIntercrude"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_options.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "uniqueInPid",
			)
		),
		"action" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_options.action",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:crud/locallang_db.xml:tx_crud_options.action.I.0", "0"),
					Array("LLL:EXT:crud/locallang_db.xml:tx_crud_options.action.I.1", "1"),
					Array("LLL:EXT:crud/locallang_db.xml:tx_crud_options.action.I.2", "2"),
					Array("LLL:EXT:crud/locallang_db.xml:tx_crud_options.action.I.3", "3"),
					Array("LLL:EXT:crud/locallang_db.xml:tx_crud_options.action.I.4", "4"),
					Array("LLL:EXT:crud/locallang_db.xml:tx_crud_options.action.I.5", "5"),
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
		"target" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_options.target",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"value" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_options.value",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, action;;;;3-3-3, target, value")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_crud_users"] = array (
	"ctrl" => $TCA["tx_crud_users"]["ctrl"],
	"intercrude" => array (
		"showRecordFieldList" => "hidden,crud_group,crud_role,feuser"
	),
	"feIntercrude" => $TCA["tx_crud_users"]["feIntercrude"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"crud_group" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_users.crud_group",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_crud_groups",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"crud_role" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_users.crud_role",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_crud_roles",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"feuser" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_users.feuser",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, crud_group, crud_role, feuser")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_crud_log"] = array (
	"ctrl" => $TCA["tx_crud_log"]["ctrl"],
	"intercrude" => array (
		"showRecordFieldList" => "title,crud_action,crud_table,crud_record,crud_page,crud_user,crud_session,crud_username,crud_cardinality"
	),
	"feIntercrude" => $TCA["tx_crud_log"]["feIntercrude"],
	"columns" => array (
		"crud_action" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_log.crud_action",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_log.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"crud_table" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_log.crud_table",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"crud_record" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_log.crud_record",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"crud_page" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_log.crud_page",		
			"config" => Array (
				"type" => "input",	
				"size" => "4",
			)
		),
		"crud_user" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_log.crud_user",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"crud_session" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_log.crud_session",		
			"config" => Array (
				"type" => "input",	
				"size" => "32",
			)
		),
		"crud_username" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_log.crud_username",		
			"config" => Array (
				"type" => "input",	
				"size" => "32",
			)
		),
		"crud_cardinality" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_log.crud_cardinality",		
			"config" => Array (
				"type" => "input",	
				"size" => "11",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "title,crud_action, crud_table, crud_record, crud_page, crud_user, crud_session, crud_username, crud_cardinality")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime")
	)
);
$TCA["tx_crud_histories"] = array (
	"ctrl" => $TCA["tx_crud_log"]["ctrl"],
	"intercrude" => array (
		"showRecordFieldList" => "title,crud_action,crud_table,crud_record,crud_page,crud_user,crud_username"
	),
	"feIntercrude" => $TCA["tx_crud_log"]["feIntercrude"],
	"columns" => array (
		"crud_action" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_log.crud_action",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_log.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"crud_table" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_log.crud_table",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"crud_record" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_log.crud_record",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"crud_page" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_log.crud_page",		
			"config" => Array (
				"type" => "input",	
				"size" => "4",
			)
		),
		"crud_user" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_log.crud_user",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		
		"crud_username" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_log.crud_username",		
			"config" => Array (
				"type" => "input",	
				"size" => "32",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "title,crud_action,crud_table,crud_record,crud_page,crud_user,crud_username")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime")
	)
);


$TCA["tx_crud_locks"] = array (
	"ctrl" => $TCA["tx_crud_locks"]["ctrl"],
	"intercrude" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,crud_table,crud_record,crud_timeout,crud_user,"
	),
	"feIntercrude" => $TCA["tx_crud_locks"]["feIntercrude"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		"crud_table" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_locks.crud_table",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"crud_record" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_locks.crud_record",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"crud_timeout" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_locks.crud_timeout",		
			"config" => Array (
				"type"     => "input",
				"size"     => "12",
				"max"      => "20",
				"eval"     => "datetime",
				"checkbox" => "0",
				"default"  => "0"
			)
		),
		"crud_user" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:crud/locallang_db.xml:tx_crud_locks.crud_user",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, crud_table, crud_record, crud_timeout, crud_user")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime")
	)
);
?>