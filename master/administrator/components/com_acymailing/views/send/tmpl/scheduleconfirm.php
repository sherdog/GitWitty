<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="iframedoc"></div>
<form action="index.php?tmpl=component&amp;option=<?php echo ACYMAILING_COMPONENT ?>&amp;ctrl=send" method="post" name="adminForm" autocomplete="off">
	<div>
	<?php if(!empty($this->lists)){?>
		<fieldset class="adminform">
		<legend><?php echo JText::_( 'NEWSLETTER_SENT_TO'); ?></legend>
			<table class="adminlist" cellspacing="1" align="center">
				<tbody>
					<?php
					$k = 0;
					foreach($this->lists as $row){
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td>
							<?php
							$text = str_replace(array("'",'"'),array("&#039;",'&quot;'),$row->description);
							$title = str_replace(array("'",'"'),array("&#039;",'&quot;'),$row->name);
							echo JHTML::_('tooltip', $text, $title, 'tooltip.png', $title);
							echo ' ( '.$row->nbsub.' )';
							 ?>
						</td>
					</tr>
					<?php
						$k = 1 - $k;
					} ?>
				</tbody>
			</table>
		</fieldset>
		<table class="adminform">
			<tr>
				<td class="key">
					<?php echo JText::_('SEND_DATE'); ?>
				</td>
				<td>
					<?php echo JHTML::_('calendar', acymailing::getDate(time(),'%Y-%m-%d %H:%M'), 'senddate','senddate','%Y-%m-%d %H:%M','size="50"'); ?>
				</td>
			</tr>
			<tr>
				<td>
				</td>
				<td>
					<button type="submit"><?php echo JText::_('SCHEDULE'); ?></button>
				</td>
			</tr>
		</table>
	<?php }else{echo acymailing::display(JText::_( 'EMAIL_AFFECT' ),'warning');}?>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo $this->mail->mailid; ?>" />
	<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
	<input type="hidden" name="task" value="schedule" />
	<input type="hidden" name="ctrl" value="send" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>