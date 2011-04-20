<?php
// no direct access
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );
global $mainframe, $jfConfig;

$jcontacts_path = JPATH_SITE.DS."components".DS."com_jcontacts";
$jcontacts_adm_path = JPATH_SITE.DS."administrator".DS."components".DS."com_jcontacts";

require_once($jcontacts_path.DS.'jcontacts.class.php' );

$language = 'english';
include_once($jcontacts_adm_path.DS."languages".DS.$language.".php" );

$phone			= $params->get( 'phone' );
$email		= $params->get( 'email' );
$comment	= $params->get( 'comment' );
$text 			= $params->get( 'text' );
$company = $params->get('company');
?>

<script language="JavaScript" type="text/javascript">

		<!--

			function checkFields() {

				var form = document.newLeadForm;				

				

				if (form.last_name.value == "") {

					alert("<?php echo _VALIDATE_LAST_NAME;?>");

					return false;

				} else if (form.email.value == "") {

					alert("<?php echo _VALIDATE_EMAIL;?>");

					return false;

				} else {

					form.submit();

				}

			}

		-->

		</script>

<table width="280" cellpadding="0" cellspacing="3" class='contactTable'>

  <tr>

	  <td class='fieldValue'>

   <form onsubmit="return checkFields()" action="index.php?option=com_jcontacts&task=saveLead" method="post" name='newLeadForm' >

  <span class='descriptor'><strong><?php echo $text ? $text : _LEAD_FORM_HEADER; ?><br />

  </strong><br />

  <?php echo _FIRST_NAME;?><br />

  </span>

  <input type="text" class="inputbox" name="first_name" />

  </td>

  </tr>

  <tr>

    <td class='fieldValue'><span class='descriptor'><?php echo _LAST_NAME;?><br />

      </span>

      <input type="text" class="inputbox" id='last_name' name="last_name" /></td>

  </tr>

 

  <tr>

    <td class='fieldValue'><span class='descriptor'><?php echo _COMPANY;?><br />

      </span>

      <input type="text" class="inputbox" name="company_name" /></td>

  </tr>

 

  <tr>

    <td class='fieldValue'><span class='descriptor'><?php echo _PHONE; ?><br />

      </span>

      <input type="text" class="inputbox" name="phone" /></td>

  </tr>

  

  <tr>

    <td class='fieldValue'><span class='descriptor'><?php echo _JEMAIL;?><br />

      </span>

      <input type="text" class="inputbox" id='email' name="email" /></td>

  </tr>

  

  <tr>

    <td class='fieldValue'><span class='descriptor'><?php echo _MESSAGE;?><br />

      </span>

      <textarea cols="20" rows="5" class="inputbox" id='message' name="message"></textarea></td>

  </tr>

  

  <tr>

    <td class='fieldValue'><input type="submit" name="submit" value="<?php echo _JSUBMIT; ?>" class='button' /></td>

  </tr>

</table>

<input type='hidden' name='created' value='<?php echo date('Y-m-d H:i:s');?>'  />

<input type='hidden' name='published' value='1'  />

</form>



