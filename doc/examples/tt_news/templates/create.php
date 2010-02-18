<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');
$params = $this->controller->parameters->getArrayCopy();
$conf = $this->controller->configurations->getArrayCopy();
///t3lib_div::Debug($conf);
$this->loadHeaderData('css', 'datepicker');
$this->loadHeaderData('css', 'forms');
$this->loadHeaderData('css', 'news');//add the tt_news css
//$this->loadFooterData('libraries', 'jquery');//include jquery 
//$this->loadFooterData('libraries', 'jquery-forms');//include jquery forms lib needed by the autocomplete
//$this->loadFooterData('libraries', 'crudscript');//include the crud js for ajax loading and browser histories
$page_url = t3lib_div::getIndpEnv(TYPO3_REQUEST_URL);

if ($this->get('mode') == 'ICON') {
	echo '<h2><span>%%%h2-create-news%%%</span></h2>
	<p>%%%create-news-info-1%%%</p>
	<p>%%%create-news-info-2%%%</p>';
	$this->printAsActionLink('%%%create-news%%%', 'create');
} elseif ($this->get('mode') == 'EDIT') {
	$entryList = $this->get('setup');
	//t3lib_div::debug($entryList);
	$entryList = $this->renderSetup($entryList);
	foreach ($entryList as $tab=>$data) {
		foreach ($data as $section=>$element) {
			foreach ($element as $item_key=>$entry) {
    			$form[$item_key] = $entry;
			}
		}
    }
	
	$this->printAsFormHeader(false, 'yform');
?>
	<div id="crud-tabs-form">
		<h3>Ihre Neuigkeit:</h3>
		<ul>
			<li><a href="<?php echo $page_url?>#fragment-1"><span>%%%generally%%% <sup class="requiredflag">*</sup></span></a></li>
			<li><a href="<?php echo $page_url?>#fragment-2"><span>%%%text%%% <sup class="requiredflag">*</sup></span></a></li>
			<li><a href="<?php echo $page_url?>#fragment-3"><span>%%%images%%% </span></a></li>
			<li><a href="<?php echo $page_url?>#fragment-4"><span>%%%categories%%% <sup class="requiredflag">*</sup></span></a></li>
		</ul>
		<div id="fragment-1">
			<fieldset class="crud-section">
				<div class="type-text<?php if ($form['title']['error']) echo ' error'; ?>">
					<label for="title">Titel der Neuigkeit: <sup class="requiredflag">*</sup></label>
					<?php if ($form['title']['error']) echo '<strong class="message">' . $this->getFormError($form['title']['error'], 'title') . '</strong>'; ?>
					<?php echo $form['title']['html']; ?>
				</div>
				<div class="type-text<?php if ($form['datetime']['error']) echo ' error'; ?>">
					<label for="datetime">Datum der Publikation:</label>
					<?php if ($form['datetime']['error']) echo '<strong class="message">' . $this->getFormError($form['datetime']['error'], 'datetime') . '</strong>'; ?>
					<?php echo $form['datetime']['html']; ?>

					<p><small>%%%news-date-publish%%%</small></p>

				</div>
				<div class="type-text<?php if ($form['keywords']['error']) echo ' error'; ?>">
					<label for="keywords">Stichworte:</label>
					<?php if ($form['keywords']['error']) echo '<strong class="message">' . $this->getFormError($form['keywords']['error'], 'keywords') . '</strong>'; ?>
					<?php echo $form['keywords']['html']; ?>
					<p><small>%%%news-keywords%%</small></p>
				</div>
				<div class="type-select<?php if ($form['category']['error']) echo ' error'; ?>">
					<label for="category">Art der Neuigkeit: <sup class="requiredflag">*</sup></label>
					<?php if ($form['category']['error']) echo '<strong class="message">' . $this->getFormError($form['category']['error'], 'category') . '</strong>'; ?>
					<?php echo $form['category']['html']; ?>
				</div>
			</fieldset>
		</div>
		
		<div id="fragment-2">
			<fieldset class="crud-section">
				<div class="type-text<?php if ($form['short']['error']) echo ' error'; ?>">
					<label for="short">Anreißer:</label>
					<?php if ($form['short']['error']) echo '<strong class="message">' . $this->getFormError($form['short']['error'], 'bodytext') . '</strong>'; ?>
					<?php echo $form['short']['html']; ?>
					<p><small>%%%news-teaser%%%</small></p>
				</div>
				<div class="type-text<?php if ($form['bodytext']['error']) echo ' error'; ?>">
					<label for="bodytext">Text Ihrer Nachricht: <sup class="requiredflag">*</sup></label>
					<?php if ($form['bodytext']['error']) echo '<strong class="message">' . $this->getFormError($form['bodytext']['error'], 'bodytext') . '</strong>'; ?>
					<?php echo $form['bodytext']['html']; ?>
				</div>
				<div class="type-text<?php if ($form['tx_yellowmed_conflict']['error']) echo ' error'; ?>">
					<label for="tx_yellowmed_conflict">Interessenkonflikt:</label>
					<?php if ($form['tx_yellowmed_conflict']['error']) echo '<strong class="message">' . $this->getFormError($form['tx_yellowmed_conflict']['error'], 'bodytext') . '</strong>'; ?>
					<?php echo $form['tx_yellowmed_conflict']['html']; ?>
					<p><small>
						<div class="pdf first odd last">
							<span>%%%news-info</span>
						</div>
					</small></p>
				</div>
			</fieldset>
		</div>
		
		<div id="fragment-3">
			<fieldset class="crud-section">
				<div class="type-text<?php if ($form['image']['error']) echo ' error'; ?>">
					<label for="image">Bilder:</label>
					<?php if ($form['image']['error']) echo '<strong class="message">' . $this->getFormError($form['image']['error'], 'image') . '</strong>'; ?>
					<?php echo $form['image']['html']; ?>
				</div>
				<div class="type-text<?php if ($form['imagecaption']['error']) echo ' error'; ?>">
					<label for="imagecaption">Bildunterschrift:</label>
					<?php if ($form['imagecaption']['error']) echo '<strong class="message">' . $this->getFormError($form['imagecaption']['error'], 'imagecaption') . '</strong>'; ?>
					<?php echo $form['imagecaption']['html']; ?>
				</div>
			</fieldset>
		</div>
		
		<div id="fragment-4">
			<fieldset class="crud-section">
				<div class="type-select<?php if ($form['tx_yellowmed_specialism']['error']) echo ' error'; ?>">
					<label for="tx_yellowmed_specialism">Kategorie: <sup class="requiredflag">*</sup></label>
					<?php if ($form['tx_yellowmed_specialism']['error']) echo '<strong class="message">' . $this->getFormError($form['tx_yellowmed_specialism']['error'], 'tx_yellowmed_specialism') . '</strong>'; ?>
					<?php echo $form['tx_yellowmed_specialism']['html']; ?>
				</div>
				<div class="type-select<?php if ($form['tx_yellowmed_infos']['error']) echo ' error'; ?>">
					<label for="tx_yellowmed_specialism">Kategorie: <sup class="requiredflag">*</sup></label>
					<?php if ($form['tx_yellowmed_infos']['error']) echo '<strong class="message">' . $this->getFormError($form['tx_yellowmed_infos']['error'], 'tx_yellowmed_infos') . '</strong>'; ?>
					<?php echo $form['tx_yellowmed_infos']['html']; ?>
				</div>
			</fieldset>
		</div>
	</div>
<?php	
		$this->enableTabs('#crud-tabs-form', $entryList);
?>
		<div class="submit">
			<input type="hidden" name="<?php echo $this->getDesignator();?>[author]" value="<?php echo $GLOBALS['TSFE']->fe_user->user['username'];?>" />
			<input type="hidden" name="<?php echo $this->getDesignator();?>[author_email]" value="<?php echo $GLOBALS['TSFE']->fe_user->user['email'];?>" />		
			<?php $this->printAsFormSubmit('Meldung jetzt eintragen', '<span>|</span>', 'button', 1); ?>
		</div>



<?php 
		$this->printAsFormFooter();
?>
<?php
	} elseif ($this->get('mode') == 'PROCESS') {
		echo '%%%news-creating%%%';
		mail('support@yourDomain.de', 'Neue Meldung eingetragen', $GLOBALS['TSFE']->fe_user->user['username'] . ' (' . $GLOBALS['TSFE']->fe_user->user['email'] . ') hat eine neue Meldung erstellt', 'From: admin@yellowmed.com');
	} elseif ($this->get('mode') == 'HIDE') {
		echo '';
	} elseif ($this->get('mode') == 'NO_RIGHTS') {
		echo 'no_rights_create';
	} else {
		echo '%%%update_record_failed%%%';
	}
?>
