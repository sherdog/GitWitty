<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="unsubpage">
<form action="<?php echo JRoute::_( 'index.php' );?>" method="post" name="adminForm">
	<?php if(!empty($this->mailid)){ ?>
		<div id="unsublist_div" class="unsubdiv">
			<input type="checkbox" value="1" name="unsublist" id="unsublist" checked="checked"/>
			<label for="unsublist"><?php echo JText::_('UNSUB_CURRENT'); ?></label>
		</div>
	<?php } ?>
		<div id="unsuball_div" class="unsubdiv">
			<input type="checkbox" value="1" name="unsuball" id="unsuball" <?php if(empty($this->mailid)) echo 'checked="checked"'; ?> />
			<label for="unsuball"><?php echo JText::_('UNSUB_ALL'); ?></label>
		</div>
		<div id="unsubfull_div" class="unsubdiv">
			<input type="checkbox" value="1" name="refuse" id="refuse" />
			<label for="refuse"><?php echo JText::_('UNSUB_FULL'); ?></label>
		</div>
	<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
	<input type="hidden" name="task" value="saveunsub" />
	<input type="hidden" name="ctrl" value="user" />
	<input type="hidden" name="subid" value="<?php echo $this->subscriber->subid; ?>" />
	<input type="hidden" name="key" value="<?php echo $this->subscriber->key; ?>" />
	<input type="hidden" name="mailid" value="<?php echo $this->mailid; ?>" />
	<input type="hidden" name="Itemid" value="<?php echo JRequest::getInt('Itemid'); ?>" />
	<?php if(JRequest::getCmd('tmpl') == 'component'){ ?><input type="hidden" name="tmpl" value="component" /><?php } ?>
	<div id="unsubbutton_div" class="unsubdiv">
		<input class="button" type="submit" value="<?php echo JText::_('UNSUBSCRIBE',true)?>"/>
	</div>
</form>
</div>