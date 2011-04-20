<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
   $lists = $this->lists;
?>
<form action="index.php?option=com_multisites" method="post" name="adminForm" id="adminForm">
	<table>
<?php if ( !empty($this->ads)) { ?>
		<tr><td colspan="2"><?php echo $this->ads; ?></td></tr>
<?php } ?>
		<tr>
			<td width="100%">
			</td>
			<td nowrap="nowrap">
				<?php
				echo $lists['dbserver'];
				echo $lists['dbname'];
				?>
			</td>
		</tr>
	</table>

	<table class="adminlist" cellspacing="1">
	<thead>
		<tr>
			<th width="5%">
				<?php echo JText::_( 'Num' ); ?>
			</th>
			<th width="5%">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->templates ); ?>);" />
			</th>
			<th width="5%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   JText::_( 'TEMPLATE_LIST_ID'), 'id', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="5%">
				<?php echo JHTML::_('grid.sort',   JText::_( 'TEMPLATE_LIST_GROUP'), 'groupName', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="10%">
				<?php echo JHTML::_('grid.sort',   JText::_( 'TEMPLATE_LIST_FROM_SITE_ID'), 'fromSiteID', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="10%">
				<?php echo JHTML::_('grid.sort',   JText::_( 'TEMPLATE_LIST_TO_SITE_ID'), 'toSiteID', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="10%">
				<?php echo JHTML::_('grid.sort',   JText::_( 'TEMPLATE_LIST_TO DOMAINS'), 'toDomains', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="10%">
				<?php echo JHTML::_('grid.sort',   JText::_( 'TEMPLATE_LIST_FROM_DB'), 'fromDB', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="10%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   JText::_( 'TEMPLATE_LIST_FROM_DB_PREFIX'), 'fromPrefix', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="20%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   JText::_( 'TEMPLATE_LIST_TO_DB_NAME'), 'toDBName', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="20%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   JText::_( 'TEMPLATE_LIST_TO_DB_PREFIX'), 'toPrefix', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="20%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   JText::_( 'TEMPLATE_LIST_THEMES'), 'templates_dir', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
<?php if ( $this->canShowDeployDir()) { ?>
			<th width="10%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   JText::_( 'TEMPLATE_LIST_DEPLOY_DIRECTORY'), 'deploy_dir', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
<?php } ?>
		</tr>
	</thead>
	<tfoot>
	   <tr>
<?php if ( $this->canShowDeployDir()) { ?>
		   <td colspan="13">
<?php } else { ?>
		   <td colspan="12">
<?php } ?>
			   <?php echo $this->pagination->getListFooter(); ?>
		   </td>
	   </tr>
	</tfoot>
	<tbody>
	<?php $i = 0; $k = 0; ?>
	<?php foreach ($this->templates as $template) : ?>
		<?php
		   $id = $template['id'];
			// Get the current iteration and set a few values
			$link 	= 'index.php?option=com_multisites&task=editTemplate&id='. $id;
		?>
		<tr class="<?php echo "row". $k; ?>">
			<td align="center">
				<?php echo $this->pagination->limitstart + 1 + $i; ?>
			</td>
			<td width="30" align="center">
				<input type="radio" id="cb<?php echo $i;?>" name="id" value="<?php echo $id; ?>" onclick="isChecked(this.checked);" />
			</td>
			<td>
			<span class="editlinktip hasTip" title="<?php echo $this->getTemplateToolTips( $id, $template);?>">
				<a href="<?php echo $link; ?>">
					<?php echo $id; ?></a></span>
			</td>
			<td nowrap="nowrap">
				<?php echo isset($template['groupName']) ? $template['groupName'] : ''; ?>
			</td>
			<td nowrap="nowrap">
				<?php echo $template['fromSiteID']; ?>
			</td>
			<td nowrap="nowrap">
				<?php echo isset($template['toSiteID']) ? $template['toSiteID'] : ''; ?>
			</td>
			<td nowrap="nowrap">
				<?php echo !empty( $template['toDomains'])
				         ? implode( ",<br/>", $template['toDomains'])
				         : '&nbsp;'; ?>
			</td>
			<td nowrap="nowrap">
				<?php echo $template['fromDB']; ?>
			</td>
			<td nowrap="nowrap">
				<?php echo $template['fromPrefix']; ?>
			</td>
			<td nowrap="nowrap">
				<?php echo (isset( $template['toDBName'])) ? $template['toDBName'] : ''; ?>
			</td>
			<td nowrap="nowrap">
				<?php echo $template['toPrefix']; ?>
			</td>
			<td>
				<?php echo (isset( $template['templates_dir'])) ? $template['templates_dir'] : ''; ?>
			</td>
<?php if ( $this->canShowDeployDir()) { ?>
			<td>
				<?php echo (isset( $template['deploy_dir'])) ? $template['deploy_dir'] : ''; ?>
			</td>
<?php } ?>
		</tr>
		<?php $i++; $k = 1 - $k; ?>
	<?php endforeach; ?>
	</tbody>
	</table>

	<input type="hidden" name="option"           value="com_multisites" />
	<input type="hidden" name="task"             value="templates" />
	<input type="hidden" name="boxchecked"       value="0" />
	<input type="hidden" name="filter_order"     value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
