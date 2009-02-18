<?php 
	if (!defined ('TYPO3_MODE') && !$this->get('panelAction') == 'DELETE') {
		die ('Access denied.'); 
	}

	if($this->get('mode') == 'PROCESS') {
		echo '%%%delete_preview%%%';
	} elseif ($mode == 'NOT_EXIST') {
		echo '%%%delete_not_exist%%%';
	} elseif ($mode == 'QUERY_ERROR') {
		echo '%%%delete_query_failed%%%';
	} elseif ($this->get('mode') == 'ICON') {
		$this->printActionLink();
	} else {
		echo "";
	}
?>
