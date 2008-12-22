<?php
if ($this->get('mode') == 'PROCESS') {
	//t3lib_div::debug($this->controller->configurations->getArrayCopy(),"Setup Browse");
	$entryList = $this->get("data");
	$config = $this->controller->configurations->getArrayCopy();
	//$entryList->renderPreview($entryList);
	$entryList=$config['view.']['data'];
	$typoscript = $config['view.'];
	
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
	$this->loadHeaderData("css","tables");
	$this->loadHeaderData("css","forms");
	$this->loadHeaderData("libraries","jquery");
	$this->loadHeaderData("libraries","jquery-forms");
	$this->loadHeaderData("libraries","crudscript");
	if (!$typoscript['count']) {
		echo "%%%error_nothing-found%%%";
	} else {
		?>

<p style="float: left;">%%%resultsProPage%%%</p>
		<?php $this->printAsLimit(); ?>
<br style="clear: left;" />
		<?php $this->printAsSearch(); $this->printAsNoSearch(); ?>
<p>%%%youbrowse%%% <?php echo $now . " - " . $to . " %%%from%%% " . $typoscript['count'] ?>
%%%records%%%</p>

<table class="crud-browser-table">
	<thead>
		<tr>
			<td>&nbsp;</td>
			<?php
			foreach($typoscript['setup'] as $fieldName=>$fieldSetup) {
				echo '<th scope="col">' . "\n\t";
				//echo $this->getLLfromKey("tx_partner_main.first_name","EXT:partner/locallang.php");
				$this->printAsSorting($fieldName,$this->getLL($fieldSetup['label']));
				//				echo $this->getLL($fieldSetup['label']);
				echo "</th>". "\n";
			}
			?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td>&nbsp;</td>
			<?php
			foreach($typoscript['setup'] as $fieldName=>$fieldSetup) {
				echo '<th scope="col">' . "\n\t";
				$this->printAsSorting($fieldName,$this->getLL($fieldSetup['label']));
				//				echo $this->getLL($fieldSetup['label']);
				echo "</th>". "\n";
			}
			?>
		</tr>
	</tfoot>
	<tbody>
	<?php
	$row = 1;
	foreach($entryList as $uid=>$record) {
		($row % 2 != 0) ? $rowclass = ' odd' : $rowclass = ' even'; //TODO: als funktion auslagern
		echo '<tr class="' . $rowclass . '">' . "\n\t";
		echo "<td>";
		$this->printAsSingleLink($uid);
		echo "</td>" . "\n\t";
		$td=0;
		foreach($typoscript['setup'] as $fieldName=>$fieldSetup) {
			if($td==0)echo '<td><a href="' .$this->printAsSingleLink($uid,$record[$fieldName],1).'">'.$record[$fieldName].'</a></td>';
			else echo "<td>" .$record[$fieldName]."</td>";
			$td++;
		}
		echo "</tr>" . "\n";
		$row++;
	}
	//t3lib_div::debug($typoscript);
	?>
	</tbody>
</table>

<ul
	class="pagebrowser clearfix">
	<!-- TODO: prev: active-first, inactive-first, active-prev, inactive-prev; pages: less-pages, page, current-page, more-pages; next: active-next, inactive-next, active-last, inactive-last -->
	<li class="first"><?php $this->printAsReverse(); ?></li>
	<li class="prev"><?php $this->printAsBegin(); ?></li>
	<li class="pages"><?php $this->printAsBrowse(); ?></li>
	<li class="next"><?php $this->printAsForward(); ?></li>
	<li class="last"><?php  $this->printAsEnd(); ?></li>
</ul>	<?php }
}
else {
	echo "%%%no_rights_browse%%%";
} ?>