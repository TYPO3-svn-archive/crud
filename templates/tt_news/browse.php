<?php 
$this->loadHeaderData('css', 'news-css'); //add the tt_news css
$this->loadHeaderData('css', 'autocomplete'); //add the css for the autocomplete serach
$this->loadHeaderData('css', 'tables');
$this->loadHeaderData('css', 'forms');
$this->loadHeaderData('libraries', 'jquery'); //include jquery lib
$this->loadHeaderData('libraries', 'jquery-forms'); //include jquery forms lib needed by the autocomplete
$this->loadHeaderData('libraries', 'jquery-autocomplete'); //include the autocomplete lib
$this->loadHeaderData('libraries', 'jquery-ui-tabs');
$this->loadHeaderData('libraries', 'tiny-mce');
$this->loadHeaderData('libraries', 'crudscript'); //include the crud js for ajax loading and browser histories

if ($this->get('mode') == 'PROCESS') { //check the mode. if PROCESS than all ok
	$newslist = $this->renderPreview($this->get('data')); //renders the preview of the data
	$config = $this->controller->configurations->getArrayCopy();
	$i = 0; //start counter for an style change every 3 records
	 echo $this->printAsFilterSelect('tx_partner_main', 'locality', 'Autor', $GLOBALS['TSFE']->id);
	 echo $this->printAsFilterSelect('tt_news', 'category', 'Kategorie', $GLOBALS['TSFE']->id);
	 echo $this->printAsFilterSelect('tt_news', 'type', 'Typ', $GLOBALS['TSFE']->id);
?>
	<h2>Unsere News</h2>
	<?php $this->printAsSearch('%%%newsSearch%%%', '', 'autocomplete', 'newscomplete') . $this->printAsNoSearch(); //prints the searchbox ?>
	<div class="news-list">
		<?php foreach ($newslist as $uid=>$news) { //loop for the news data
			if (empty($news['short'])) {
				$news['short'] = $news['bodytext']; //if the news short empty we take the news bodytext as short
			}
			$news['short'] = substr($news['short'], 0, 250); // we crop the text length
			$this->exchangeArray($news); //set the singlenews data to the view
			if ($i == 3) { //every 3 news we set an extra style and reset the counter
				$first = 'first';
				$i = 0;
			} else {
				$first = '';
			}
		?>
		<div class="entry <?php echo $first?>">
			<h3><?php $this->printAsSingleLink($uid, $this->get('title')); //prints a single link with the title as label ?></h3>
			<ul class="news-meta">
				<li>%%%datetime%%% <?php echo strftime($this->getLL('LLL:EXT:crud/locallang.xml:datetimeTCA.output'), $this->get('datetime')); //prints the news date ?></li>
				<li>%%%author%%%: <?php echo $this->get('author'); ?></li>
				<li>%%%category%%%: <?php echo $this->getSortingLink($this->get('category'), $config['view.']['setup']['category']); ?></li>
			</ul>
			<p>
				<?php $this->printAsImage('image', 100, 100, $this->get('imagealttext'), 0, 1); //if the news has images ?>
				<?php
					if ($this->get('imagecaption')) {
						echo '<em>' . $this->get('imagecaption') . '</em>'; //if the news iamge has an caption
					}
				?>
				<?php $this->printAsRaw('short'); //the news short ?>
				 [<?php $this->printAsSingleLink($uid, '%%%more%%%'); //singlelink again with text ?>&hellip;]
			</p>
		</div>
		<?php $i++; } //close the loop ?>
	</div>
	<ul class="pagebrowser">
		<li class="label">Seiten: </li>
		<?php $this->printAsBegin('&laquo;', 0, '<li class="first">|</li>'); ?>
		<?php $this->printAsReverse('%%%prev%%%', 0, '<li class="prev">|</li>'); ?>
		<?php $this->printAsBrowse($pages='6|6', $label=''); ?>
		<?php $this->printAsForward('%%%next%%%', 0, '<li class="next">|</li>'); ?>
		<?php  $this->printAsEnd('&raquo;', 0, '<li class="last">|</li>'); ?>
	</ul>
<?php
	} else {
		echo '<p>ups</p>'; //something went wrong
	}
?>