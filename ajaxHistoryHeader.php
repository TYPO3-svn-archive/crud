<?php
$TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT'] = array(
	'init' => array(
		'enableCHashCache' => 0,
		'appendMissingSlash' => 'ifNotFile',
		'enableUrlDecodeCache' => 1,
		'enableUrlEncodeCache' => 1,
		'respectSimulateStaticURLs' => 0,
		'postVarSet_failureMode'=>'redirect_goodUpperDir',
	),
	'redirects_regex' => array (

	),
	'preVars' => array(
		array(
			'GETvar' => 'no_cache',
			'valueMap' => array(
				'no_cache' => 1,
			),
			'noMatch' => 'bypass',
		),
		array(
			'GETvar' => 'L',
			'valueMap' => array(
				'de' => '0',
				'en' => '1',
			),
			'valueDefault' => 'de',
//			'noMatch' => 'bypass',
		),
	),
	'pagePath' => array (
             'type' => 'user',
              'userFunc' => 'EXT:aoe_realurlpath/class.tx_aoerealurlpath_pagepath.php:&tx_aoerealurlpath_pagepath->main',
             'spaceCharacter' => '-',
            'languageGetVar' => 'L', 
              'rootpage_id' => '2',
            'segTitleFieldList'=>'alias,tx_aoerealurlpath_overridesegment,nav_title,title,subtitle',
         ) ,

	'postVarSets' => array(
		'_DEFAULT' => array(
                ///news medizintechnik
        	'medizintechnik' => array (
    			array(
     				'GETvar' => 'listnews[retrieve]',
    				'lookUpTable' => array(
						'table' => 'tt_news',
						'id_field' => 'uid',
						'alias_field' => 'title',
						'addWhereClause' => ' AND NOT deleted',
						'useUniqueCache' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-',
						),
					),
    			),
   		    ),
   		    //kalender
          'termin' => array (
    			array(
     				'GETvar' => 'events[retrieve]',
    				'lookUpTable' => array(
						'table' => 'tx_crudevents_entry',
						'id_field' => 'uid',
						'alias_field' => 'title',
						'addWhereClause' => ' AND NOT deleted',
						'useUniqueCache' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-',
						),
					),
				),  
   			),
   			//messse
   			'product' => array (
    			array(
     				'GETvar' => 'tradeshow[retrieve]',
    				'lookUpTable' => array(
						'table' => 'tx_yellowmed_product',
						'id_field' => 'uid',
						'alias_field' => 'name',
						'addWhereClause' => ' AND NOT deleted',
						'useUniqueCache' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-',
						),
					),
				),  
   			),
   			
   			//bibliothek
   			'technik' => array (
    			array(
     				'GETvar' => 'library[retrieve]',
    				'lookUpTable' => array(
						'table' => 'tx_yellowmed_product',
						'id_field' => 'uid',
						'alias_field' => 'name',
						'addWhereClause' => ' AND NOT deleted',
						'useUniqueCache' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-',
						),
					),
				),  
   			),
		),
	),
	'fileName' => array(
		'defaultToHTMLsuffixOnPrev'=>0,
		'index' => array(
			'rss.xml' => array(
				'keyValues' => array(
					'type' => 100,
				),
			),
			'rss091.xml' => array(
				'keyValues' => array(
					'type' => 101,
				),
			),
			'rdf.xml' => array(
				'keyValues' => array(
					'type' => 102,
				),
			),
			'atom.xml' => array(
				'keyValues' => array(
					'type' => 103,
				),
			),
		),
	),
);
?>
