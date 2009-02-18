<?php
if ($this->get('mode') == 'PROCESS') {
	$params = $this->controller->parameters->getArrayCopy();
	$config = $this->controller->configurations->getArrayCopy();
	if(!isset($params[history])) {
		if($_POST['ajaxTarget']!="thickbox") {
			
			$data = $this->renderPreview();
			echo "<ul>";
			foreach ($data as $uid=>$record) {
				foreach ($record as $key=>$val) {
					if(strlen($val)>=1) {
					$fieldSetup=$config['view.']['setup'][$key];
					echo '<li>' . $this->getLL($config['view.']['setup'][$key]['label']).": ";
					if($fieldSetup['config.']['eval']=="date") {
						if(!$dateFormat)$dateFormat=$this->getLLfromKey("dateTCA.output");
						echo strftime($dateFormat,$record[$fieldName]);
					}
					elseif($fieldSetup['config.']['eval']=="datetime") {
						if(!$datetimeFormat)$datetimeFormat=$this->getLLfromKey("datetimeTCA.output");
						strftime($datetimeFormat,$record[$fieldName]);
					}
					else {
						$val_exploded=explode(",",$val);
						$i=0;
						for($i=0;$i<count($val_exploded);$i++) {
							echo $this->getLL($val_exploded[$i],1);
							if(count($val_exploded)>1 && $i<count($val_exploded)-1) echo ",";
						}
					}
					echo "</li>";
					}
				}
				}
				echo "</ul>";
			
		}
	//	echo '<div class="crud-form">{{{UPDATE~'.$config['storage.']['nameSpace']."~".$params['retrieve']."~LIB.TX_CRUD_UPDATE~".$config['storage.']['fields']."}}}</div>\n";
		if($_POST['ajaxTarget']!="thickbox") {
			//echo '<div class="crud-form">{{{DELETE~'.$config['storage.']['nameSpace']."~".$params['retrieve']."~LIB.TX_CRUD_DELETE~deleted}}}</div>\n";
			$creator = tx_crud__log::getCreator();
			$visitCount = tx_crud__log::getLogUserCount('retrieve');
			$updateCount = tx_crud__log::getLogUserCount('update');
			if ($creator) {
				echo '%%%created%%% %%%on%%% ' . tx_crud__log::getCreationDate() . ' %%%by%%% ' . $creator . "\n";
			}
			
			if (isset($visitCount)) {
			$lastVisitor = tx_crud__log::getLastLogUser('retrieve');
			
			if ($visitCount > 0) {
				echo '%%%atall%%% ' . $visitCount . '-%%%times%%% %%%retrieved%%%';
				if ($lastVisitor == "") {
					$lastVistor = '%%%retrieve_guest%%%';
				}
				echo ' - %%%atlast%%% %%%on%%% ' . tx_crud__log::getLastLogDate('retrieve') . 
					' %%%by%%% ' . $lastVistor . "<br/>\n";
			}
		}
		if (isset($updateCount)) {
			$lastUpdater = tx_crud__log::getLastLogUser('update');
			
			if ($updateCount > 0) {
				if(count($config['view.']['histories']) > 0) {
					$params['history'] = 1;
					$params['ajaxTarget']=$this->getAjaxTarget("showHistory");
					echo '<a href="' . $this->getUrl($params) . '">';
				}
				echo '%%%atall%%% ' . $updateCount . '-%%%times%%% %%%updated%%%';
				if ($lastUpdater == "") {
					$lastUpdater = '%%%retrieve_guest%%%';
				}
				echo ' - %%%atlast%%% %%%on%%% ' . tx_crud__log::getLastLogDate('update') . 
					' %%%by%%% ' . $lastUpdater . "<br/>\n";
				if(count($config['view.']['histories']) > 0)
					echo '</a>';
			}
		}
	
	?>
	<?php 
		// TODO: Localization
		$this->printBackLink("Back",$config['pidBrowse']);
	}
	}
	else require_once("histories.php");
}
else echo "%%%no_rights_retrieve%%%";

?>
