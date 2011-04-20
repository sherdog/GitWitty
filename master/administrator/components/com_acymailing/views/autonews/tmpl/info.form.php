<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<table class="adminform">
	<tr>
		<td>
			<label for="subject">
				<?php echo JText::_( 'JOOMEXT_SUBJECT' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[mail][subject]" id="subject" class="inputbox" style="width:80%" value="<?php echo $this->escape(@$this->mail->subject); ?>" />
		</td>
		<td>
        	<label for="published">
          	<?php echo JText::_( 'ACY_PUBLISHED' ); ?>
        	</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', "data[mail][published]" , '',$this->mail->published); ?>
		</td>
	</tr>
	<tr>
		<td>
			<label for="alias">
				<?php echo JText::_( 'JOOMEXT_ALIAS' ); ?>
            </label>
		</td>
		<td>
            <input class="inputbox" type="text" name="data[mail][alias]" id="alias" size="50" value="<?php echo @$this->mail->alias; ?>" />
		</td>
		<td>
			<?php echo JText::_( 'SEND_HTML' ); ?>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', "data[mail][html]" , 'onchange="updateAcyEditor(this.value)"',$this->mail->html); ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo JText::_( 'NEXT_GENERATE' ); ?>
		</td>
		<td>
			<?php if(empty($this->mail->senddate)) $this->mail->senddate = time(); echo JHTML::_('calendar', acymailing::getDate($this->mail->senddate,'%Y-%m-%d %H:%M'), 'data[mail][senddate]','senddate','%Y-%m-%d %H:%M','size="30"'); ?>
		</td>
		<td>
			<?php echo JText::_( 'GENERATE_FREQUENCY' ); ?>
		</td>
		<td>
			<?php echo JText::_('EVERY').' '.$this->delay->display('data[mail][frequency]',@$this->mail->frequency,3); ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo JText::_( 'ISSUE_NB' ); ?>
		</td>
		<td>
			<input class="inputbox" type="text" name="data[mail][params][issuenb]" size="10" value="<?php echo empty($this->mail->params['issuenb']) ? 1 : $this->mail->params['issuenb']; ?>" />
		</td>
		<td>
			<?php echo JText::_( 'LAST_RUN' ); ?>
		</td>
		<td>
			<input type="text" class="inputbox"  value="<?php echo acymailing::getDate(@$this->mail->params['lastgenerateddate'],'%Y-%m-%d %H:%M'); ?>" name="data[mail][params][lastgenerateddate]"/>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo JText::_( 'GENERATE_MODE' ); ?>
		</td>
		<td>
			<?php echo $this->generatingMode->display('data[mail][params][generate]',@$this->mail->params['generate']); ?>
		</td>
		<td>
			<?php echo JText::_( 'NOTIFICATION_TO' ); ?>
		</td>
		<td>
			<input class="inputbox" type="text" name="data[mail][params][generateto]" id="generateto" size="50" value="<?php echo @$this->mail->params['generateto']; ?>" /> <?php echo $this->values->editnotification; ?>
		</td>
	</tr>
</table>