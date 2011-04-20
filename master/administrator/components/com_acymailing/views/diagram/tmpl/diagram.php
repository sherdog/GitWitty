<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="iframedoc"></div>
<form action="index.php?option=<?php echo ACYMAILING_COMPONENT ?>&amp;ctrl=diagram" method="post" name="adminForm">
<table>
	<tr>
		<td width="100%">
		</td>
		<td nowrap="nowrap">
			<?php echo $this->filters->task; ?>
		</td>
	</tr>
</table>
<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
<input type="hidden" name="ctrl" value="diagram" />
<div style="float:left;"><?php if(!empty($this->lists)){
	echo '<table>';
	foreach($this->lists as $onelist){
		echo '<tr><td width="12"><div class="roundsubscrib rounddisp" style="background-color:'.$onelist->color.'"></div></td><td><input type="checkbox" checked="checked" onclick="drawChart();" name="lists" value="'.$onelist->listid.'" id="list_'.$onelist->listid.'"/><label for="list_'.$onelist->listid.'">'.$onelist->name.'</label></td></tr>';
	}
	echo '</table>';
}?></div>
<div id="acychart" style="float:left;text-align:center"></div>
</form>
<br style="clear:both" />