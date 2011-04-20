<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="page-security">
<br  style="font-size:1px;" />
<?php if(acymailing::level(1)) {
	?>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'CAPTCHA' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
					<?php echo JText::_('ENABLE_CATCHA'); ?>
				</td>
				<td>
					<?php
						$js = "function updateCaptcha(newvalue){";
						$js .= "if(newvalue == 0) {window.document.getElementById('captchafield').style.display = 'none'; }else{window.document.getElementById('captchafield').style.display = ''; }";
						$js .= '}';
						$captchaClass = acymailing::get('class.acycaptcha');
						if($captchaClass->available()){
							$js .='window.addEvent(\'load\', function(){ updateCaptcha('.$this->config->get('captcha_enabled',0).'); });';
							echo JHTML::_('select.booleanlist', "config[captcha_enabled]" , 'onclick="updateCaptcha(this.value)"',$this->config->get('captcha_enabled',0) );
						}else{
							$js .='window.addEvent(\'load\', function(){ updateCaptcha(0); });';
							echo '<input type="hidden" name="config[captcha_enabled]" value="0" />';
							echo $captchaClass->error;
						}
						$doc =& JFactory::getDocument();
						$doc->addScriptDeclaration( $js );
					?>
				</td>
			</tr>
		</table>
		<table id="captchafield" width="100%">
			<tr>
				<td colspan="2">
					<table class="admintable" cellspacing="1">
						<tr>
							<td class="key">
								<?php echo acymailing::tooltip(JText::_('CAPTCHA_CHARS_DESC'), JText::_('CAPTCHA_CHARS'), '', JText::_('CAPTCHA_CHARS')); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[captcha_chars]" size="100" value="<?php echo $this->config->get('captcha_chars','abcdefghijkmnpqrstwxyz23456798ABCDEFGHJKLMNPRSTUVWXYZ'); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php $secKey = $this->config->get('security_key');
								if(empty($secKey)){
									jimport('joomla.user.helper');
									$secKey = JUserHelper::genRandomPassword(30);
								}
								echo acymailing::tooltip(JText::sprintf('SECURITY_KEY_DESC','index.php?option=com_acymailing&ctrl=sub&task=optin&seckey='.$secKey), JText::_('SECURITY_KEY'), '', JText::_('SECURITY_KEY')); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[security_key]" size="50" value="<?php echo $secKey; ?>" />
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td width="50%">
					<fieldset class="adminform">
						<legend><?php echo JText::_('MODULE_VIEW'); ?></legend>
						<table class="admintable" cellspacing="1">
							<tr>
								<td class="key">
									<?php echo JText::_('CAPTCHA_NBCHAR'); ?>
								</td>
								<td>
									<input class="inputbox" type="text" name="config[captcha_nbchar_module]" size="10" value="<?php echo $this->config->get('captcha_nbchar_module',3); ?>" />
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('CAPTCHA_HEIGHT'); ?>
								</td>
								<td>
									<input class="inputbox" type="text" name="config[captcha_height_module]" size="20" value="<?php echo $this->config->get('captcha_height_module',25); ?>" />
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('CAPTCHA_WIDTH'); ?>
								</td>
								<td>
									<input class="inputbox" type="text" name="config[captcha_width_module]" size="20" value="<?php echo $this->config->get('captcha_width_module',60); ?>" />
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('CAPTCHA_BACKGROUND'); ?>
								</td>
								<td>
									<?php echo $this->elements->colortype->displayAll('captcha_background_module','config[captcha_background_module]',$this->config->get('captcha_background_module','#ffffff')); ?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('CAPTCHA_COLOR'); ?>
								</td>
								<td>
									<?php echo $this->elements->colortype->displayAll('captcha_color_module','config[captcha_color_module]',$this->config->get('captcha_color_module','#bbbbbb')); ?>
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
				<td>
					<fieldset class="adminform">
						<legend><?php echo JText::_('COMPONENT_VIEW'); ?></legend>
						<table class="admintable" cellspacing="1">
							<tr>
								<td class="key">
									<?php echo JText::_('CAPTCHA_NBCHAR'); ?>
								</td>
								<td>
									<input class="inputbox" type="text" name="config[captcha_nbchar_component]" size="10" value="<?php echo $this->config->get('captcha_nbchar_component',6); ?>" />
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('CAPTCHA_HEIGHT'); ?>
								</td>
								<td>
									<input class="inputbox" type="text" name="config[captcha_height_component]" size="20" value="<?php echo $this->config->get('captcha_height_component',25); ?>" />
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('CAPTCHA_WIDTH'); ?>
								</td>
								<td>
									<input class="inputbox" type="text" name="config[captcha_width_component]" size="20" value="<?php echo $this->config->get('captcha_width_component',120); ?>" />
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('CAPTCHA_BACKGROUND'); ?>
								</td>
								<td>
									<?php echo $this->elements->colortype->displayAll('captcha_background_component','config[captcha_background_component]',$this->config->get('captcha_background_component','#ffffff')); ?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('CAPTCHA_COLOR'); ?>
								</td>
								<td>
									<?php echo $this->elements->colortype->displayAll('captcha_color_component','config[captcha_color_component]',$this->config->get('captcha_color_component','#bbbbbb')); ?>
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
			</tr>
		</table>
	</fieldset>
	<?php
} ?>
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'ACY_FILES' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
					<?php echo acymailing::tooltip(JText::_('ALLOWED_FILES_DESC'), JText::_('ALLOWED_FILES'), '', JText::_('ALLOWED_FILES')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[allowedfiles]" size="60" value="<?php echo strtolower(str_replace(' ','',$this->config->get('allowedfiles'))); ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo acymailing::tooltip(JText::_('UPLOAD_FOLDER_DESC'), JText::_('UPLOAD_FOLDER'), '', JText::_('UPLOAD_FOLDER')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[uploadfolder]" size="60" value="<?php echo $this->config->get('uploadfolder'); ?>" />
				</td>
			</tr>
		</table>
	</fieldset>
</div>