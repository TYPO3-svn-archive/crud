<?php
if ($this->get('mode') == 'PROCESS') {
	//t3lib_div::debug($this->controller->configurations->getArrayCopy(),"Setup Browse");
	//$this->renderPreview();
	
	$config = $this->controller->configurations->getArrayCopy();
	//$entryList->renderPreview($this->getent("data"));
	$entryList = $this->renderPreview($this->get('data'));
	$typoscript = $config['view.'];
	//t3lib_div::debug($entryList);
	$rows = count($entryList);
	if ($typoscript['start'] == 0) {
		$now = 1;
		$to = $now + $rows - 1;
	} else {
		$now = $typoscript['start'];
		$to = $now + $rows;
		if ($to > $typoscript['count']) {
			$to = $typoscript['count'];
		}
	}
	$cols = count($typoscript['setup']) + 1;
	$this->loadHeaderData('css', 'tables');
	$this->loadHeaderData('css', 'forms');
	$this->loadHeaderData('css', 'autocomplete');
	$this->loadHeaderData('libraries', 'jquery');
	$this->loadHeaderData('libraries', 'jquery-forms');
	$this->loadHeaderData('libraries', 'jquery-autocomplete');
	$this->loadHeaderData('libraries', 'crudscript');
	$this->loadHeaderData('libraries', 'jquery-ui-tabs');
	$this->loadHeaderData('libraries', 'tiny-mce');
	//$this->enableTabs($entryList, "$('#crud-tabs-form > ul')");
	$this->printAsFilterList('tx_partner_main', 'locality', 'Stadt', $GLOBALS['TSFE']->id, 0, 6);
 	$this->printAsFilterSelect('tx_partner_main', 'locality', 'Stadt', $GLOBALS['TSFE']->id, 6, 60);
 	$this->printAsFilterList('tx_partner_main', 'org_legal_form', 'Rechtsform', $GLOBALS['TSFE']->id, 0, 6);
 	$this->printAsFilterSelect('tx_partner_main', 'org_legal_form', 'Rechtsform', $GLOBALS['TSFE']->id, 6, 60);
 	$this->printAsFilterSelect('tx_partner_main', 'hobbies', 'Hobbies', $GLOBALS['TSFE']->id, 6, 60);
	if (!$typoscript['count']) {
		echo '<p>%%%error_nothing-found%%%</p>';
	} else {
?>
<p style="float: left;">%%%resultsProPage%%%</p>

<?php $this->printAsLimit(5); ?>
<br style="clear: left;" />

<?php
	$this->printAsSearch();
	$this->printAsNoSearch();
?>

<p>%%%youbrowse%%% <?php echo $now . ' - ' . $to . ' %%%from%%% <strong>' . $typoscript['count'] ?>
 %%%records%%%</strong></p>
<?php if (is_array($filter = $this->getActiveFilters())) {
	//t3lib_div::debug($filter);
	echo '<ul class="filters">' . "\n\t"; //TODO: Localization
	foreach ($filter as $label=>$value) {
		echo '<li class="filtersLabel">' . $label . ':</li>' . "\n\t";
		foreach ($value as $uid=>$val) {
			foreach ($val as $url=>$name) {
				echo '<li><a href="' . $url . '" '. $this->getAjaxOnClick() . '>' . $name . '</a></li>' . "\n\t";
			}
		}
	}
	echo '</ul>' . "\n";
}
?>
<table class="crud-browser-table">
	<thead>
		<tr>
			<td>&nbsp;</td>
			<?php
			foreach ($typoscript['setup'] as $fieldName=>$fieldSetup) {
				echo '<th scope="col">' . "\n\t";
				//echo $this->getLLfromKey("tx_partner_main.first_name","EXT:partner/locallang.php");
				$this->printAsSorting($fieldName,$this->getLL($fieldSetup['label']));
							///echo $this->getLL($fieldSetup['label']);
				echo '</th>' . "\n";
			}
			?>
		</tr>
	</thead>
	<tbody>
	<?php
	$row = 1;
	foreach ($entryList as $uid=>$record) {
		($row % 2 != 0) ? $rowclass = 'odd' : $rowclass = 'even'; //TODO: als funktion auslagern
		echo '<tr class="' . $rowclass . '">' . "\n\t";
		//echo '<div class="crud-form">';
		echo '<td>';
		$this->printAsSingleLink($uid);
		//echo $this->getActionLink('update', $uid);
		//echo $this->getActionLink('delete', $uid, 'deleted');
		echo '</td>' . "\n\t";
		$td = 0;
		foreach ($typoscript['setup'] as $fieldName=>$fieldSetup) {
			//t3lib_div::debug($fieldSetup);
			if ($td == 0) {
				echo '<td>';
				echo '<a href="' . $this->printAsSingleLink($uid, $record[$fieldName], 1) . '">' . $record[$fieldName] . '</a></td>';
			} elseif (is_array($fieldSetup['options.']) && $fieldSetup['config.']['type'] != 'check') {
				echo '<td>' . $this->getSortingLink($record[$fieldName], $fieldSetup) . '</td>';
			} elseif ($fieldSetup['config.']['eval'] == 'date') {
				if (!$dateFormat) {
					$dateFormat = $this->getLLfromKey('dateTCA.output');
				}
				echo '<td>' . strftime($dateFormat, $record[$fieldName]) . '</td>';
			} elseif ($fieldSetup['config.']['eval'] == 'datetime') {
				if (!$datetimeFormat) {
					$datetimeFormat = $this->getLLfromKey('datetimeTCA.output');
				}
				echo '<td>' . strftime($datetimeFormat, $record[$fieldName]) . '</td>';
			} else {
				echo '<td>' . $record[$fieldName] . '</td>';
			}
			$td++;
		}
		echo '</tr>' . "\n";
		$row++;
	}
	//t3lib_div::debug($typoscript);
	?>
	</tbody>
</table>
<ul class="pagebrowser clearfix">
	<?php $this->printAsBegin('&laquo;', 0, '<li class="first">|</li>'); ?>
	<?php $this->printAsReverse('%%%prev%%%', 0, '<li class="prev">|</li>'); ?>
	<?php $this->printAsBrowse($pages='3|3', $label=''); ?>
	<?php $this->printAsForward('%%%next%%%', 0, '<li class="next">|</li>'); ?>
	<?php  $this->printAsEnd('&raquo;', 0, '<li class="last">|</li>'); ?>
</ul>

<?php
		}
	} elseif ($this->get('mode') == 'LOCKED') {
		echo '<p>Element is locked</p>'; //TODO: Localization
	} else {
		echo '<p>%%%no_rights_browse%%%</p>';
	}
?>