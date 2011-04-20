<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="iframedoc"></div>
<form action="index.php?option=<?php echo ACYMAILING_COMPONENT ?>&amp;ctrl=statsurl" method="post" name="adminForm">
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_( 'JOOMEXT_FILTER' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->pageInfo->search;?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'JOOMEXT_GO' ); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'JOOMEXT_RESET' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php echo $this->filters->mail; ?>
				<?php echo $this->filters->url; ?>
			</td>
		</tr>
	</table>
	<table class="adminlist" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'NUM' );?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'URL' ), 'c.name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_( 'JOOMEXT_SUBJECT'), 'b.subject', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort',   JText::_( 'UNIQUE_HITS' ), 'uniqueclick', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort',   JText::_( 'TOTAL_HITS' ), 'totalclick', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = 0;
				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];
					$id = 'urlclick'.$i;
			?>
				<tr class="<?php echo "row$k"; ?>" id="<?php echo $id; ?>">
					<td align="center">
					<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td align="center">
						<a  class="modal" title="<?php echo JText::_('URL_EDIT',true); ?>"  href="<?php echo acymailing::completeLink('statsurl&task=edit&urlid='.$row->urlid,true); ?>" rel="{handler: 'iframe', size: {x: 500, y: 200}}"><img src="<?php echo ACYMAILING_LIVE.'images/M_images/edit.png'; ?>"/></a>
						<a target="_blank" href="<?php echo strip_tags($row->url); ?>" id="urlink_<?php echo $row->urlid.'_'.$row->mailid; ?>"><?php echo $row->name; ?></a>
					</td>
					<td align="center">
						<?php echo $row->subject; ?>
					</td>
					<td align="center">
					<?php if(acymailing::level(2)){ ?><a class="modal" href="<?php echo acymailing::completeLink('statsurl&task=detaillisting&filter_mail='.$row->mailid.'&filter_url='.$row->urlid,true)?>" rel="{handler: 'iframe', size:{x:800, y:500}}"><?php }
					 echo $row->uniqueclick;
					 if(acymailing::level(2)){ ?></a> <?php } ?>
					</td>
					<td align="center">
						<?php if(acymailing::level(2)){ ?><a class="modal" href="<?php echo acymailing::completeLink('statsurl&task=detaillisting&filter_mail='.$row->mailid.'&filter_url='.$row->urlid,true)?>" rel="{handler: 'iframe', size:{x:800, y:500}}"><?php }
						echo $row->totalclick;
						 if(acymailing::level(2)){ ?></a> <?php } ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
