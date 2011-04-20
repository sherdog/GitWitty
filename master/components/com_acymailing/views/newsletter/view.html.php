<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class NewsletterViewNewsletter extends JView
{
	var $type = 'news';
	var $ctrl = 'newsletter';
	var $nameListing = 'NEWSLETTERS';
	var $nameForm = 'NEWSLETTER';
	function display($tpl = null)
	{
		$doc =& JFactory::getDocument();
		$doc->addStyleSheet( ACYMAILING_CSS.'frontendedition.css' );
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function form(){
		JHTML::_('behavior.modal','a.modal');
		$templateClass = acymailing::get('class.template');
		$mailid = acymailing::getCID('mailid');
		$my = JFactory::getUser();
		if(!empty($mailid)){
			$mailClass = acymailing::get('class.mail');
			$mail = $mailClass->get($mailid);
			if(!empty($mail->tempid)){
				$myTemplate = $templateClass->get($mail->tempid);
			}
		}else{
			$config =& acymailing::config();
			$mail->created = time();
			$mail->published = 0;
			$mail->visible = 1;
			$mail->html = 1;
			$mail->body = '';
			$mail->altbody = '';
			$mail->tempid = 0;
			$templateid = JRequest::getInt('templateid');
			if(empty($templateid)){
				$myTemplate = $templateClass->getDefault();
			}else{
				$myTemplate = $templateClass->get($templateid);
			}
			if(!empty($myTemplate->tempid)){
				$mail->body = $myTemplate->body;
				$mail->altbody = $myTemplate->altbody;
				$mail->tempid = $myTemplate->tempid;
				$mail->subject = $myTemplate->subject;
				$mail->replyname = $myTemplate->replyname;
				$mail->replyemail = $myTemplate->replyemail;
				$mail->fromname = $myTemplate->fromname;
				$mail->fromemail = $myTemplate->fromemail;
			}
			if($config->get('frontend_sender',0)){
				$mail->fromname = $my->name;
				$mail->fromemail = $my->email;
			}else{
				if(empty($mail->fromname)) $mail->fromname = $config->get('from_name');
				if(empty($mail->fromemail)) $mail->fromemail = $config->get('from_email');
			}
			if($config->get('frontend_reply',0)){
				$mail->replyname = $my->name;
				$mail->replyemail = $my->email;
			}else{
				if(empty($mail->replyname)) $mail->replyname = $config->get('reply_name');
				if(empty($mail->replyemail)) $mail->replyemail = $config->get('reply_email');
			}
		}
		$sentbyname = '';
		if(!empty($mail->sentby)){
			$db =& JFactory::getDBO();
			$db->setQuery('SELECT `name` FROM `#__users` WHERE `id`= '.intval($mail->sentby).' LIMIT 1');
			$sentbyname = $db->loadResult();
		}
		$this->assignRef('sentbyname',$sentbyname);
		if(JRequest::getVar('task','') == 'replacetags'){
			$mailerHelper = acymailing::get('helper.mailer');
			JPluginHelper::importPlugin('acymailing');
			$dispatcher = &JDispatcher::getInstance();
			$dispatcher->trigger('acymailing_replacetags',array(&$mail));
			if(!empty($mail->altbody)) $mail->altbody = $mailerHelper->textVersion($mail->altbody,false);
		}
		$values = null;
		$listmailClass = acymailing::get('class.listmail');
		$lists = $listmailClass->getLists($mailid);
		$copyAllLists = $lists;
		foreach($copyAllLists as $listid => $oneList){
			if(!$oneList->published OR empty($my->id)){
				unset($lists[$listid]);
				continue;
			}
			if($oneList->access_manage == 'all') continue;
			if((int)$my->id == (int)$oneList->userid) continue;
			if(!acymailing::isAllowed($oneList->access_manage)){
				unset($lists[$listid]);
				continue;
			}
		}
		if(empty($lists)){
			$app =& JFactory::getApplication();
			$app->enqueueMessage('You don\'t have the rights to add or edit an e-mail','error');
			$app->redirect(acymailing::completeLink('lists',false,true));
		}
		jimport('joomla.html.pane');
		$tabs	=& JPane::getInstance('tabs');
		$values->maxupload = (acymailing::bytes(ini_get('upload_max_filesize')) > acymailing::bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize');
		$toggleClass = acymailing::get('helper.toggle');
		$editor = acymailing::get('helper.editor');
		$editor->setTemplate($mail->tempid);
		$editor->name = 'editor_body';
		$editor->content = $mail->body;
		$editor->prepareDisplay();
		$js = "function updateAcyEditor(htmlvalue){";
			$js .= 'if(htmlvalue == \'0\'){window.document.getElementById("htmlfieldset").style.display = \'none\'}else{window.document.getElementById("htmlfieldset").style.display = \'block\'}';
		$js .= '}';
		$js .='window.addEvent(\'load\', function(){ updateAcyEditor('.$mail->html.'); });';
		$script = 'function addFileLoader(){
		var divfile=window.document.getElementById("loadfile");
		var input = document.createElement(\'input\');
		input.type = \'file\';
		input.size = \'30\';
		input.name = \'attachments[]\';
		divfile.appendChild(document.createElement(\'br\'));
		divfile.appendChild(input);}
		';
		$script .= 'function submitbutton(pressbutton){
						if (pressbutton == \'cancel\') {
							submitform( pressbutton );
							return;
						}';
		$script .= 'if(window.document.getElementById("subject").value.length < 2){alert(\''.JText::_('ENTER_SUBJECT',true).'\'); return false;}';
		$script .= $editor->jsCode();
		$script .= 'submitform( pressbutton );}';
		$script .= "function changeTemplate(newhtml,newtext,newsubject,stylesheet,fromname,fromemail,replyname,replyemail,tempid){
			if(newhtml.length>2){".$editor->setContent('newhtml')."}
			var vartextarea =$('altbody'); if(newtext.length>2) vartextarea.innerHTML = newtext;
			document.getElementById('tempid').value = tempid;
			if(fromname.length>1){document.getElementById('fromname').value = fromname;}
			if(fromemail.length>1){document.getElementById('fromemail').value = fromemail;}
			if(replyname.length>1){document.getElementById('replyname').value = replyname;}
			if(replyemail.length>1){document.getElementById('replyemail').value = replyemail;}
			if(newsubject.length>1){document.getElementById('subject').value = newsubject;}
		}
		";
		$script .= "function insertTag(tag){ try{jInsertEditorText(tag,'editor_body'); return true;} catch(err){alert('Your editor does not enable AcyMailing to automatically insert the tag, please copy/paste it manually in your Newsletter'); return false;}}";
		$script .= "window.addEvent('domready', function(){ mytoolbar = $('toolbar'); if(!mytoolbar) return true; mytoolbar.addEvent('mouseover', function(){ try{IeCursorFix();}catch(e){} }); }); ";
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js.$script );
		$toggleClass = acymailing::get('helper.toggle');
		$toggleClass->ctrl = 'newsletter';
		$toggleClass->extra = '&listid='.JRequest::getInt('listid');
		$this->assignRef('lists',$lists);
		$this->assignRef('editor',$editor);
		$this->assignRef('mail',$mail);
		$this->assignRef('tabs',$tabs);
		$this->assignRef('values',$values);
		$this->assignRef('toggleClass',$toggleClass);
	}
	function preview(){
		JHTML::_('behavior.modal','a.modal');
		$mailid = acymailing::getCID('mailid');
		$app =& JFactory::getApplication();
		$mailerHelper = acymailing::get('helper.mailer');
		$mail = $mailerHelper->load($mailid);
		$user =& JFactory::getUser();
		$userClass = acymailing::get('class.subscriber');
		$receiver = $userClass->get($user->email);
		$mail->sendHTML = true;
		$mailerHelper->dispatcher->trigger('acymailing_replaceusertagspreview',array(&$mail,&$receiver));
		if(!empty($mail->altbody)) $mail->altbody = $mailerHelper->textVersion($mail->altbody,false);
		$listmailClass = acymailing::get('class.listmail');
		$lists = $listmailClass->getReceivers($mail->mailid,true,false);
		$receiversClass = acymailing::get('type.testreceiver');
		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName();
		$infos = null;
		$infos->receiver_type = $app->getUserStateFromRequest( $paramBase.".receiver_type", 'receiver_type', '','string' );
		$infos->test_html = $app->getUserStateFromRequest( $paramBase.".test_html", 'test_html', 1,'int' );
		$infos->test_email = $app->getUserStateFromRequest( $paramBase.".test_email", 'test_email', '','string' );
		$this->assignRef('lists',$lists);
		$this->assignRef('infos',$infos);
		$this->assignRef('receiverClass',$receiversClass);
		$this->assignRef('mail',$mail);
	}
	function stats(){
		include(ACYMAILING_BACK.'views'.DS.'diagram'.DS.'view.html.php');
		$diagramClass = new DiagramViewDiagram();
		$doc =& JFactory::getDocument();
		$doc->addScript(((empty($_SERVER['HTTPS']) OR strtolower($_SERVER['HTTPS']) != "on" ) ? 'http://' : 'https://')."www.google.com/jsapi");
		$diagramClass->mailing();
		$this->assignRef('mailing',$diagramClass->mailing);
		$this->assignRef('mailingstats',$diagramClass->mailingstats);
		$this->assignRef('openclick',$diagramClass->openclick);
		$this->assignRef('openclickday',$diagramClass->openclickday);
	}
	function scheduleconfirm(){
		JRequest::setVar('tmpl','component');
		$mailid = acymailing::getCID('mailid');
		$listmailClass = acymailing::get('class.listmail');
		$mailClass = acymailing::get('class.mail');
		$this->assignRef('lists',$listmailClass->getReceivers($mailid));
		$this->assignRef('mail',$mailClass->get($mailid));
	}
}