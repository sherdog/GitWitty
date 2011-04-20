<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="acy_content">
<div id="iframedoc"></div>
<form action="index.php?option=<?php echo ACYMAILING_COMPONENT ?>" method="post" name="adminForm" autocomplete="off">
	<table class="adminform" cellspacing="1" width="100%">
		<tr>
			<td>
				<label for="name">
					<?php echo JText::_( 'ACY_NAME' ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="data[list][name]" id="name" class="inputbox" size="40" value="<?php echo $this->escape(@$this->list->name); ?>" />
			</td>
			<td>
				<label for="activated">
					<?php echo JText::_( 'ENABLED' ); ?>
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', "data[list][published]" , '',$this->list->published); ?>
			</td>
		</tr>
	</table>
	<table width="100%">
		<tr>
			<td width = "60%" valign="top">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'ACY_DESCRIPTION' ); ?></legend>
					<?php echo $this->editor->display();?>
				</fieldset>
			</td>
			<td valign="top">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'LISTS' ); ?></legend>
					<?php echo JText::_('CAMPAIGN_START')?>
					<table class="adminlist" cellpadding="1">
						<thead>
							<tr>
								<th class="title">
									<?php echo JText::_('LIST_NAME'); ?>
								</th>
								<th class="title">
									<?php echo JText::_('AFFECTED'); ?>
								</th>
							</tr>
						</thead>
						<tbody>
					<?php
							$k = 0;
							for($i = 0,$a = count($this->lists);$i<$a;$i++){
								$row =& $this->lists[$i];
					?>
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
									<?php echo JHTML::_('select.booleanlist', "data[listcampaign][".$row->listid."]" , '',(bool) $row->campaignid); ?>
								</td>
							</tr>
					<?php
								$k = 1-$k;
							}
						?>
						</tbody>
					</table>
				</fieldset>
			</td>
		</tr>
	</table>
	<div class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->list->listid; ?>" />
	<input type="hidden" name="data[list][type]" value="campaign" />
	<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="campaign" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>