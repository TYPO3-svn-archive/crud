<?php
$this->loadHeaderData('css', 'news-css');
$this->loadHeaderData('libraries', 'jquery');
$this->loadHeaderData('libraries', 'jquery-forms');
$this->loadHeaderData('libraries', 'jquery-ui-tabs');
$this->loadHeaderData('libraries', 'tiny-mce');
$this->loadHeaderData('libraries', 'crudscript');

$pars = $this->controller->parameters->getArrayCopy();
$config = $this->controller->configurations->getArrayCopy();
$extras = $config['view.']['additionalData'];

if ($this->get('mode') == 'PROCESS') { 
	$newslist = $this->renderPreview();
	$this->exchangeArray($newslist);
?>
<div class="news">	
	<h2><?php $this->printAsRaw('title'); ?></h2>
	<?php $this->setTitleTag('-  ' . $this->get('title'));?>
	<?php $this->printAsBackLink(); ?>
	<ul class="news-meta">
		<li class="news_date"><em>%%%datetime%%%:</em> <?php echo strftime($this->getLL('LLL:EXT:crud/locallang.xml:dateTCA.output'), $this->get('datetime')); ?></li>
		<li class="news_author"><em>%%%author%%%:</em> <?php $this->printAsRaw('author'); ?></li>
		<?php if (tx_crud__log::getLogUserCount('retrieve')) { ?>
			<li class="news_views">
				<em>%%%timesvisited%%%:</em> <?php echo tx_crud__log::getLogUserCount('retrieve') . ' %%%times%%%';
				if (tx_crud__log::getLastLogUser('retrieve')) {
					echo ' %%%atlast%%% %%%by%%%' . tx_crud__log::getLastLogUser('retrieve');
				} else {
					echo ' %%%atlast%%% %%%by%%% %%%retrieve_guest%%%';
				} ?>
			</li>
		<?php } ?>
	</ul>
	<div class="news_content">
		<h3><?php $this->printAsRaw('short'); ?></h3>
		<p><?php $this->printAsRaw('bodytext'); ?></p>
		<p>
			<?php $this->printAsImage('image', 400, 400, $this->get('imagealttext'), 0, 1); //if the news has images ?>
			<?php
				if ($this->get('imagecaption')) {
					echo '<em>' . $this->get('imagecaption') . '</em>';//if the news iamge has an caption
				} ?>
		</p>
	</div>
	<?php if ($this->get('links')) {
		$links = explode(',', $this->get('links')); ?>
		<div class="news_links">
			<h4>%%%links%%%:</h4>
				<ul>
				<?php foreach ($links as $link) {
					echo'<li><a href="' . $link . '">' . $link . '</a></li>';
				}
			?>
		</ul>
	</div>
<?php
	}
?>
<a class="news_backlink" href="#"><?php $this->printAsBackLink('%%%back%%%', 1); ?></a>
<div class="news_footer">
	<a href="#" target="_blank">%%%print%%%</a> | <a href="#">%%%search%%%</a> | <a href="#">%%%send%%%</a>
</div>
<?php 
	if ($this->get('related')) {
?>
	<div class="news_addit_info">
		<h4>%%%relatednews%%%:</h4>
		<ul>
			<?php 
			$related = $this->printAsOptionLinks('related', '<li>|</li>', 1);
			foreach ($related as $key=>$array) {
				echo '<li><a href="' . $array['url'] . '">' . $array['label'] . '</a>';
				if ($extras['related'][$key]['author']) {
					echo ' von ' . $extras['related'][$key]['author'] . ' geschrieben am ' . strftime($this->getLL('LLL:EXT:crud/locallang.xml:datetimeTCA.output'), $extras['related'][$key]['datetime']);
				}
				echo '</li>';
			} ?>
		</ul>
	</div>
<?php }?>
<?php
	if (tx_crud__log::getLogUserCount('update') >= 2) {
		if (($lastUpdater = tx_crud__log::getLastLogUser('retrieve')) > 1);
		else $lastUpdater = '%%%retrieve_guest%%%'; ?>

	<p class="news_edit">
		insgesamt <?php echo tx_crud__log::getLogUserCount('update')?> mal bearbeitet, zuletzt von <?php echo $lastUpdater?> am  <?php  echo strftime($this->getLL('LLL:EXT:crud/locallang.xml:datetimeTCA.output'), tx_crud__log::getLastLogDate('update')); ?><br />
		<div id="edit-news" class="news-edit">
			{{{UPDATE~TT_NEWS~<?php echo $pars['retrieve'];?>~PLUGIN.TT_NEWS_SHOW}}}
		</div>
	</p>
<?php }?>
</div>
<?php
	} else { //FIXME: Bug
		echo '<p>Something went wrong.</p>';
	}
?>