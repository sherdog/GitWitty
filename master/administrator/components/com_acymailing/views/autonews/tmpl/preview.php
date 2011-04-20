<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="iframedoc"></div>
	<table width="100%">
		<tr >
			<td width="50%" valign="top">
				<?php include(ACYMAILING_BACK.'views'.DS.'newsletter'.DS.'tmpl'.DS.'test.php'); ?>
			</td>
			<td valign="top">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'NEWSLETTER_SENT_TO' ); ?></legend>
					<table class="adminlist" cellspacing="1" align="center">
						<tbody>
							<?php if(!empty($this->lists)){
								$k = 0;
								foreach($this->lists as $row){
							?>
							<tr class="<?php echo "row$k"; ?>">
								<td>
									<?php
									if(!$row->published) echo JHTML::_('tooltip', JText::_('LIST_PUBLISH'), '', 'warning.png').' ';
									$text = str_replace(array("'",'"'),array("&#039;",'&quot;'),$row->description);
									$title = str_replace(array("'",'"'),array("&#039;",'&quot;'),$row->name);
									echo JHTML::_('tooltip', $text, $title, 'tooltip.png', $title);
									echo ' ( '.$row->nbsub.' '.JText::_('USERS').' )';
									echo '<div class="roundsubscrib rounddisp" style="background-color:'.$row->color.'"></div>';
									?>
								</td>
							</tr>
							<?php $k = 1-$k;}}else{ ?>
								<tr>
									<td>
										<?php echo JText::_('EMAIL_AFFECT');?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</fieldset>
			</td>
		</tr>
	</table>
	<?php include(ACYMAILING_BACK.'views'.DS.'newsletter'.DS.'tmpl'.DS.'previewcontent.php'); ?>