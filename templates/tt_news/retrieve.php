<?php

$this->loadHeaderData("css","news-css");
$this->loadHeaderData("libraries","jquery");
$this->loadHeaderData("libraries","jquery-forms");
$this->loadHeaderData("libraries","jquery-ui-tabs");
$this->loadHeaderData("libraries","tiny-mce");
$this->loadHeaderData("libraries","crudscript");
$pars = $this->controller->parameters->getArrayCopy();
	
if ($this->get('mode') == 'PROCESS') { 
	$newslist = $this->renderPreview();
	$this->exchangeArray($newslist);
?>

<div class="news">	
	<h2><?php $this->printAsRaw("title"); ?></h2>

	<?php //$this->printAsBackLink(); ?>
	<a href="<?php $this->printAsBackLink("",1); ?>" class="news_backlink" title="%%%backtitle%%%">%%%back%%%</a>
	<ul class="news-meta">
		<li class="news_date"><em>%%%datetime%%%:</em> <?php echo strftime($this->getLL("LLL:EXT:crud/locallang.xml:datetimeTCA.output"),$this->get("datetime")); ?></li>
		<li class="news_author"><em>%%%author%%%:</em> <?php $this->printAsRaw("author"); ?></li>
		<?php if (tx_crud__log::getLogUserCount('retrieve')) { ?>
			<li class="news_views">
				<em>%%%timesvisited%%%:</em> <?php echo tx_crud__log::getLogUserCount('retrieve')." %%%times%%% ";
				if (tx_crud__log::getLastLogUser('retrieve')) {
					echo "%%%atlast%%% %%%by%%%".tx_crud__log::getLastLogUser('retrieve');
				} else {
					echo "%%%atlast%%% %%%by%%% %%%retrieve_guest%%%";
				} ?>
			</li>
		<?php } ?>
	</ul>
	<div class="news_content">
		<h3><?php $this->printAsRaw("short"); ?></h3>
		<p><?php $this->printAsRaw("bodytext"); ?></p>
		<p>
			<?php $this->printAsImage("image",400,400,$this->get("imagealttext"),0,1);//if the news has images ?>
			<?php
				if ($this->get("imagecaption")) {
					echo '<em>' . $this->get("imagecaption") . '</em>';//if the news iamge has an caption
				} ?>
		</p>
	</div>
	<?php if ($this->get("links")) {
		$links = explode(",",$this->get("links"));?>
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
<a class="news_backlink" href="#"><?php $this->printAsBackLink("%%%back%%%",1); ?></a>
<div class="news_footer">
	<a href="#" target="_blank">%%%print%%%</a> | <a href="#">%%%search%%%</a> | <a href="#">%%%send%%%</a>
</div>
<?php 
	if ($this->get("related")) {
?>
	<div class="news_addit_info">
		<h4>%%%relatednews%%%:</h4>
		<ul>
			<?php $this->printAsOptionLinks("related","<li>|</li>");?>
		</ul>
	</div>
<?php
	}
?>
	<p class="news_edit">
		Bearbeitet von Maik Musterman, am 24.12.2008 um 08:24 Uhr<br />
		<div id="edit-news" class="news-edit">
			{{{UPDATE~TT_NEWS~<?php echo $pars['retrieve'];?>~PLUGIN.TT_NEWS_SHOW}}}
		</div>
	</p>
</div>

<?php
	} else { //FIXME: Bug
		echo "sometinh wrong";
	}
?>