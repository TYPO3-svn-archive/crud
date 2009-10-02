<?php
	if (!defined ('TYPO3_MODE')) die ('Access denied.');
	if ($this->get('mode') == 'EDIT') {
	$entryList = $this->get('setup');
	$entryList = $this->renderSetup($entryList);
	$this->printAsFormHeader();
?>
<div id="crud-tabs-form">
	<ul>
		<li><a href="<?php echo $this->baseUrl?>#fragment-1"><span>Neuigkeit</span></a></li>
		<li><a href="<?php echo $this->baseUrl?>#fragment-2"><span>Medien</span></a></li>
		<li><a href="<?php echo $this->baseUrl?>#fragment-3"><span>Relationen</span></a></li>
		<li><a href="<?php echo $this->baseUrl?>#fragment-4"><span>Infos und Zeiten</span></a></li>
	</ul>
	<div id="fragment-1">
		<fieldset class="crud-section">
			<legend>die komplette Neuigkeit</legend>
			<dl>
				<dt>
					<label>Neuigkeitentyp</label>
				</dt>
				<dd>
					<?php echo $entryList['news']['content']['type']['html']; ?>
				</dd>
				<dt>
					<label>Titel</label>
				</dt>
				<dd>
					<?php echo $entryList['news']['content']['title']['html']; ?>
				</dd>
				<dt>
					<label>Intro</label>
				</dt>
				<dd>
					<?php echo $entryList['news']['content']['short']['html']; ?>
				</dd>
				<?php
					if ($entryList['news']['content']['bodytext']['html']) { ?>
						<dt>
							<label>Text</label>
						</dt>
						<dd>
							<?php echo $entryList['news']['content']['bodytext']['html']; ?>
						</dd>
				<?php
					}
				?>
			</dl>
		</fieldset>
	</div>
	<div id="fragment-2">
		<fieldset class="crud-section">
			<legend>Bilder zur Neuigkeit</legend>
			<dl>
				<dt>
					<label>Dateien</label>
				</dt>
				<dd>
					<?php echo $entryList['media']['images']['image']['html']; ?>
				</dd>
				<dt>
					<label>Infos zu den Bildern</label>
				</dt>
				<dd>
					<label>Bildtexte</label>
					<?php echo $entryList['media']['images']['imagecaption']['html']; ?>
					<?php
						if ($entryList['media']['images']['imagealttext']['html']) { ?>
							<label>Alternativtexte zu den Bildern</label>
							<?php echo $entryList['media']['images']['imagealttext']['html'];
						}
					?>
					<?php
						if ($entryList['media']['images']['imagetitletext']['html']) { ?>
							<label>Titeltexte zu den Bildern</label>
							<?php echo $entryList['media']['images']['imagetitletext']['html'];
						}
					?>
				</dd>
			</dl>
		</fieldset>
		<?php if ($entryList['media']['files']['news_files']['html']) { ?>
		<fieldset class="crud-section">
			<legend>Dateianhänge</legend>
			<dl>
				<dt>
					<label>Dateien</label>
				</dt>
				<dd>
					<?php echo $entryList['media']['files']['news_files']['html']; ?>
				</dd>
			</dl>
		</fieldset>
		<?php }?>
	</div>
	<div id="fragment-3">
		<fieldset class="crud-section">
			<legend>Kategorie der Neuigkeit</legend>
			<dl>
				<dt>
					<label>Auswahl</label>
				</dt>
				<dd>
					<?php echo $entryList['relations']['category']['category']['html']; ?>
				</dd>
			</dl>
		</fieldset>
		<fieldset class="crud-section">
			<legend>Links zu Themen der Neuigkeit</legend>
			<dl>
				<?php if ($entryList['relations']['links']['links']['html']) { ?>
				<dt>
					<label>interessante Links zu der Neuigkeit</label>
				</dt>
				<dd>
					<?php echo $entryList['relations']['links']['links']['html']; ?>
				</dd>
				<?php } ?>
				<?php if ($entryList['relations']['links']['page']['html']) { ?>
				<dt>
					<label>Interne Seite zur Neuigkeit</label>
				</dt>
				<dd>
					<?php echo $entryList['relations']['links']['page']['html']; ?>
				</dd>
				<?php } ?>
				<?php if ($entryList['relations']['links']['ext_url']['html']) { ?>
				<dt>
					<label>Externe Seite zur Neuigkeit</label>
				</dt>
				<dd>
					<?php echo $entryList['relations']['links']['ext_url']['html']; ?>
				</dd>
				<?php } ?>
			</dl>
		</fieldset>
		<?php if($entryList['relations']['related']['related']['html']) { ?>
		<fieldset class="crud-section">
			<legend>Verwandte Neuigkeit</legend>
			<dl>
				<dt>
					<label>Auswahl</label>
				</dt>
				<dd>
					<?php echo $entryList['relations']['related']['related']['html']; ?>
				</dd>
			</dl>
		</fieldset>
		<?php }?>
	</div>
	<div id="fragment-4">
		<fieldset class="crud-section">
			<legend>Infos zum Autor der Neuigkeit</legend>
			<dl>
				<dt>
					<label>Autor</label>
				</dt>
				<dd>
					<label>Name</label>
					<?php echo $entryList['infos']['author']['author']['html']; ?>
					<label>E-Mail</label>
					<?php echo $entryList['infos']['author']['author_email']['html']; ?>
				</dd>
			</dl>
		</fieldset>
		<fieldset class="crud-section">
			<legend>Zeiten</legend>
			<dl>
				<dt>
					<label>Veröffentlichung </label>
				</dt>
				<dd>
					<?php echo $entryList['infos']['dates']['datetime']['html']; ?>
				</dd>
				<dt>
					<label>Archivierung</label>
				</dt>
				<dd>
					<?php echo $entryList['infos']['dates']['archivedate']['html']; ?>
				</dd>
			</dl>
		</fieldset>
		<fieldset class="crud-section">
		<legend>Stichworte</legend>
			<dl>
				<dt>
					<label>Stichworte</label>
				</dt>
				<dd>
					<?php echo $entryList['infos']['keywords']['keywords']['html']; ?>
				</dd>
			</dl>
		</fieldset>
	</div>
	<?php $this->enableTabs('#crud-tabs-form', $entryList);?>
	<?php $this->printAsFormSubmit('Neugkeit aktualisieren');?>
</div>
<?php $this->printAsFormFooter();?>
<?php $this->printAsFormCancel();?>
	
<?php
	} elseif ($this->get('mode') == 'ICON') {
		$this->printActionLink('update');
	} elseif ($this->get('mode') == 'PROCESS') {
		echo '%%%create_preview%%%' . $this->printAsExitLink('%%%back%%%', 0);
	} elseif ($this->get('mode') == 'HIDE') {
		echo '';
	} elseif ($this->get('mode') == 'NO_RIGHTS') {
		echo '<p>no rights to create</p>';
	} else {
		echo '<p>%%%update_record_failed%%%</p>';
	}
?>