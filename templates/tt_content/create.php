<?php 
if (!defined ('TYPO3_MODE')) die ('Access denied.');
if ($this->get('mode') == 'EDIT') {
	$entryList = $this->get('setup');
	$entryList = $this->renderSetup($entryList);
	//t3lib_div::debug($entryList);
	$pathExtraLL='LLL:EXT:crud/templates/tt_content/locallang.xml:';
	$this->printAsFormHeader();
	echo '<div id="crud-tabs-form">' . "\n\t";
	echo '<ul>' . "\n\t";
	$i = 1;
	foreach ($entryList as $divider=>$sections) {
		echo '<li><a href="'.$this->baseUrl.'#fragment-' . $i . '"><span><b>' . $this->getLLfromKey($divider,0,1) . '</b></span></a></li>' . "\n\t";
		$i++;
	}
	echo "</ul>\n";
	$i = 1;
	$j = 1;
	foreach ($entryList as $divider=>$sections) {
		echo '<div id="fragment-' . $i . '">' . "\n\t";
		foreach ($sections as $section=>$entries) {
	
			echo '<fieldset class="crud-section">' . "\n";
			echo '<legend>' . $section . '</legend><dl>'; //TODO: sinnvolle legende ermöglichen
			if(isset($entries["starttime"])  && $entries["endtime"] ) {
					$entries["starttime"]['html'].='<label>'.$this->getLL($entries['endtime']['label'],1).'</label>'.$entries['endtime']['html'];
					unset($entries['endtime']);
			}
			elseif(isset($entry["spaceAfter"])  && $entry["spaceBefore"] ) {
					$entries["spaceBefore"]['html'].='<label>'.$this->getLL($entries['spaceAfter']['label'],1).'</label>'.$entries['spaceAfter']['html'];
					unset($entries['spaceAfter']);
			}
			foreach ($entries as $entry=>$value) {
				
				echo '<dt><label>' . $value['label'];
				if ($value['required']) {
					echo " *";
				}
				echo '</label></dt>' . "\n\t";
				echo '<dd';
				if ($this->getLLfromKey($section.".csh")) {
					echo 'class="hasHelp" refId="csh' . $j . '"';
				}	
				echo '>' . "\n\t\t";
				
				echo $value['html'];
				$help=$this->getLL($pathExtraLL.$value['key'].".csh");
				if (strlen($help)>=2){
					echo '<div class="csh" id="csh' . $j . '">' . $help . '</div>';
				}
				if ($value['error']) {
					echo '<div class="fielderror"><b>'.$this->getLLFromKey($value['error'],"EXT:crud/locallang.xml") . '</b></div>';
				}
				echo '</dd>' . "\n\t";
				
				$j++;
			
			}
			echo '</dl></fieldset>' . "\n";
		}
		echo "</div>\n";
		$i++;
	}
	
	$this->loadHeaderData("css","tables");
	$this->loadHeaderData("css","forms");
	$this->loadHeaderData("libraries","jquery");
	$this->loadHeaderData("libraries","jquery-forms");
	$this->loadHeaderData("libraries","jquery-ui-tabs");
	$this->loadHeaderData("libraries","tiny-mce");
	$this->loadHeaderData("libraries","crudscript");
	$this->enableTabs($entryList,"$('#crud-tabs-form > ul')");
	$this->printAsFormSubmit();
	
	echo "</div>";

	$this->printAsFormFooter("");
	//$this->print
	

	$this->printAsFormCancel();
	} elseif ($this->get('mode') == 'ICON') {
		$this->printActionLink("update") . "";
	} elseif ($this->get('mode') == 'PROCESS') {
		echo "%%%create_preview%%%".$this->printAsExitLink("%%%back%%%",0);
	} elseif ($this->get('mode') == 'HIDE') {
		echo "";
	}
	elseif ($this->get('mode') == 'NO_RIGHTS') {
		echo "no_rights_create";
	}
	else {
		echo "%%%update_record_failed%%%";
	}

?>
