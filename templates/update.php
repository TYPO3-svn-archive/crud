
<?php if (!defined ('TYPO3_MODE')) die ('Access denied.'); ?>
<?php 
	if ($this->get('mode') == 'EDIT') {
	
	$entryList = $this->get('setup');
	$entryList = $this->renderSetup($entryList);

	$this->printAsFormHeader();

	echo '<div id="crud-tabs-form">' . "\n\t";
	echo '<ul>' . "\n\t";
	$i = 1;
	foreach ($entryList as $divider=>$sections)  {
		echo '<li><a href="'.$this->baseUrl.'#fragment-' . $i . '"><span>' . $this->getLLfromKey($divider,0,1) . '</span></a></li>' . "\n\t";
		$i++;
	}
	echo "</ul>\n";


	$i = 1;
	$j = 1;
	foreach ($entryList as $divider=>$sections) {
		echo '<div id="fragment-' . $i . '">';
		foreach ($sections as $section=>$entries) {
			echo '<fieldset class="hasHelp" refId="csh' . $j . '">' . "\n\t";
//			echo '<legend>' . $this->getLLfromKey($section) . '</legend>' . "\n\t"; //TODO: sinnvolle legend ermöglichen
			echo '<dl>' . "\n\t";
			if ($this->getLLfromKey($section.".csh")) {
				echo '<div class="csh" id="csh' . $j . '">' . $this->getLLfromKey($section.".csh") . '</div>' . "\n\t";
			}
			foreach ($entries as $entry=>$value) {
				echo "<dt>\n\t\t<label>" . $value['label'];
				if ($value['required']) {
					echo " *";
				}
				echo "</label>\n\t</dt>\n\t\t";
				echo "<dd>\n\t\t";
				echo $value['html'];
				if ($this->getLLfromKey($value['key'].".csh")) {
					echo '<div class="csh">' . $this->getLLfromKey("informations.csh","EXT:partner__profiles/locallang.xml").'</div>' . "\n\t\t";
				}
				if ($value['error']) {
					echo '<div class="fielderror">' . $value['error'] . '</div>' . "\n\t";
				}
				echo "</dd>\n\t";
			}
			echo "</dl>\n</fieldset>\n";
			$j++;
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

	$this->printAsFormFooter();
	
	echo '</div>' . "\n";
	
	$this->printAsFormCancel();

	} elseif ($this->get('mode') == 'ICON') {
		$this->printActionLink("update") . "";
	} elseif ($this->get('mode') == 'PROCESS') {
		echo "%%%update_preview%%%".$this->getExitLink("%%%back%%%");
	} elseif ($this->get('mode') == 'HIDE') {
		echo "";
	}
	elseif ($this->get('mode') == 'NO_RIGHTS') {
		echo "%%%no_rights_update%%%";
	}
	else {
		echo "%%%update_record_failed%%%";
	}
?>
