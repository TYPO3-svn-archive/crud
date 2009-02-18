<?php 
if (!defined ('TYPO3_MODE')) die ('Access denied.');
if ($this->get('mode') == 'PROCESS') {
	$config = $this->controller->configurations->getArrayCopy();
	$params = $this->controller->parameters->getArrayCopy();
	$request = $params;
	$histDateArray = tx_crud__histories::getHistoryDates();
	if(count($histDateArray) > 1) {
		$fieldName1 = $this->getDesignator()."[showhistory]";
		$fieldName2 = $this->getDesignator()."[compareWith]";
		$historyCount = count($histDateArray) - 1;
		if(isset($request['showhistory']))
			$showHistory = $request['showhistory'];
		else
			$showHistory = -1;
		if(isset($request['compareWith']))
			$compareWith = $request['compareWith'];
		else
			$compareWith = 0;
		echo "historie-eintraege:" . $historyCount ."<br/>\nZeige Unterschied zw Versionen: <br/>\n";
		echo '<form method="post" action="' . $this->getUrl($params) . '"><table><tr><td>';
  		echo '<input type="hidden" name="ajaxTarget" value="' . $this->getAjaxTarget("showHistory") . '"/>';
		for($i = -1; $i < count($config['view.']['histories']); $i++) {
			if($i == $showHistory)
				echo '<tr><td><input class="showhistory" type="radio" name="' .$fieldName1. '" value="' . $i.'" checked="checked"/>Eintrag vom ' .	$histDateArray[$i] . '</td>';
			else echo '<tr><td><input class="showhistory" type="radio" name="' .$fieldName1. '" value="' . $i.'"/>Eintrag vom ' . $histDateArray[$i] . '</td>';
			if($i == $compareWith)
				echo '<td><input class="comparewith" type="radio" name="' .$fieldName2. '" value="' . $i . '" checked="checked"/>Eintrag vom ' . $histDateArray[$i] . '</td></tr>';
			else echo '<td><input class="comparewith" type="radio" name="' .$fieldName2. '" value="' . $i . '"/>Eintrag vom ' . $histDateArray[$i] . '</td></tr>';
			
		}
		echo '</table><input type="submit"/></form>';
		$diffArray = tx_crud__histories::getHistoryDiff($request['compareWith'], $request['showhistory']);
		if(is_array($diffArray)) {
			foreach($diffArray AS $diffKey => $diffValue)
				echo "<p>" . $diffKey . ": " . $diffValue . "</p>";			
		}
	}
	unset($params['history']);
	unset($params['showhistory']);
	unset($params['compareWith']);
	$params['ajaxTarget']=$this->getAjaxTarget("retrieve");
	echo '<a href="' . $this->getUrl($params) . '">zurueck zum Datensatz</a>';
}



?>
