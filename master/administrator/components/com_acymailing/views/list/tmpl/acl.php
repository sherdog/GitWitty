<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php $acltype = acymailing::get('type.acl'); ?>
<fieldset class="adminform">
	<legend><?php echo JText::_( 'ACCESS_LEVEL' ); ?></legend>
	<table width="100%">
		<tr>
			<td valign="top" width="50%">
				<fieldset>
				<legend><?php echo JText::_('ACCESS_LEVEL_SUB'); ?></legend>
				<?php echo $acltype->display('access_sub',$this->list->access_sub); ?>
				</fieldset>
			</td>
			<td valign="top">
				<fieldset>
				<legend><?php echo JText::_('ACCESS_LEVEL_MANAGE'); ?></legend>
				<?php echo $acltype->display('access_manage',$this->list->access_manage); ?>
				</fieldset>
			</td>
		</tr>
	</table>
</fieldset>