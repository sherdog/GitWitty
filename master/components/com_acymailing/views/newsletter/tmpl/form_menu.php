<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<fieldset id="acy_form_menu">
	<div class="toolbar" id="acytoolbar" style="float: right;">
		<table><tr>
		<?php if(empty($this->mail->mailid)){ ?><td id="acybuttontemplate"><a class="modal"  rel="{handler: 'iframe', size: {x: 750, y: 550}}" href="<?php echo acymailing::completeLink("fronttemplate&task=theme",true ); ?>"><span class="icon-32-acytemplate" title="<?php echo JText::_('ACY_TEMPLATE'); ?>"></span><?php echo JText::_('ACY_TEMPLATE'); ?></a></td><?php } ?>
		<td id="acybuttontag"><a class="modal" rel="{handler: 'iframe', size: {x: 750, y: 550}}" href="<?php echo acymailing::completeLink("fronttag&task=tag&type=".$this->type,true ); ?>"><span class="icon-32-tag" title="<?php echo JText::_('TAGS'); ?>"></span><?php echo JText::_('TAGS'); ?></a></td>
		<td id="acybuttonreplace"><a onclick="javascript:submitbutton('replacetags'); return false;" href="#" class="toolbar"><span class="icon-32-replacetag" title="<?php echo JText::_('REPLACE_TAGS'); ?>"></span><?php echo JText::_('REPLACE_TAGS'); ?></a></td>
		<td id="acybuttondivider"><span class="divider"></span></td>
		<td id="acybuttonpreview"><a onclick="javascript:submitbutton('savepreview'); return false;" href="#" ><span class="icon-32-preview" title="<?php echo JText::_('ACY_PREVIEW').' / '.JText::_('SEND'); ?>"></span><?php echo JText::_('ACY_PREVIEW').' / '.JText::_('SEND'); ?></a></td>
		<td id="acybuttonsave"><a onclick="javascript:submitbutton('save'); return false;" href="#" ><span class="icon-32-save" title="<?php echo JText::_('ACY_SAVE'); ?>"></span><?php echo JText::_('ACY_SAVE'); ?></a></td>
		<td id="acybuttonapply"><a onclick="javascript:submitbutton('apply'); return false;" href="#" ><span class="icon-32-apply" title="<?php echo JText::_('ACY_APPLY'); ?>"></span><?php echo JText::_('ACY_APPLY'); ?></a></td>
		<td id="acybuttoncancel"><a onclick="javascript:submitbutton('cancel'); return false;" href="#" ><span class="icon-32-cancel" title="<?php echo JText::_('ACY_CANCEL'); ?>"></span><?php echo JText::_('ACY_CANCEL'); ?></a></td>
		</tr></table>
	</div>
	<div class="header" style="float: left;"><h1><?php echo JText::_('NEWSLETTER').' : '.@$this->mail->subject; ?></h1></div>
</fieldset>