<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="acy_content" >
<div id="iframedoc"></div>
<form action="index.php?option=<?php echo ACYMAILING_COMPONENT ?>" method="post" name="adminForm" enctype="multipart/form-data">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'IMPORT_FROM' ); ?></legend>
		<?php echo JHTML::_('select.radiolist',   $this->importvalues, 'importfrom', 'class="inputbox" size="1" onclick="updateImport(this.value);"', 'value', 'text','file'); ?>
	</fieldset>
	<div>
	<?php foreach($this->importdata as $div => $name){
		echo '<div id="'.$div.'"';
		if($div != 'file') echo ' style="display:none"';
		echo '>';
		echo '<fieldset class="adminform">';
		echo '<legend>'.$name.'</legend>';
		include(dirname(__FILE__).DS.$div.'.php');
		echo '</fieldset>';
		echo '</div>';
		}?>
	</div>
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'IMPORT_SUBSCRIBE' ); ?></legend>
	<table class="adminlist" cellpadding="1">
	<?php
	$currentValues = JRequest::getVar('importlists');
	$k = 0;
	foreach( $this->lists as $row){?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo '<div class="roundsubscrib rounddisp" style="background-color:'.$row->color.'"></div>'; ?>
				<?php
				$text = '<b>'.JText::_('ID').' : </b>'.$row->listid;
				$text .= '<br/>'.str_replace(array("'",'"'),array("&#039;",'&quot;'),$row->description);
				$title = str_replace(array("'",'"'),array("&#039;",'&quot;'),$row->name);
				echo JHTML::_('tooltip', $text, $title, 'tooltip.png', $title);
				?>
			</td>
			<td align="center">
				<?php echo JHTML::_('select.booleanlist', "importlists[".$row->listid."]",'',!empty($currentValues[$row->listid])); ?>
			</td>
		</tr>
		<?php
		$k = 1-$k;
	}?>
	</table>
</fieldset>
	<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>