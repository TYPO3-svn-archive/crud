<?php
$this->loadHeaderData('css', 'news');
$config = $this->controller->configurations->getArrayCopy();
$extras = $config['view.']['additionalData'];
$pars = $this->controller->parameters->getArrayCopy();
$data = $config['view.']['data'][$pars['retrieve']];
if ($this->get('mode') == 'PROCESS') { 
	$newslist = $this->renderPreview();
	$this->exchangeArray($newslist[$pars['retrieve']]);
?>
<div class="news">
	<h2><span><?php $this->printAsRaw('title'); ?></span></h2>

	<?php if ($this->get('short')) { ?>
		<h3><?php echo $this->printAsRaw('short') ?></h3>
	<?php } ?>

	<ul class="news-meta clearfix">
		<li class="news_date"><em>%%%datetime%%%:</em> <?php echo strftime($this->getLL('LLL:EXT:crud/locallang.xml:dateTCA.output'),$this->get('crdate')); ?></li>
		<li class="news_author"><em>%%%author%%%:</em> <?php $this->printAsRaw('author'); ?></li>
		<?php if (tx_crud__log::getLogUserCount('retrieve')) { ?>
			<li class="news_views">
				<em>%%%timesvisited%%%:</em> <?php echo tx_crud__log::getLogUserCount('retrieve') . ' %%%times%%%';
				if (tx_crud__log::getLastLogUser('retrieve')) {
					echo ', %%%atlast%%% %%%by%%% ' . tx_crud__log::getLastLogUser('retrieve');
				} else {
					echo ', %%%atlast%%% %%%by%%% %%%retrieve_guest%%%';
				} ?>
			</li>
		<?php } ?>
	</ul>

	<div class="news_content">

	<?php
		if ($this->get('image')) { // if image available
	?>
			<div class="csc-textpic csc-textpic-intext-right">
				<div class="csc-textpic-imagewrap">
					<dl class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol">
						<dt>
							<?php $this->printAsImage('image', 250, 250, $this->get('imagealttext'), false, false, '1'); //if the news has images ?>
						</dt>
						<?php
							if ($this->get('imagecaption')) {
								echo '<dd class="csc-textpic-caption">' . "\n\t" . '<p class="csc-caption">' . $this->get('imagecaption') . '</p>' . "\n" . '</dd>';//if the news iamge has an caption
							}
						?>
					</dl>
				</div>
			</div>
	<?php } // if image available - end ?>
	
	<?php $this->printAsRte('bodytext'); ?>

	<?php
		if ($this->get('tx_yellowmed_conflict')) { // if interests conflict available
	?>
					<dl class="news_interest-conflict">
						<dt>%%%interest-conflict%%%</dt>
							<dd><?php $this->printAsRaw('tx_yellowmed_conflict'); ?></dd>
					</dl>
	<?php } // if  interests conflict available - end ?>

	</div>
	<?php
		if ($this->get('links')) {
			$links = explode("\n", $this->get('links')); ?>
		<div class="news_links">
			<h4>%%%links%%%:</h4>
				<ul>
				<?php foreach ($links as $link) {
					if(strlen($link)>=3) {
						if (! (strstr($link, 'http://') == $link)) {
							$link = 'http://' . $link;
						}
						$link = explode('|',$link);
						if (! isset($link[1])) {
							$label = $link[0];
						} else {
							$label = $link[1];
						}
						echo'<li><a href="' . $link[0] . '" class="exturl">' . $label . '</a></li>';
					}
				}
			?>
		</ul>
	</div>
<?php } ?>

<?php 
	if ($this->get('related')) { // if related news available
?>
	<div class="news_related">
		<h4>%%%relatednews%%%:</h4>
		<ul>
			<?php 
				$related = $this->printAsOptionLinks('related', '<li>|</li>', 1);
				foreach ($related as $key=>$array) {
					echo "\n\t" . '<li><a href="' . $array['url'] . '" class="inturl">' . $array['label'] . "</a>";
					if ($extras['related'][$key]['author']) {
						echo ' von ' . $extras['related'][$key]['author'] . ' geschrieben am ' . strftime($this->getLL('LLL:EXT:crud/locallang.xml:dateTCA.output'), $extras['related'][$key]['datetime']);
					}
					echo '</li>';
				}
			?>
		</ul>
	</div>
<?php } ?>

<?php $this->printAsBackLink('%%%back%%%', $config['setup.']['browsePid'], array(), 0); ?>

<?php
	if (tx_crud__log::getLogUserCount('update') >= 2) {
		if (($lastUpdater = tx_crud__log::getLastLogUser('retrieve')) > 1);
		else $lastUpdater = '%%%retrieve_guest%%%'; ?>
	<p class="news_edit">
		insgesamt <?php echo tx_crud__log::getLogUserCount('update')?> mal bearbeitet, zuletzt von <?php echo $lastUpdater; ?> am  <?php  echo strftime($this->getLL('LLL:EXT:crud/locallang.xml:datetimeTCA.output'), tx_crud__log::getLastLogDate('update')); ?><br />
		<div id="edit-news" class="news-edit">
		
		</div>
	</p>
<?php } ?>
<div id="edit-news" class="news-edit">
	
</div>
</div>

<?php
	} else { //FIXME: Bug
		echo '<p>Something went wrong.</p>';
	}
?>