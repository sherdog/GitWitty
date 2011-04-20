<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $isTemplate = $this->_isExtensionSite( 1);
      $isSite = $this->_isExtensionSite( 2);
      $hasChildren = $this->_hasChildren();
      $yes = '<img src="components/com_multisites/images/yes.png" title="Present" />'
?>
	<table class="adminlist" cellspacing="1">
      <thead>
   		<tr>
   			<th><?php echo JText::_( 'Plugins'); ?></th>
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
   			   <input type="checkbox" name="togglePlugins" value="" onclick="checkAllComponents(this.checked, 'plg');" />
            </th>
   			<th><?php echo JText::_( 'Overwrite'); ?><br/>
   			   <input type="checkbox" name="overwritePlugins" value="" onclick="checkAllComponents(this.checked, 'pow');" />
            </th>
<?php } ?>
         </tr>
      </thead>
      <tbody>
<?php if ( !empty( $this->extensions['Plugins'])) {
         $i = 0; $k = 0;
         foreach( $this->extensions['Plugins'] as $name => $columns) {
            if ( !empty( $columns[0]))       { $plugin = & $columns[0]; }
            else if ( !empty( $columns[1]))  { $plugin = & $columns[1]; }
            else if ( !empty( $columns[2]))  { $plugin = & $columns[2]; }
            $option = $plugin->folder . '/' . $plugin->element;
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
   			<td><?php echo $this->_getComponentAction( $isTemplate, $option, $columns, 'aplg', $i); ?></td>
<?php } ?>
<?php if ( $hasChildren) { ?>
         	<td align="center"><input type="checkbox" id="plg<?php echo $i; ?>" name="cplg[]" value="<?php echo $option; ?>" onclick="synchOverwrite(this.checked, 'pow<?php echo $i; ?>');" /></td>
         	<td align="center"><input type="checkbox" id="pow<?php echo $i; ?>" name="cpow[]" value="<?php echo $option; ?>" disabled="disabled" /></td>
<?php } ?>
         </tr>
<?php
		   $i++; 
		   $k = 1 - $k;
         } // Next Plugin
      } // End if Plugins
?>    
      </tbody>
   </table>
