<?php 
if (!defined ('TYPO3_MODE') && !$this->get('panelAction') == 'DELETE') {
	die ('Access denied.'); 
}

if ($this->get('mode') == 'PROCESS') {
	echo '<p>%%%delete_preview%%%</p>';
} elseif ($mode == 'NOT_EXIST') {
	echo '<p>%%%delete_not_exist%%%</p>';
} elseif ($mode == 'QUERY_ERROR') {
	echo '<p>%%%delete_query_failed%%%</p>';
} elseif ($this->get('mode') == 'ICON') {
	$this->printActionLink();
} else {
	echo '';
}
?>