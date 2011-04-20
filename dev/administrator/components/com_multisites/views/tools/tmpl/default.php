<?php defined('_JEXEC') or die('Restricted access'); ?>
<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
		var form = document.adminForm;

		if (pressbutton == 'applyTools') {
			try {
   			if ( form.site_id.value == '' ) {
   				alert( '<?php echo JText::_( 'Please select a site', true ); ?>' );
   				return;
   			}
			}
			catch( e) {
				alert( '<?php echo JText::_( 'Please select a site', true ); ?>' );
				return;
			}
			submitform( 'applyTools' );
		} else {
			submitform( pressbutton );
		}
	}
	
	var g_curtoken = '<?php echo JUtility::getToken() . '=1'; ?>';

   function checkAllComponents( checked, name)
   {
      var i;
      var cbx;
      for ( i=0; ; i++) {
         try {
            cbx = $( name + i);
            if ( cbx.disabled) {
               cbx.checked = false;
            }
            else {
               cbx.checked = checked;
            } 
            
            if ( name == 'com') {
               synchOverwrite( checked, 'ow'+i);
            }
            else if ( name == 'mod') {
               synchOverwrite( checked, 'mow'+i);
            }
            else if ( name == 'plg') {
               synchOverwrite( checked, 'pow'+i);
            }
         }
         catch( e) {
            break;
         }
      }
   }
   
   function updateCB( select, cbName)
   {
      try {
         cbx = $( cbName);
         if ( select.value == '[unselected]') {
            cbx.checked  = false;
            cbx.disabled = true;
         }
         else {
            cbx.disabled = false;
         }
      }
      catch( e) {}
   }

   function synchOverwrite( checked, cbName)
   {
      try {
         cbx = $( cbName);
         if ( checked) {
            cbx.disabled = false;
         }
         else {
            cbx.checked  = false;
            cbx.disabled = true;
         }
      }
      catch( e) {}
   }

//-->
</script>
<?php if ( !empty($this->ads)) { ?>
<table border="0">
   <tr><td><?php echo $this->ads; ?></td></tr>
</table>
<?php } ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
   <table border="0" cellpadding="0" cellspacing="0">
      <tr>
         <td width="20%" valign="top">
            <fieldset id="treeview">
                <div id="treesites_tree"></div>
<?php echo $this->getChildrenTree( $this->treeSites, ' id="treesites"'); ?>
            </fieldset>
         </td>
         <td class="treesite_form">
            <div id="treesite_message"><?php echo JText::_( 'Select a site'); ?></div>
            <div id="treesite_detail">&nbsp;</div>
         </td>
      </tr>
   </table>
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="task" value="tools" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>