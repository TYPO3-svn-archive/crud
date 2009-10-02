<?php
$config = $this->controller->configurations->getArrayCopy();
$pars = $this->controller->parameters->getArrayCopy();
$data = $config['view.']['data'][$pars['retrieve']];
//t3lib_div::Debug($config);
if ($this->get('mode') == 'PROCESS') { 
	$newslist = $this->renderPreview();
	$this->exchangeArray($newslist[$pars['retrieve']]);
	$newslist= $newslist[$pars['retrieve']];
	?>
<div class="infobox">
	<h3>Mehr News aus:</h3>	
	<?php
		if (!empty($data['author'])) {
			echo 'Author:' . "\n" . '<ul>';
			$cats = explode(',',$data['author']);
			$labels = explode(',',$newslist['author']);
			foreach ($cats as $id=>$cat) {
				$params = array();
				$params['search']['author']['is'] = $cat;
				echo "\n\t" . '<li><a href="' . $this->getUrl($params,$config['setup.']['browsePid'],1,1) . '">' . $labels[$id] . '</a></li>';
			}
			echo "\n" . '</ul>' . "\n";
		}
	if (!empty($data['category'])) {
		echo 'Anwendungen:' . "\n" . '<ul>';
		$cats = explode(',',$data['category']);
		$labels = explode(',',$newslist['category']);
		//t3lib_div::debug($labels,"labels");
		//t3lib_div::debug($data);
		foreach ($cats as $id=>$cat) {
			$params = array();
			$params['search']['category']['mm'] = $cat;
			echo "\n\t" . '<li><a href="' . $this->getUrl($params,$config['setup.']['browsePid'],1,1) . '">' . $labels[$id] . '</a></li>';
		}
		echo "\n". '</ul>' . "\n";
	}
?>
</div>
<?php
}
?>