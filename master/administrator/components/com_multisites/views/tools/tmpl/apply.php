<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
   $sites = $this->sites;
?>
<script language="javascript" type="text/javascript">
<!--
	var g_curtoken = '<?php echo JUtility::getToken() . '=1'; ?>';
//-->
</script>
<?php if ( !empty($this->ads)) { ?>
<table border="0">
   <tr><td><?php echo $this->ads; ?></td></tr>
</table>
<?php } ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
 <table border="0"><tr><td>
   <table class="adminlist">
      <tbody>
<?php  $i = 0;
       foreach( $sites as $site_id => $actions) { ?>
         <tr class="site" id="siteid_<?php echo $i; ?>">
            <!-- site_id=<?php echo $site_id; ?>; -->
            <td><?php echo $site_id; 
                      if ( !empty($actions->sitename)) {
                        echo ': ' . $actions->sitename;
                      }
                 ?></td>
            <td><?php echo JText::_( 'Option'); ?></td>
            <td><?php echo JText::_( 'Action'); ?></td>
            <td><?php echo JText::_( 'From site'); ?></td>
            <td><?php echo JText::_( 'Overwrite'); ?></td>
            <td><?php echo JText::_( 'Status'); ?></td>
         </tr>
<?php    $j = 0;
         $k = 0;
         foreach( $actions as $action) { ?>
         <tr id="action_<?php echo $i.'_'.$j; ?>" class="<?php echo "row". $k; ?>">
            <!-- opt=<?php echo $action->option; ?>; action=<?php echo $action->action; ?>; fromSiteID=<?php echo $action->fromSiteID; ?>; overwrite=<?php echo $action->overwrite; ?>; -->
            <td class="toolaction"><div class="toolaction"><?php echo $action->name; ?></div></td>
            <td><?php echo $action->option; ?></td>
            <td><?php echo $action->action; ?></td>
            <td><?php echo (empty( $action->fromSiteID) ? '&nbsp;' : $action->fromSiteID); ?></td>
            <td align="center"><input type="checkbox" name="ow[]" value="1" disabled <?php if ( !empty($action->overwrite) && $action->overwrite) { echo 'checked="checked"'; } ?>/></td>
            <td><div id="result_<?php echo $i.'_'.$j; ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
         </tr>
         
<?php       $j++;
            $k = 1 - $k;
         } // Next action (j)
?>
         <tr id="err_<?php echo $i ?>" style="display:none;">
            <td colspan="6"><div id="errmsg_<?php echo $i; ?>" class="toolApply_errmsg"></div></td>
         </tr>
<?php    $i++;
      } // Next site (i)
?>
      </tbody>
   </table>
 </table>
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="task" value="tools" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>