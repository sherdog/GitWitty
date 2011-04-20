<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<META NAME="robots" CONTENT="index,follow">
	<title><!--config_gl_site_name--></title>
	<link rel="stylesheet" type="text/css" href="./templates/style.css">
</head>
<body leftmargin="0" topmargin="0">
	<!--alert-->
	<div id="lz_chat_navigation_sub"></div>
	<div id="lz_chat_loading"><br><br><br><br><!--lang_client_loading--> ...</div>
	<!--errors-->
	<div id="lz_chat_login">
	<br>
		<form name="lz_login_form" method="post" action="./<!--file_chat-->?template=lz_chat_frame.3.2.chat" target="lz_chat_frame.3.2">
		<table align="center" cellpadding="5" cellspacing="0" width="100%" border="0">
			<tr>
				<td align="center" valign="top">	
					<table cellpadding="5" height="0" width="100%">
						<tr>
							<td align="center" id="lz_chat_login_info_field"><!--info_text--></td>
						</tr>
					</table>
					<div id="lz_chat_login_values"><!--login_trap--></div>
					<div id="lz_chat_login_details" style="display:none;">
						<!--chat_login_inputs-->
						<table cellpadding="2" cellspacing="2" style="display:block;width:410px;<!--group_select_visibility-->">
							<tr>
								<td class="lz_chat_form_field"><strong><!--lang_client_group-->:</strong></td>
								<td valign="middle">
									<table cellpadding="0" cellspacing="0">
										<tr>
											<td><select id="lz_chat_login_groups" name="intgroup" onChange="top.lz_chat_change_group();"></select></td>
											<td width="25" align="right"><img id="lz_chat_groups_loading" src="./images/icon_network.gif" alt="" width="16" height="16" border="0">
										</tr>
									</table>
								</td>
							</tr>
						</table>
						<table cellpadding="2" cellspacing="2"  style="display:block;width:410px;">
							<tr>
								<td class="lz_chat_form_field_empty">&nbsp;</td>
								<td><input type="button" onclick="top.lz_chat_check_login_inputs();" id="lz_chat_login_button" disabled></td>
							</tr>
							<tr>
								<td class="lz_chat_form_field_empty">&nbsp;</td>
								<td><span class="lz_index_red" id="lz_chat_login_mandatory" style="display:none;">* <!--lang_client_required_field--></span></td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
		</table>
		</form>
	</div>
</body>
</html>
