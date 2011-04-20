<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.2 from 9th June 2010
*  Author: Klemen Stirn
*  Website: http://www.hesk.com
********************************************************************************
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2005-2010 Klemen Stirn. All Rights Reserved.
*  HESK is a registered trademark of Klemen Stirn.

*  The HESK may be used and modified free of charge by anyone
*  AS LONG AS COPYRIGHT NOTICES AND ALL THE COMMENTS REMAIN INTACT.
*  By using this code you agree to indemnify Klemen Stirn from any
*  liability that might arise from it's use.

*  Selling the code for this program, in part or full, without prior
*  written consent is expressly forbidden.

*  Using this code, in part or full, to create derivate work,
*  new scripts or products is expressly forbidden. Obtain permission
*  before redistributing this software over the Internet or in
*  any other medium. In all cases copyright and header must remain intact.
*  This Copyright is in full effect in any country that has International
*  Trade Agreements with the United States of America or
*  with the European Union.

*  Removing any of the copyright notices without purchasing a license
*  is expressly forbidden. To remove HESK copyright notice you must purchase
*  a license for this script. For more information on how to obtain
*  a license please visit the page below:
*  https://www.hesk.com/buy.php
*******************************************************************************/

define('IN_SCRIPT',1);
define('HESK_PATH','./');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/database.inc.php');

/* Befor anything else block obvious spammers trying to inject email headers */
$pattern = "/\n|\r|\t|%0A|%0D|%08|%09/";
if (preg_match($pattern,$_POST['name']) || preg_match($pattern,$_POST['subject']))
{
	header('HTTP/1.1 403 Forbidden');
    exit();
}

hesk_session_start();

/* A security check */
//hesk_token_check($_POST['token']);

/* Connect to database */
hesk_dbConnect();
if($_POST['savetodb']){ 

/* Generate tracking ID and make sure it's not a duplicate one */
$useChars = 'AEUYBDGHJLMNPQRSTVWXZ123456789';
$trackingID = $useChars{mt_rand(0,29)};
for($i=1;$i<10;$i++)
{
	$trackingID .= $useChars{mt_rand(0,29)};
}

/* Check for duplicate Tracking ID. Small chance, but on some servers... */
$sql = "SELECT `id` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `trackid` = '".hesk_dbEscape($trackingID)."' LIMIT 1";
$res = hesk_dbQuery($sql);

if (hesk_dbNumRows($res) != 0)
{
	/* Tracking ID not unique, let's try another way */
	$trackingID  = $useChars[mt_rand(0,29)];
	$trackingID .= $useChars[mt_rand(0,29)];
	$trackingID .= $useChars[mt_rand(0,29)];
	$trackingID .= $useChars[mt_rand(0,29)];
	$trackingID .= $useChars[mt_rand(0,29)];
	$trackingID .= substr(microtime(), -5);

	$sql = "SELECT `id` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` WHERE `trackid` = '".hesk_dbEscape($trackingID)."' LIMIT 1";
	$res = hesk_dbQuery($sql);

	if (hesk_dbNumRows($res) != 0)
	{
    	$hesk_error_buffer[]=$hesklang['e_tid'];
	}
}

$trackingURL = $hesk_settings['hesk_url'].'/ticket.php?track='.$trackingID;



	$sql = "
INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` (
`trackid`,`name`,`email`,`category`,`priority`,`subject`,`message`,`dt`,`lastchange`,`ip`,`status`,`attachments`,`history`,`custom1`,`custom2`,`custom3`,`custom4`,`custom5`,`custom6`,`custom7`,`custom8`,`custom9`,`custom10`,`custom11`,`custom12`,`custom13`,`custom14`,`custom15`,`custom16`,`custom17`,`custom18`,`custom19`,`custom20`
)
VALUES (
'".hesk_dbEscape($trackingID)."',
'".hesk_dbEscape($_POST['name'])."',
'".hesk_dbEscape($_POST['email'])."',
'".hesk_dbEscape($_POST['category'])."',
'".hesk_dbEscape('1')."',
'".hesk_dbEscape($_POST['subject'])."',
'".hesk_dbEscape($_POST['message'])."',
NOW(),
NOW(),
'".hesk_dbEscape($_SERVER['REMOTE_ADDR'])."',
'0',
'".hesk_dbEscape($myattachments)."',
'',
'".hesk_dbEscape($_POST['custom1'])."',
'".hesk_dbEscape($_POST['custom2'])."',
'".hesk_dbEscape($_POST['custom3'])."',
'".hesk_dbEscape($_POST['custom4'])."',
'".hesk_dbEscape($_POST['custom5'])."',
'".hesk_dbEscape($_POST['custom6'])."',
'".hesk_dbEscape($_POST['custom7'])."',
'".hesk_dbEscape($_POST['custom8'])."',
'".hesk_dbEscape($_POST['custom9'])."',
'".hesk_dbEscape($_POST['custom10'])."',
'".hesk_dbEscape($_POST['custom11'])."',
'".hesk_dbEscape($_POST['custom12'])."',
'".hesk_dbEscape($_POST['custom13'])."',
'".hesk_dbEscape($_POST['custom14'])."',
'".hesk_dbEscape($_POST['custom15'])."',
'".hesk_dbEscape($_POST['custom16'])."',
'".hesk_dbEscape($_POST['custom17'])."',
'".hesk_dbEscape($_POST['custom18'])."',
'".hesk_dbEscape($_POST['custom19'])."',
'".hesk_dbEscape($_POST['custom20'])."'
)
";

$result = hesk_dbQuery($sql);

}
?>
<html>
<title>Test Account Signup  Form</title>
<head>

</head>
<body>
	<fieldset><legend>Sign up for an account!</legend>
    
    <form action="newaccount-signup.php" method="post">
    <input type="hidden" name="savetodb" value="true">
    <input type="hidden" name="category" value="4" />
    <input type="hidden" name="subject" value="New Account Signup" />
    <input type="hidden" name="custom20" value="4374238947HDHDU" />
     <input type="hidden" name="message" value="date: <?=date('m/d/y f:g a')?>" />
    <table>
    	<tr>
        	<td>Name</td>
            <td><input type="text" name="name" /></td>
        </tr>
        <tr>
        	<td>Email</td>
            <td><input type="text" name="email" /></td>
        </tr>
    	<tr>
        	<td>Billing Cycle</td>
            <td><select name="custom19">
            		<option value="Monthly">Monthly</option>
                    <option value="Annually">Annually</option>
                </select>
            </td>
        </tr>
        <tr>
        	<td>Account Type</td>
            <td>
                <label><input type="radio" name="custom18" value="Cheap">Cheap</label>
                    <br />
                <label><input type="radio" name="custom18" value="Premium">Premium</label>
                    <br />
                <label><input type="radio" name="custom18" value="Custom">Custom</label>
            </td>
        </tr>
        <tr>
        	<td>&nbsp;</td>
            <td>
               <input type="submit" value="Submit">
            </td>
        </tr>
    </table>
    
    </form>
    </fieldset>
</body>
</html>

