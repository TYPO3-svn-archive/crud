<script type="text/javascript">
$(document).ready( function(){ 
	$('#slidernews').innerfade({ speed: 'slow', timeout: 4000, type: 'sequence', containerheight: '220px' }); 
} ); 
</script> 
<?php 
$config = $this->controller->configurations->getArrayCopy();
//$this->loadFooterData('libraries', 'jquery');//include jquery 
//$this->loadFooterData('libraries', 'innerfade');//include jquery 
$page_url = t3lib_div::getIndpEnv(TYPO3_REQUEST_URL);
if ($this->get('mode') == 'PROCESS') { //check the mode. if PROCESS than all ok
	$newslist = $this->renderPreview($this->get('data')); //renders the preview of the data
	$config = $this->controller->configurations->getArrayCopy();?>
	<ul id="slidernews">
	<?php foreach ($newslist as $uid=>$news) { //loop for the news data
		if (empty($news['short'])) {
			$news['short'] = $news['bodytext']; //if the news short empty we take the news bodytext as short
		}
		$news['short'] = $this->getCropText($news['short'], 100, 20); // we crop the text length
		$this->exchangeArray($news); ?>
		<li>
			<div class="csc-textpic csc-textpic-center csc-textpic-below csc-textpic-border">
				<div class="csc-textpic-text">
					<p><?php $this->printAsRaw("title");?></p>
					<p class="more"> <?php $this->printAsSingleLink($uid, 'mehr >>', 0, 'retrieve', $news['single_pid'], 0);?></p>
				</div>
				<div style="width: 194px;" class="csc-textpic-imagewrap">
					<dl style="width: 194px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol">
						<dt><?php if(!empty($news['image'])) $this->printAsImage('image', 100, 100, $this->get('imagealttext'), 0, 1);?></dt>
					</dl>
				</div>
			</div>
		</li>
		
	<?php }?>
	</ul>
<?php }?>


