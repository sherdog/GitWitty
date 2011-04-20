<?php defined('_JEXEC') or die('Restricted access'); ?>
<table border="0">
	<tr>
		<td width="100%" />
		<td nowrap="nowrap">
         <?php echo MultisitesHelper::getFilterActionsCombo( count( $this->symbolicLinks)); ?>
		</td>
	</tr>
</table>
<table class="adminlist" cellspacing="1">
<thead>
	<tr>
		<th width="1%">
			<?php echo JText::_( 'Num' ); ?>
		</th>
		<th width="5%">
			<?php echo JHTML::_('grid.sort',   JText::_( 'Action'), 'action', @$lists['order_Dir'], @$lists['order'] ); ?>
		</th>
		<th width="15%">
			<?php echo JHTML::_('grid.sort',   JText::_( 'Folder or File'), 'name', @$lists['order_Dir'], @$lists['order'] ); ?>
		</th>
		<th width="55%">
			<?php echo JHTML::_('grid.sort',   JText::_( 'TEMPLATE_VIEW_EDT_UNIX_FROM_FILE_OR_FOLDER'), 'file', @$lists['order_Dir'], @$lists['order'] ); ?>
			<?php echo JHTML::_('tooltip', JText::_( 'When empty for a copy, this is the master website that is used to replicate the file or the folder' )); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</th>
	</tr>
</thead>
<tbody>
<?php $i = 0; $k = 0; ?>
<?php foreach ($this->symbolicLinks as $key => $symbolicLink) { ?>
	<tr class="<?php echo "row". $k; ?>" id="row_<?php echo $i; ?>">
		<td align="center">
			<?php echo $i+1; ?>
		</td>
		<td nowrap="nowrap">
<?php
		   if ( isset( $symbolicLink['readOnly']) && $symbolicLink['readOnly']) {
?>
      	<input type="hidden" name="SL_actions[<?php echo $i; ?>]" value="<?php echo $symbolicLink['action']; ?>" />
      	<input type="hidden" name="SL_readOnly[<?php echo $i; ?>]" value="true" />
<?php
            echo JText::_( "TEMPLATE_ACTION_". $symbolicLink['action']);
		   }
		   else {
		      echo MultisitesHelper::getActionsList( "SL_actions[$i]", $key, $symbolicLink, "SL_files[$i]");
   		}
?>
		</td>
		<td nowrap="nowrap">
      	<input type="hidden" name="SL_names[<?php echo $i; ?>]" value="<?php echo $key; ?>" />
			<?php echo $key; ?>
		</td>
		<td nowrap="nowrap">
<?php    $sl_file = (isset( $symbolicLink['file'])) ? $symbolicLink['file'] : '';
         if ( $this->isActionEditable( $symbolicLink['action'])) {
?>
      	<input type="text" name="SL_files[<?php echo $i; ?>]" id="SL_files[<?php echo $i; ?>]" value="<?php echo $sl_file; ?>" size="120" maxlength="255"/>
<?php
         } else {
?>
      	<input type="hidden" readonly="1" name="SL_files[<?php echo $i; ?>]" id="SL_files[<?php echo $i; ?>]" value="<?php echo $sl_file; ?>" size="120" maxlength="255"/>
<?php    echo $sl_file;
         }
?>
		</td>
	</tr>
	<?php $i++; $k = 1 - $k; ?>
<?php } // End for each ?>
</tbody>
</table>