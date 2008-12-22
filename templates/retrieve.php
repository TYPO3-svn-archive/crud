<?php
if ($this->get('mode') == 'PROCESS') {
	$config = $this->controller->configurations->getArrayCopy();
	$data = $config['view.']['data'];
//	/t3lib_div::debug($config);
	foreach ($data as $uid=>$record) {
		foreach ($record as $key=>$val) {
			echo '<p>' . $this->getLL($config['view.']['setup'][$key]['label']) . ': ' . $val . "</p>\n";
		}
	}
	//logs
	$creator = $this->getCreator();
	$visitCount = $this->getVisitorCount();

	if ($creator) {
		echo '%%%created%%% %%%on%%% ' . getCreationDate() . ' %%%by%%% ' . $creator . "\n";
	}

	if (isset($visitCount)) {
		$lastVisitor = $this->getLastVisitor();
		echo '%%%atall%%% ' . $visitCount . '-%%%times%%% %%%retrieved%%%';
		if ($visitCount > 0) {
			if ($lastVisitor == "") {
				$lastVistor = '%%%retrieve_guest%%%';
			}
			echo ' - %%%atlast%%% %%%on%%% ' . $this->getLastVisitDate() . ' %%%by%%% ' . $lastVistor;
		}
	}
?>
<?php 
	// TODO: Localization
	$this->printBackLink("Back",$config['pidBrowse']);

}
else echo "%%%no_rights_retrieve%%%";
	
?>
