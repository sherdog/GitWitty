<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<fieldset>
	<legend><?php echo JText::_('EXTRA_INFORMATION'); ?></legend>
	<table class="admintable">
	<?php foreach($this->extraFields as $fieldName => $oneExtraField) {
		echo '<tr><td class="key">'.$this->fieldsClass->getFieldName($oneExtraField).'</td><td>'.$this->fieldsClass->display($oneExtraField,@$this->subscriber->$fieldName,'data[subscriber]['.$fieldName.']').'</td></tr>';
	}
	 ?>
	</table>
</fieldset>