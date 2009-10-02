<?php
if($this->get('mode') == 'PROCESS') { //check the mode. if PROCESS than all ok
	header('Content-Type: application/rss+xml');
	$newslist = $this->renderPreview($this->get('data')); //renders the preview of the data
	$config = $this->controller->configurations->getArrayCopy(); // TODO: folgende RSS-Feed-Beschreibungen in Constants auslagern, // TODO: $this->baseUrl statt domain verwenden
echo '<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<atom:link href="http://yellowmed.com/rss.xml" rel="self" type="application/rss+xml" />
		<title>yellowmed.com - Neuigkeiten aus der Medizintechnik</title>
		<link>http://yellowmed.com/news/</link>
		<description>Aktuelle Informationen aus dem medizintechnischen Bereich.</description>
		<language>de-de</language>
		<copyright>copyright by yellowmed UG (haftungsbeschränkt)</copyright>
		<pubDate>' . date(DATE_RSS,mktime(0,0,0, date('m'), date('d'), date('Y'))) . '</pubDate>
		<lastBuildDate>' . date(DATE_RSS,time()) . '</lastBuildDate>
		<generator>http://typo3.org/, TYPO3 with crud</generator>';

foreach ($newslist as $uid=>$news) { //loop for the news data
	$this->exchangeArray($news); ?>
		
		<item>
			<title><![CDATA[<?php echo strip_tags($this->get('title')); ?>]]></title>
			<author><![CDATA[<?php $this->printAsRaw('author');?>]]></author>
			<category><![CDATA[<?php $this->printAsRaw('category');?>]]></category>
			<description><![CDATA[<?php 
				if (!empty($news['short'])) {
					'<p><strong>' . $this->printAsRaw('short') . '</strong></p>';
				}
				$this->printAsRte('bodytext');
			?>]]></description>
			<pubDate><?php 
					$rssDatetime = $news['datetime'];
					echo date(DATE_RSS,$rssDatetime);
				?></pubDate>
			<link>http://yellowmed.com/<?php // TODO: $this->baseUrl statt domain verwenden
				echo $this->printAsSingleLink($uid,$this->get('title'),1,'retrieve',183,0); // TODO: RealURL-Link?></link>
			<source></source>
		</item>
<?php } ?>
	</channel>
</rss>
<?php } //close the loop ?>