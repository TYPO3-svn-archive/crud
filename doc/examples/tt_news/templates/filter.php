<?php
$config = $this->controller->configurations->getArrayCopy();
$existingValues = $config['view.']['existingValues']['tt_news'];

$recentNews = $config['view.']['counter'];
//t3lib_div::debug($existingValues);
//t3lib_div::debug($recentNews);;
$pars = $this->controller->parameters->getArrayCopy();

?>

<div class="infobox infobox-restrained">

<?php

if (is_array($filter = $this->getActiveFilters())) {
	echo '<h4>%%%active-filter%%%</h4>' . "\n\t" . '<ul class="activeFilters">' . "\n\t";
	foreach ($filter as $label=>$values) {
		echo '<li><strong>' . $this->getLL($config['view.']['setup'][$label]['label']) . ':</strong>';
		foreach ($values as $entry) {
			foreach ($entry as $url=>$name) {
				echo ' <a href="' . $url . '">' . $name . '</a>';
			}
		}
		echo '</li>' . "\n\t" ;
	}
	echo '</ul>' . "\n";
}
?>

	<h3>%%%select-filter%%%</h3>
	<dl id="filter-hersteller" class="filter">
	<?php
		if (count($existingValues['category']) > 1 && !isset($pars['search']['category']['mm'])) {
	?>
		<dt>%%%select-branches%%%</dt>
		<dd>
			<?php
				$this->printAsFilterList('tt_news', 'category', '%%%themes%%', $config['setup.']['pidBrowse'], 0, 8, '<ul class="two-margins clearfix">|</ul>', '<li>|</li>');
			?>
			<div id="filterselect-news">
				<?php $this->printAsFilterSelect('tt_news', 'category', '%%%additional-branches%%%', $config['setup.']['pidBrowse'], 8, 30); ?>
			</div>
		</dd>
	<?php
		} // end count-branches

		if (count($existingValues['author']) > 1) { 
	?>
			<dt>%%%authors%%%</dt>
			<dd>
			<?php
				$this->printAsFilterList('tt_news', 'author', '%%%authors%%%', $config['setup.']['pidBrowse'], 0, 6, '<ul class="two-margins clearfix">|</ul>', '<li>|</li>');
			?>
			</dd>
		<?php
		}
		
		if (count($existingValues['datetime']) > 1) { 
		?>
	<dt>%%%select-period%%%</dt>
	<dd>
		<?php
			if (is_array($dates=$this->getTimeLine('tt_news', 'datetime'))) {
				echo "\n" . '<ul class="two-margins clearfix">';
				foreach ($dates as $year=>$months) {
					echo '<li>' . $year . '<ul>';
					foreach ($months as $month=>$count) {
						$params = array();
						if (is_array($pars['search'])) {
							$params['search'] = $pars['search'];
						}
						$params['search']['datetime']['min'] = mktime(0, 0, 0, $month, 1, $year);
						$params['search']['datetime']['max'] = mktime(0, 59, 23, $month, 31, $year);
						if (strlen($params['search']['datetime']['min']) > 6) {
							echo "\n\t" . '<li><a href="' . $this->getUrl($params,$GLOBALS['TSFE']->id, 1) . '">' . date('M', $params['search']['datetime']['min']) . ' (' . $count . ')' . '</a></li>';
						}
					}
					echo '</ul>' . "\n\t" . '</li>' . "\n";
				}
				echo '</ul>';
				$i++;
			}
		?>
	</dd>
	<?php 
		} 
	?>
		
	</dl>
</div>
<?php
	if (is_array($recentNews)){
?>
	<div class="infobox">
		<h3>%%%most-read-news%%%</h3>
		<ul style="list-style:none;margin-left:0;">
	<?php // TODO: CSS auslagern
		$i = 0;
	 	foreach ($recentNews as $uid=>$recent) {
			if ($i > 4) {
				break;
			}
			echo '<li style="margin-bottom: 1.5em;">';
			$this->printAsSingleLink($uid, $recent['data']['title'], 0, 'retrieve', $config['setup.']['singlePid'], 0);
			echo '<br />' . $recent['count'].' %%%quantity-read%%%';
			echo '<br />%%%author%%% ' . $recent['data']['author'];
			echo '</li>';
			$i++;
		}
	?>
		</ul>
	</div>
<?php
	}
?>
