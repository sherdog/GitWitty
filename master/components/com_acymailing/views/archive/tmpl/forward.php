<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<div class="componentheading"><?php echo JText::_('FORWARD_FRIEND'); ?></div>
<form action="<?php echo JRoute::_( 'index.php' );?>" method="post" name="adminForm">
	<div class="acymailing_forward">
		<table>
			<tr>
				<td>
					<?php echo JText::_('JOOMEXT_NAME'); ?>
				</td>
				<td>
					<input type="text" name="name" value="" size="20"/>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('JOOMEXT_EMAIL'); ?>
				</td>
				<td>
					<input type="text" name="email" value="" size="20"/>
				</td>
			</tr>
		</table>
		<input type="submit" value="<?php echo JText::_('SEND'); ?>"/>
	</div>
	<input type="hidden" name="key" value="<?php echo $this->mail->key;?>" />
	<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
	<input type="hidden" name="task" value="doforward" />
	<input type="hidden" name="ctrl" value="archive" />
	<input type="hidden" name="mailid" value="<?php echo $this->mail->mailid; ?>" />
	<?php if(JRequest::getCmd('tmpl') == 'component'){ ?><input type="hidden" name="tmpl" value="component" /><?php } ?>
</form>
<?php include(dirname(__FILE__).DS.'view.php'); ?>