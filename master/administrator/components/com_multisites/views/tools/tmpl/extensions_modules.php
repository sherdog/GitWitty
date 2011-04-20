<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $isTemplate = $this->_isExtensionSite( 1);
      $isSite = $this->_isExtensionSite( 2);
      $hasChildren = $this->_hasChildren();
      $yes = '<img src="components/com_multisites/images/yes.png" title="Present" />'
?>
	<table class="adminlist" cellspacing="1">
      <thead>
   		<tr>
   			<th><?php echo JText::_( 'Modules'); ?></th>
   			<th><?php echo JText::_( 'Master'); ?></th>
<?php if ($isTemplate) { ?>
   			<th><?php echo JText::_( 'Template'); ?></th>
<?php }
      if ($isSite) {
?>
   			<th><?php echo JText::_( 'Site'); ?></th>
   			<th><?php echo JText::_( 'Action'); ?></th>
<?php }
      if ( $hasChildren) {
?>
   			<th><?php echo JText::_( 'Propagate to children'); ?><br/>
   			   <input type="checkbox" name="toggleComponents" value="" onclick="checkAllComponents(this.checked, 'mod');" />
            </th>
   			<th><?php echo JText::_( 'Overwrite'); ?><br/>
   			   <input type="checkbox" name="overwriteComponents" value="" onclick="checkAllComponents(this.checked, 'mow');" />
            </th>
<?php } ?>
         </tr>
      </thead>
      <tbody>
<?php if ( !empty( $this->extensions['Modules'])) {
         $i = 0; $k = 0;
         foreach( $this->extensions['Modules'] as $name => $columns) {
            if ( !empty( $columns[0]))       { $module = & $columns[0]; }
            else if ( !empty( $columns[1]))  { $module = & $columns[1]; }
            else if ( !empty( $columns[2]))  { $module = & $columns[2]; }
            $option = $module->module;
?>            
   		<tr class="<?php echo "row". $k; ?>">
            <td>
      			<span class="editlinktip hasDynTip" title="<?php echo $this->_getToolTips( $columns, $i); ?>">
      				<?php echo $name ?>
      		   </span>
      		</td>
            <td align="center"><?php echo ( !empty( $columns[0]) ? $yes : '-') ;?></td>
<?php if ($isTemplate) { ?>
            <td align="center"><?php echo ( !empty( $columns[1]) ? $yes : '-') ;?></td>
<?php }
      if ($isSite) {
?>
            <td align="center"><?php echo $this->_getTableType( $columns, $i); ?></td>
   			<td><?php echo $this->_getComponentAction( $isTemplate, $option, $columns, 'amod', $i); ?></td>
<?php } ?>
<?php if ( $hasChildren) { ?>
         	<td align="center"><input type="checkbox" id="mod<?php echo $i; ?>" name="cmod[]" value="<?php echo $option; ?>" onclick="synchOverwrite(this.checked, 'mow<?php echo $i; ?>');" /></td>
         	<td align="center"><input type="checkbox" id="mow<?php echo $i; ?>" name="cmow[]" value="<?php echo $option; ?>" disabled="disabled" /></td>
<?php } ?>
         </tr>
<?php
		   $i++; 
		   $k = 1 - $k;
         } // Next Modules
      } // End if Modules
?>    
      </tbody>
   </table>
