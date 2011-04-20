<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
defined('_JEXEC') or die('Restricted access');
class plgAcymailingTablecontents extends JPlugin
{
	var $noResult = array();
	function plgAcymailingTablecontents(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin =& JPluginHelper::getPlugin('acymailing', 'tablecontents');
			$this->params = new JParameter( $plugin->params );
		}
    }
    function acymailing_replaceusertagspreview(&$email,&$user){
		return $this->acymailing_replaceusertags($email,$user);
	}
	 function acymailing_getPluginType() {
	     $onePlugin = null;
	     $onePlugin->name = JText::_('ACY_TABLECONTENTS');
	     $onePlugin->function = 'acymailingtablecontents_show';
	     $onePlugin->help = 'plugin-tablecontents';
	     return $onePlugin;
	}
	function acymailingtablecontents_show(){
		$contenttype = array();
		$contenttype[] = JHTML::_('select.option', '',JText::_('ACY_EXISTINGANCHOR'));
		for($i = 1;$i<6;$i++){
			$contenttype[] = JHTML::_('select.option', "|type:h".$i,'H'.$i);
		}
		$contenttype[] = JHTML::_('select.option', 'class',JText::_('CLASS_NAME'));
		?>
    <script language="javascript" type="text/javascript">
    <!--
     function updateTag(){
        var tag = '{tableofcontents';
		if(document.adminForm.contenttype.value){
			if(document.adminForm.contenttype.value == 'class'){
				document.adminForm.classvalue.style.display = '';
				tag += '|class:'+document.adminForm.classvalue.value;
			}else{
				document.adminForm.classvalue.style.display = 'none';
				tag += document.adminForm.contenttype.value;
			}
		}
        tag += '}';
        setTag(tag);
      }
    //-->
    </script>
    <table width="100%" class="adminform">
    	<tr><td><?php echo JText::_('ACY_GENERATEANCHOR'); ?></td><td><?php echo JHTML::_('select.genericlist', $contenttype, 'contenttype' , 'size="1" onchange="updateTag();"', 'value', 'text'); ?><input style="display:none" onchange="updateTag();" name="classvalue" /></td></tr>
    </table>
<?php
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration("window.addEvent('domready', function(){ updateTag(); });");
}
	function acymailing_replaceusertags(&$email,&$user){
		if(isset($this->noResult[intval($email->mailid)])) return;
		$match = '#{tableofcontents(.*)}#Ui';
		$variables = array('body','altbody');
	    $found = false;
	    foreach($variables as $var){
	      if(empty($email->$var)) continue;
	      $found = preg_match_all($match,$email->$var,$results[$var]) || $found;
	      if(empty($results[$var][0])) unset($results[$var]);
	    }
	    if(!$found){
	    	$this->noResult[intval($email->mailid)] = true;
	    	 return;
	    }
	    $mailerHelper = acymailing::get('helper.mailer');
	    $htmlreplace = array();
	    $textreplace = array();
	    foreach($results as $var => $allresults){
	      foreach($allresults[0] as $i => $oneTag){
	        if(isset($htmlreplace[$oneTag])) continue;
	        $article = $this->_generateTable($allresults,$i,$email);
	        $htmlreplace[$oneTag] = $article;
	        $textreplace[$oneTag] = $mailerHelper->textVersion($article);
	      }
	    }
	    $email->body = str_replace(array_keys($htmlreplace),$htmlreplace,$email->body);
	    $email->altbody = str_replace(array_keys($textreplace),$textreplace,$email->altbody);
	}
	function _generateTable(&$results,$i,&$email){
	    $arguments = explode('|',strip_tags($results[1][$i]));
	    $tag = null;
	    for($i=1,$a=count($arguments);$i<$a;$i++){
	      $args = explode(':',$arguments[$i]);
	      if(isset($args[1])){
	        $tag->$args[0] = $args[1];
	      }else{
	        $tag->$args[0] = true;
	      }
	    }

		if(!empty($tag->type)){
			preg_match_all('#<'.$tag->type.'[^>]*>((?!</ *'.$tag->type.'>).)*</ *'.$tag->type.'>#Uis',$email->body,$anchorresults);
		}elseif(!empty($tag->class)){
			preg_match_all('#<[^>]*class="'.$tag->class.'"[^>]*>(<[^>]*>|[^<>])*</.*>#Uis',$email->body,$anchorresults);
			$tag->type = 'item';
		}else{
			preg_match_all('#<a[^>]*name="([^">]*)"[^>]*>((?!</ *a>).)*</ *a>#Uis',$email->body,$anchorresults);
		}
		if(empty($anchorresults)) return '';
		$updateMail = array();
		$links = array();
		foreach($anchorresults[0] as $i => $oneContent){
			$linktext = strip_tags($oneContent);
			if(empty($linktext)) continue;
			if(empty($tag->type)){
				$links[] = '<a href="#'.$anchorresults[1][$i].'" class="oneitem" >'.$linktext.'</a>';
			}else{
				$links[] = '<a href="#'.$tag->type.$i.'" class="oneitem" >'.$linktext.'</a>';
				$updateMail[$oneContent] = $oneContent.'<a name="'.$tag->type.$i.'"></a>';
			}
		}
		if(empty($links)) return '';
		if(!empty($updateMail)) $email->body = str_replace(array_keys($updateMail),$updateMail,$email->body);
		return '<div class="tableofcontents">'.implode('<br />',$links).'</div>';
	}
}//endclass