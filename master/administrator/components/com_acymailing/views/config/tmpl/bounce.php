<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="page-bounce">
<br  style="font-size:1px;" />
<fieldset class="adminform">
	<legend><?php echo JText::_( 'BOUNCE_HANDLING' ); ?></legend>
		<table><tr><td>
			<table class="admintable" cellspacing="1">
				<tr>
					<td class="key">
						<?php echo JText::_('BOUNCE_ADDRESS'); ?>
					</td>
					<td>
						<?php echo $this->config->get('bounce_email'); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('BOUNCE_SERVER'); ?>
					</td>
					<td>
						<input type="text" size="40" name="config[bounce_server]" value="<?php echo $this->config->get('bounce_server',''); ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('BOUNCE_PORT'); ?>
					</td>
					<td>
						<input type="text" size="10" name="config[bounce_port]" value="<?php echo $this->config->get('bounce_port',''); ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('BOUNCE_CONNECTION'); ?>
					</td>
					<td>
						<?php echo $this->elements->bounce_connection; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('SMTP_SECURE'); ?>
					</td>
					<td>
						<?php echo $this->elements->bounce_secured; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('BOUNCE_CERTIF'); ?>
					</td>
					<td>
						<?php echo $this->elements->bounce_certif; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('BOUNCE_USERNAME'); ?>
					</td>
					<td>
						<input type="text" size="30" name="config[bounce_username]" value="<?php echo $this->config->get('bounce_username',''); ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('BOUNCE_PASSWORD'); ?>
					</td>
					<td>
						<input type="password" size="30" name="config[bounce_password]" value="<?php echo $this->config->get('bounce_password',''); ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('BOUNCE_TIMEOUT'); ?>
					</td>
					<td>
						<input type="text" size="10" name="config[bounce_timeout]" value="<?php echo $this->config->get('bounce_timeout',''); ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('BOUNCE_MAX_EMAIL'); ?>
					</td>
					<td>
						<input type="text" size="10" name="config[bounce_max]" value="<?php echo $this->config->get('bounce_max',100); ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('BOUNCE_FEATURE'); ?>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "config[auto_bounce]" , 'onclick="displayBounceFrequency(this.value);"',$this->config->get('auto_bounce',0) ); ?>
					</td>
				</tr>
			</table>
		</td>
		<td valign="bottom">
			<table id="bouncefrequency" class="admintable" cellspacing="1">
				<tr><td class="key"><?php echo JText::_('FREQUENCY'); ?></td>
				<td><?php $freqBounce = acymailing::get('type.delay'); echo $freqBounce->display('config[auto_bounce_frequency]',$this->config->get('auto_bounce_frequency',86400),3); ?></td>
				</tr>
				<tr><td class="key"><?php echo JText::_('LAST_RUN'); ?></td>
				<td><?php echo acymailing::getDate($this->config->get('auto_bounce_last')); ?></td>
				</tr>
				<tr><td class="key"><?php echo JText::_('NEXT_RUN'); ?></td>
				<td><?php echo acymailing::getDate($this->config->get('auto_bounce_next')); ?></td>
				</tr>
				<tr><td class="key"><?php echo JText::_('REPORT'); ?></td>
				<td><?php echo $this->config->get('auto_bounce_report'); ?></td>
				</tr>
			</table>
		</td>
		</tr></table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'BOUNCE_RULES' ); ?></legend>
		<table class="adminlist" cellspacing="1">
			<thead>
				<tr>
					<th>
						<?php echo JText::_('NAME'); ?>
					</th>
					<th>
						<?php echo JText::_('BOUNCE_REGEX'); ?>
					</th>
					<th>
						<?php echo JText::_('BOUNCE_ACTION'); ?>
					</th>
					<th>
						<?php echo JText::_('EMAIL_ACTION'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
		<tr class="row0"><td><?php echo JText::_('BOUNCE_FAILED'); ?></td><td><?php echo $this->config->get('bounce_regex_bounce',''); ?></td><td><?php echo $this->bounceaction->display('bounce',$this->config->get('bounce_action_bounce')); ?></td><td><?php echo $this->emailaction->display('bounce',$this->config->get('bounce_email_bounce')); ?></td></tr>
<?php $i = 1;
		$k=1;
	do{
		$rule = $this->config->get('bounce_rules_'.$i,'');
?>
	<tr class="row<?php echo $k;$k=1-$k ?>"><td><input type="text" name="config[bounce_rules_<?php echo $i?>]" value="<?php echo $this->escape($this->config->get('bounce_rules_'.$i)); ?>" size="50"/></td><td><input type="text" name="config[bounce_regex_<?php echo $i?>]" value="<?php echo $this->escape($this->config->get('bounce_regex_'.$i)); ?>" size="50"/></td>
	<td><?php echo $this->bounceaction->display($i,$this->config->get('bounce_action_'.$i)); ?></td><td><?php echo $this->emailaction->display($i,$this->config->get('bounce_email_'.$i))?></td></tr>
<?php
		$i++;
	}while(!empty($rule))
?>
			<tr class="row<?php echo $k; ?>"><td><?php echo JText::_('BOUNCE_FINAL'); ?></td><td><?php echo $this->config->get('bounce_regex_end',''); ?></td><td></td><td><?php echo $this->emailaction->display('end',$this->config->get('bounce_email_end'))?></td></tr>
			</tbody>
		</table>
	</fieldset>
</div>