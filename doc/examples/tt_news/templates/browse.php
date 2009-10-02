<?php 
$this->loadHeaderData('css', 'forms');
$this->loadHeaderData('css', 'autocomplete');//add the css for the autocomplete search
$this->loadHeaderData('css', 'news');//add the tt_news css
$this->loadHeaderData('libraries', 'jquery');//include jquery 
$this->loadHeaderData('libraries', 'jquery-forms');//include jquery forms lib needed by the autocomplete
$this->loadHeaderData('libraries', 'jquery-autocomplete');//include the autocomplete lib
$this->loadHeaderData('libraries', 'crudscript');//include the crud js for ajax loading and browser histories

$config = $this->controller->configurations->getArrayCopy();

if ($this->get('mode') == 'PROCESS') { //check the mode. if PROCESS than all ok
	$newslist = $this->renderPreview($this->get('data')); //renders the preview of the data
	$config = $this->controller->configurations->getArrayCopy();
	//t3lib_div::Debug($config['view.']['counter']);
?>
	<h2><span>%%%h2-news-browse%%%</span></h2>
	<?php
		$this->printAsSearch('%%%newsSearch%%%', '', 'autocomplete', 0) . $this->printAsNoSearch(); 
		if ($config['view.']['count'] < 1) {
			echo '<p>%%%news-not-found%%</p>';
		} else {
		//prints the searchbox
	?>
	%%%news-sorted-by%%% <?php $this->printAsSorting("title","%%%title%%%");?> : <?php $this->printAsSorting("author","%%%author%%%");?>
	<div class="news-list">
		<?php foreach ($newslist as $uid=>$news) { //loop for the news data
			if (empty($news['short'])) {
				$news['short'] = $news['bodytext']; //if the news short empty we take the news bodytext as short
			}
			$news['short'] = $this->getCropText($news['short'], 500, 20); // we crop the text length
			$this->exchangeArray($news); //set the singlenews data to the view
			if ($i == 3) { //every 3 news we set an extra style and reset the counter
				$first = 'first';
				$i = 0;
			} else {
				$first = '';
			}
		?>
		<div class="entry clearfix <?php echo $first . ' ' . $video; ?>">
			<p class="meta"><?php echo strftime($this->getLL('LLL:EXT:crud/locallang.xml:dateTCA.output'),$this->get('crdate')); ?></p>
			<h3><?php $this->printAsSingleLink($uid,$this->get('title'), 0, 'retrieve', $config['setup.']['singlePid'], 0); //prints a single link with the title as label ?></h3>
			<p class="meta">%%%category%%% <?php echo $news['category'];?> </p>
			<p class="meta">%%%author%%% <?php echo $news['author'];?> </p>
			<p>
				<?php $this->printAsImage('image', 100, 100, $this->get('imagealttext'), 0, 1); //if the news has images ?>
				<?php echo strip_tags($this->get('short')); //the news short ?>
				 <?php $this->printAsSingleLink($uid, '%%%more%%%', 0, 'retrieve', $config['setup.']['singlePid'], 0); //singlelink again with text ?>
			</p>
		</div>
		<?php $i++; } //close the loop ?>
	</div>
	<ul class="pagebrowser clearfix">
		<?php $this->printAsBegin('&laquo;', 0, '<li class="first">|</li>'); ?>
		<?php $this->printAsReverse('%%%prev%%%', 0, '<li class="prev">|</li>'); ?>
		<?php $this->printAsBrowse($pages='3|3', $label=''); ?>
		<?php $this->printAsForward('%%%next%%%', 0, '<li class="next">|</li>'); ?>
		<?php  $this->printAsEnd('&raquo;', 0, '<li class="last">|</li>'); ?>
	</ul>
	<?php
	}
	?>
	
<?php
	} else {
		echo '<p>%%%error%%%</p>';
	}
?>
