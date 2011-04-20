<?php defined('_JEXEC') or die('Restricted access'); ?>

<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
		var form = document.adminForm;

		if (pressbutton == 'savetemplate') {
			if ( form.id.value == '' ) {
				alert( '<?php echo JText::_( 'Please enter a template identifier', true ); ?>' );
				form.menutype.focus();
				return;
			}
			var r = new RegExp("[\']", "i");
			if ( r.exec(form.id.value) ) {
				alert( '<?php echo JText::_( 'The template identifier cannot contain a \'', true ); ?>' );
				form.menutype.focus();
				return;
			}
			submitform( 'savetemplate' );
		} else {
			submitform( pressbutton );
		}
	}

   function getUserList( site_id)
   {
      var ajax;
      document.getElementById("divMessage").innerHTML = 'Refreshing ...';
      try {  ajax = new ActiveXObject('Msxml2.XMLHTTP');   }
      catch (e)
      {
        try {   ajax = new ActiveXObject('Microsoft.XMLHTTP');    }
        catch (e2)
        {
          try {  ajax = new XMLHttpRequest();     }
          catch (e3) {  ajax = false;   }
        }
      }

      ajax.onreadystatechange  = function()
      {
         if(ajax.readyState  == 4)
         {
            var showFolders = true;
            if(ajax.status  == 200) {
               document.getElementById("divAdminUser").innerHTML = ajax.responseText;
               if ( ajax.responseText.length <= 0) {
                  showFolders = false;
               }
               document.getElementById("divMessage").innerHTML = '';
            }
            else {
               document.getElementById("divMessage").innerHTML = "Error code " + ajax.status;
               showFolders = false;
            }
            var shareDB = document.getElementById("tr_shareDB");
            var style_display = '';
            var db_display = '';
            if ( !showFolders) {
               style_display   = 'none';
               db_display      = 'none';
            }
            else {
               if ( shareDB.checked) {
                  db_display      = 'none';
               }
            }
            

<?php if ( $this->isCreateView) { ?>
            try {
               document.getElementById("panelsharing").style.display = style_display;
            }
            catch( ee) {}
<?php } ?>
            document.getElementById("show_sku").style.display     = style_display;
            document.getElementById("title").style.display        = style_display;
            document.getElementById("toSiteID").style.display     = style_display;
            document.getElementById("admin_user").style.display   = style_display;
            
            document.getElementById("tr_shareDB").style.display   = style_display;
            document.getElementById("db_host").style.display      = db_display;
            document.getElementById("db_name").style.display      = db_display;
            document.getElementById("db_user").style.display      = db_display;
            document.getElementById("db_psw").style.display       = db_display;
            document.getElementById("table_prefix").style.display = db_display;
<?php if ( MultisitesHelper::isSymbolicLinks()) { ?>
            document.getElementById("alias_folder").style.display = style_display;
<?php } ?>
            document.getElementById("media_folder").style.display = style_display;
            document.getElementById("image_folder").style.display = style_display;
         }
      };

      ajax.open( "GET", "index.php?option=com_multisites&task=ajaxGetUsersList&<?php echo JUtility::getToken() . '=1'; ?>&site_id="+site_id,  true);
      ajax.send(null);
   }
   
   function enableSource( action, field_id)
   {
      var elt = document.getElementById( field_id);
      if ( action == 'copy' || action == 'unzip') {
         elt.type       = 'text';
         elt.readOnly   = false;
      }
      else {
         elt.type       = 'hidden';
         elt.readOnly   = true;
      }
   }

   function filterActions( action, nbrows)
   {
      var i = 0;
      for ( i=0; i<nbrows; i++) {
         var field_id = 'SL_actions' + i;
         var elt = document.getElementById( field_id);
         if ( elt != null) {
            var show = true;
            if ( action == 'hide') {
               if ( elt.value == 'ignore') {
                  var show = false;
               }
            }
            var eltRow = document.getElementById( 'row_'+i);
            if ( show) {
               eltRow.style.display="";
            }
            else {
               eltRow.style.display="none";
            }
         }
      }
   }
   
   function onSharedDB( checked)
   {
      var db_display = '';
      if ( checked) {
         db_display = 'none';
      }

      document.getElementById("show_sku").style.display     = db_display;

      document.getElementById("db_host").style.display      = db_display;
      document.getElementById("db_name").style.display      = db_display;
      document.getElementById("db_user").style.display      = db_display;
      document.getElementById("db_psw").style.display       = db_display;
      document.getElementById("table_prefix").style.display = db_display;

      document.getElementById("media_folder").style.display = db_display;
      document.getElementById("image_folder").style.display = db_display;
   }

   function onShowFTPField( radiovalue)
   {
      var ftp_display = 'none';
      if ( radiovalue=='0' || radiovalue=='1') {
         ftp_display = '';
      }

      document.getElementById("tr_toFTP_host").style.display      = ftp_display;
      document.getElementById("tr_toFTP_port").style.display      = ftp_display;
      document.getElementById("tr_toFTP_user").style.display      = ftp_display;
      document.getElementById("tr_toFTP_psw").style.display       = ftp_display;
      document.getElementById("tr_toFTP_rootpath").style.display  = ftp_display;
   }

//-->
</script>
<?php if ( !empty($this->ads)) { ?>
<table border="0">
   <tr><td><?php echo $this->ads; ?></td></tr>
</table>
<?php } ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<?php
jimport('joomla.html.pane');
$pane =& JPane::getInstance(); 
echo $pane->startPane( 'pane' );
echo $pane->startPanel( JText::_( 'Common'), 'panelcmn' );
echo $this->loadTemplate('common');
echo $pane->endPanel();
if ( true || $this->canShowDeployDir()) {
   echo $pane->startPanel( JText::_( 'Folders and files'), 'panelunix' );
   echo $this->loadTemplate('unix');
   echo $pane->endPanel();
}
if ( !$this->isCreateView) {
   echo $pane->startPanel( JText::_( 'Sharing'), 'panelsharing" style="display:none;');
}
else {
   echo $pane->startPanel( JText::_( 'Sharing'), 'panelsharing');
}
?>   
   <fieldset id="treeview">
       <div id="dbsharing-tree_tree"></div>
       <?php echo $this->loadTemplate('sharing'); ?>
   </fieldset>
   <span class="note">
		<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_SHR_NOTES' ); ?></strong>
   </span>
<?php
echo $pane->endPanel();
echo $pane->endPane();
?>
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="task" value="saveTemplate" />
	<input type="hidden" name="isnew" value="<?php if ($this->isnew) { echo '1';} else { echo'0';} ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>