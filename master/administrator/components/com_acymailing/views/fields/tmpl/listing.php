<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="iframedoc"></div>
<form action="index.php?option=<?php echo ACYMAILING_COMPONENT ?>&amp;ctrl=fields" method="post" name="adminForm">
	<table class="adminlist" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'ACY_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->rows); ?>);" />
				</th>
				<th class="title">
					<?php echo JText::_('FIELD_COLUMN'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('FIELD_LABEL'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('FIELD_TYPE'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('REQUIRED'); ?>
				</th>
				<th class="title titleorder">
					<?php echo JText::_('ACY_ORDERING'); echo JHTML::_('grid.order',  $this->rows );?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('DISPLAY_FRONTCOMP'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('DISPLAY_BACKEND'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('DISPLAY_LISTING'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('ACY_PUBLISHED'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('CORE'); ?>
				</th>
				<th class="title titleid">
					<?php echo JText::_( 'ACY_ID' ); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$k = 0;
				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];
					$publishedid = 'published_'.$row->fieldid;
					$requiredid = 'required_'.$row->fieldid;
					$backendid = 'backend_'.$row->fieldid;
					$frontcompid = 'frontcomp_'.$row->fieldid;
					$listingid = 'listing_'.$row->fieldid;
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center">
					<?php echo $i+1; ?>
					</td>
					<td align="center">
						<?php echo JHTML::_('grid.id', $i, $row->fieldid ); ?>
					</td>
					<td>
						<a href="<?php echo acymailing::completeLink('fields&task=edit&cid[]='.$row->fieldid); ?>">
							<?php echo $row->namekey; ?>
						</a>
					</td>
					<td>
						<?php echo $this->fieldsClass->trans($row->fieldname); ?>
					</td>
					<td>
						<?php echo $this->fieldtype->allValues[$row->type]; ?>
					</td>
					<td align="center">
						<span id="<?php echo $requiredid ?>" class="loading"><?php echo $this->toggleClass->toggle($requiredid,(int) $row->required,'fields') ?></span>
					</td>
					<td class="order">
						<span><?php echo $this->pagination->orderUpIcon( $i, $row->ordering >= @$this->rows[$i-1]->ordering ,'orderup', 'Move Up',true ); ?></span>
						<span><?php echo $this->pagination->orderDownIcon( $i, $a, $row->ordering <= @$this->rows[$i+1]->ordering , 'orderdown', 'Move Down' ,true); ?></span>
						<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
					</td>
					<td align="center">
						<span id="<?php echo $frontcompid ?>" class="loading"><?php echo $this->toggleClass->toggle($frontcompid,(int) $row->frontcomp,'fields') ?></span>
					</td>
					<td align="center">
						<span id="<?php echo $backendid ?>" class="loading"><?php echo $this->toggleClass->toggle($backendid,(int) $row->backend,'fields') ?></span>
					</td>
					<td align="center">
						<span id="<?php echo $listingid ?>" class="loading"><?php echo $this->toggleClass->toggle($listingid,(int) $row->listing,'fields') ?></span>
					</td>
					<td align="center">
						<span id="<?php echo $publishedid ?>" class="loading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->published,'fields') ?></span>
					</td>
					<td align="center">
						<?php echo $this->toggleClass->display('activate',$row->core); ?>
					</td>
					<td width="1%" align="center">
						<?php echo $row->fieldid; ?>
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
	<input type="hidden" name="ctrl" value="fields" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
