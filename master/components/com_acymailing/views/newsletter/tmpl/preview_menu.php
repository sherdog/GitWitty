<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<fieldset id="acy_preview_menu">
	<div class="toolbar" id="acytoolbar" style="float: right;">
		<table><tr>
		<?php if($this->mail->published == 2){ ?>
		<td id="acybuttonunschedule"><a onclick="javascript:submitbutton('unschedule'); return false;" href="#" class="toolbar" ><span class="icon-32-unschedule" title="<?php echo JText::_('UNSCHEDULE',true); ?>"></span><?php echo JText::_('UNSCHEDULE'); ?></a></td>
		<?php }else{ ?>
		<td id="acybuttonschedule"><a class="modal" rel="{handler: 'iframe', size: {x: 500, y: 400}}" href="<?php echo acymailing::completeLink("newsletter&task=scheduleconfirm&listid=".JRequest::getInt('listid')."&mailid=".$this->mail->mailid,true ); ?>"><span class="icon-32-schedule" title="<?php echo JText::_('SCHEDULE',true); ?>"></span><?php echo JText::_('SCHEDULE'); ?></a></td>
		<?php } ?>
		<td id="acybuttonsend"><a onclick="if(confirm('<?php echo JText::_('PROCESS_CONFIRMATION',true); ?>')){submitbutton('send')} return false;" href="#" class="toolbar" ><span class="icon-32-send" title="<?php echo JText::_('SEND'); ?>"></span><?php echo JText::_('SEND'); ?></a></td>
		<td id="acybuttondivider"><span class="divider"></span></td>
		<td id="acybuttonedit"><a onclick="javascript:submitbutton('edit'); return false;" href="#" class="toolbar" ><span class="icon-32-edit" title="<?php echo JText::_('ACY_EDIT'); ?>"></span><?php echo JText::_('ACY_EDIT'); ?></a></td>
		<td id="acybuttoncancel"><a onclick="javascript:submitbutton('cancel'); return false;" href="#" class="toolbar" ><span class="icon-32-cancel" title="<?php echo JText::_('ACY_CLOSE'); ?>"></span><?php echo JText::_('ACY_CLOSE'); ?></a></td>
		</tr></table>
	</div>
	<div class="header" style="float: left;"><h1><?php echo JText::_('PREVIEW').' : '.@$this->mail->subject; ?></h1></div>
</fieldset>