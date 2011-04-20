<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="iframedoc"></div>
<form action="index.php?tmpl=component&amp;option=<?php echo ACYMAILING_COMPONENT ?>" method="post" name="adminForm" autocomplete="off" enctype="multipart/form-data">
	<fieldset>
		<div class="header" style="float: left;"><?php echo JText::_('URL'); ?></div>
		<div class="toolbar" id="toolbar" style="float: right;">
			<button type="button" onclick="javascript:submitbutton('save')"><?php echo JText::_('SAVE'); ?></button>
		</div>
	</fieldset>
	<table class="adminform" cellspacing="1" width="100%">
		<tr>
			<td>
				<label for="name">
					<?php echo JText::_( 'URL_NAME' ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="data[url][name]" id="name" class="inputbox" size="40" value="<?php echo $this->escape(@$this->url->name); ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="url">
					<?php echo JText::_( 'URL' ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="data[url][url]" id="name" class="inputbox" size="90" value="<?php echo $this->escape(@$this->url->url); ?>" />
			</td>
		</tr>
	</table>
	<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="cid[]" value="<?php echo $this->url->urlid; ?>" />
	<input type="hidden" name="ctrl" value="statsurl" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>