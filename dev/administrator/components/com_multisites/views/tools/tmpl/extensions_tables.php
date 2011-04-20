<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $isTemplate = $this->_isExtensionSite( 1);
      $isSite = $this->_isExtensionSite( 2);
      $viewImg  = '<img src="components/com_multisites/images/view.png" alt="Shared table" title="Shared table{_viewFrom}" />';
      $tableImg = '<img src="components/com_multisites/images/table.png" alt="Table" title="Table" />';
?>
	<table class="adminlist" cellspacing="1">
      <thead>
   		<tr>
   			<th>Tables</th>
   			<th>Master</th>
<?php if ($isTemplate) { ?>
   			<th>Template</th>
<?php }
      if ($isSite) { ?>
   			<th>Site</th>
<?php } ?>
         </tr>
      </thead>
      <tbody>
<?php 
         $i = 0; $k = 0;
         foreach( $this->tablesInfo as $name => $columns) {
?>            
   		<tr class="<?php echo "row". $k; ?>">
            <td><?php echo $name ?></td>
            <td align="center"><?php echo ( !empty( $columns[0]) ? ( $columns[0]->_isView ? str_replace( '{_viewFrom}', $columns[0]->_viewFrom, $viewImg) : $tableImg) : '-') ;?></td>
<?php if ($isTemplate) { ?>
            <td align="center"><?php echo ( !empty( $columns[1]) ? ( $columns[1]->_isView ? str_replace( '{_viewFrom}', $columns[1]->_viewFrom, $viewImg) : $tableImg) : '-') ;?></td>
<?php }
      if ($isSite) { ?>
            <td align="center"><?php echo ( !empty( $columns[2]) ? ( $columns[2]->_isView ? str_replace( '{_viewFrom}', $columns[2]->_viewFrom, $viewImg) : $tableImg) : '-') ;?></td>
<?php } ?>
         </tr>
<?php
		   $i++; 
		   $k = 1 - $k;
         } // Next Table
?>    
      </tbody>
   </table>
