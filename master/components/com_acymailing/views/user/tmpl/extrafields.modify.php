<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
	<?php foreach($this->extraFields as $fieldName => $oneExtraField) {
		echo '<tr id="tr'.$fieldName.'"><td width="150" class="key">'.$this->fieldsClass->getFieldName($oneExtraField).'</td><td>';
		if(in_array($fieldName,array('name','email')) AND !empty($this->subscriber->userid)){echo $this->subscriber->$fieldName; }
		else{echo $this->fieldsClass->display($oneExtraField,@$this->subscriber->$fieldName,'data[subscriber]['.$fieldName.']'); }
		echo '</td></tr>';
	}
	 ?>