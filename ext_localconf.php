<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_crud_roles=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_crud_options=1
');
require_once(t3lib_extMgm::extPath('crud').'library/class.tx_crud__parser.php');
//$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-cached'][] = 'tx_crudmarker_parser->contentPostProc_output';
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][] = 'tx_crud__parser->contentPostProc_output';
?>