<?php defined('_JEXEC') or die('Restricted access'); ?>

<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
		if (pressbutton == 'doInstallPatches') {
			submitform( pressbutton );
		} else if (pressbutton == 'doUninstallPatches') {
			if (confirm ("<?php echo JText::_( 'Are you sure?' ); ?>")) {
				submitform( pressbutton );
			}
		} else {
			submitform( pressbutton );
		}
	}
//-->
</script>
<?php if ( !empty($this->ads)) { ?>
<table border="0">
   <tr><td><?php echo $this->ads; ?></td></tr>
</table>
<?php } ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div>
	   <table border="0">
	   <tr><td><?php echo JText::_( 'Joomla Multi Sites version'); ?>: <?php 
if ( !empty( $this->latestVersion['version'])) {
   if ( version_compare( $this->jmsVersion, $this->latestVersion['version']) < 0) {
      echo '<font color="red">' . $this->jmsVersion .'</font>';
      $getLatestURL = '&nbsp;&nbsp;&nbsp;<a href="http://www.jms2win.com/get-latest-version">Get Latest Version</a>';
   }
   else {
      echo '<font color="green">' . $this->jmsVersion .'</font>';
   }
   echo ' <em>(' . JText::_( 'Latest available') . ': ' . $this->latestVersion['version'] . ')</em>';
   if ( !empty( $getLatestURL)) {
      echo $getLatestURL;
   }
}
else {
   echo $this->jmsVersion;
}
?></td></tr>
	   <tr><td><?php echo JText::_( 'PATCHES_VIEW_PATCHES_DEF_VERS'); ?>: <?php
if ( !empty( $this->latestVersion['patch_version'])) {
   if ( version_compare( $this->patchesVersion, $this->latestVersion['patch_version']) < 0) {
      echo '<font color="red">' . $this->patchesVersion .'</font>';
   }
   else {
      echo '<font color="green">' . $this->patchesVersion .'</font>';
   }
   echo ' <em>(' . JText::_( 'Latest available') . ': ' . $this->latestVersion['patch_version'] . ')</em>';
}
else {
   echo $this->patchesVersion;
}
?></td></tr>
	   <tr><td>
	   <table class="adminform" border="1">
	      <thead>
	         <tr>
	            <th><?php echo JText::_( 'Files'); ?></th>
	            <th><?php echo JText::_( 'Status'); ?></th>
	         </tr>
	      </thead>
	      <tbody>
<?php
            foreach( $this->patches_status as $filename => $status) {
               $msgs = preg_split( '#[|]#', $status);
?>
            <tr valign="top">
	            <td><?php echo $filename; ?></td>
	            <td>
<?php             if ( $msgs[0] == '[OK]') { ?>
                  <center><font color="green">OK</font></center> 
<?php             } else { ?>                     
                  <center><font color="red">Not OK</font></center> 
	               <ul><?php foreach( $msgs as $msg) { 
	                  if ( $msg == '[NOK]') {}
	                  else if ( $msg == '[ACTION]') {
	                     break;
	                  }
	                  else { ?>
                     <li><?php echo $msg; ?></li><?php } } ?>
	               </ul>
<?php             if ( $msg == '[ACTION]') { ?>
                  <?php echo JText::_( 'Actions' ); ?>:
                  <ul type="circle"><?php
                     $state = 0;
                     foreach( $msgs as $msg) {
                        if ( $state == 0) {
                           if ( $msg == '[ACTION]') {
                              $state = 1;
                           }
                        }
                        else if ( $state == 1) { ?>
                        <li><?php echo $msg; ?></li>                        
<?php
                        } // End if state
                     } // End foreach
?>
                  </ul>                     
<?php             } // End if [ACTION]
?>
<?php             } ?>
<?php             if ( $filename == 'installation' && $msgs[0] == '[NOK]') { ?>
         			<label for="id">
         				<strong><?php echo JText::_( 'PATCHES_VIEW_RENINSTDIR_LBL' ); ?>:</strong>
         			</label>
         			<input class="inputbox" type="text" name="ren_inst_dir" id="ren_inst_dir" size="30" maxlength="25" value="" />
         			<?php echo JHTML::_('tooltip', JText::_( 'PATCHES_VIEW_RENINSTDIR_TT' )); ?>
<?php } ?>                     
	            </td>
	         </tr>
<?php } ?>
	      </tbody>
	   </table>
	   </td></tr></table>
<?php if ( $this->can_install) { ?>
      <span class="note"><strong><?php echo JText::_('PATCHES_NOTES'); ?>:</strong><br/><?php echo JText::_('PATCHES_CAN_INSTALL'); ?></span>
<?php } else { ?>
      <span class="note"><strong><?php echo JText::_('PATCHES_INSTALL_OK'); ?></strong><br/></span>
<?php } ?>
		<div class="clr"></div>
	</div>

	<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>