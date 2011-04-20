<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class DiagramViewDiagram extends JView
{
	function display($tpl = null)
	{
		$doc =& JFactory::getDocument();
		$doc->addScript(((empty($_SERVER['HTTPS']) OR strtolower($_SERVER['HTTPS']) != "on" ) ? 'http://' : 'https://')."www.google.com/jsapi");
		$function = $this->getLayout();
		$this->setLayout('diagram');
		if(method_exists($this,$function)) $this->$function();
		$filters = null;
		$diagramType = acymailing::get('type.diagram');
		$filters->task = $diagramType->display('task',JRequest::getCmd('task'));
		$this->assignRef('filters',$filters);
		$bar = & JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Link', 'back', JText::_('GLOBAL_STATISTICS'), acymailing::completeLink('stats') );
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','diagram-'.JRequest::getCmd('task','lists'));
		parent::display($tpl);
	}
	function mailing(){
		$mailid = JRequest::getInt('mailid');
		if(empty($mailid)) return;
		$db =& JFactory::getDBO();
		$db->setQuery('SELECT * FROM '.acymailing::table('stats').' WHERE mailid = '.$mailid);
		$mailingstats = $db->loadObject();
		if(empty($mailingstats->mailid)) return;
		$mailClass = acymailing::get('class.mail');
		$mailing = $mailClass->get($mailid);

		$db->setQuery('SELECT COUNT(*) FROM `#__acymailing_queue` WHERE `mailid` = '.$mailingstats->mailid.' GROUP BY `mailid`');
		$mailingstats->queue = $db->loadResult();
		$db->setQuery('SELECT min(opendate) as minval, max(opendate) as maxval FROM '.acymailing::table('userstats').' WHERE opendate > 0 AND mailid = '.$mailid);
		$datesOpen = $db->loadObject();
		$db->setQuery('SELECT min(`date`) as minval, max(`date`) as maxval FROM '.acymailing::table('urlclick').' WHERE  mailid = '.$mailid);
		$datesClick = $db->loadObject();
		$spaces = array();
		$intervals = 10;
		$minDate = min($datesOpen->minval,$datesClick->minval);
		if(empty($minDate)) $minDate = max($datesOpen->minval,$datesClick->minval);
		$maxDate = max($datesOpen->maxval,$datesClick->maxval)+1;
		$delay = ($maxDate - $minDate)/$intervals;
		for($i=0;$i<$intervals;$i++){
			$spaces[$i] = (int) ($minDate + $delay*$i);
		}
		$spaces[$intervals] = $maxDate;
		$openclick = null;
		$openclick->open = array();
		$openclick->click = array();
		$openclick->legend = array();
		$dateFormat = '%d %B %Y';
		if(date('Y',$maxDate) == date('Y',$minDate)){
			$dateFormat = '%d %B';
			if(date('m',$maxDate) == date('m',$minDate)){
				$dateFormat = '%A %d';
				if($delay < 172800){
					$dateFormat = '%a %d %H:%M';
				}
			}
		}
		$openresults = array();
		$legendX = array();
		for($i = 0; $i<=$intervals; $i++ ){
			if($i%2 == 0) $openclick->legend[$i] = acymailing::getDate($spaces[$i],$dateFormat);
			$db->setQuery('SELECT count(subid) FROM '.acymailing::table('userstats').' WHERE opendate < '.$spaces[$i].' AND opendate > 0 AND mailid = '.$mailid);
			$openclick->open[$i] = (int) $db->loadResult();
			$db->setQuery('SELECT count(subid) FROM '.acymailing::table('urlclick').' WHERE date < '.$spaces[$i].' AND mailid = '.$mailid);
			$openclick->click[$i] = (int) $db->loadResult();
		}
		$config =& JFactory::getConfig();
		$timeoffset = $config->getValue('config.offset');
		$diffTime =  $timeoffset - date('Z');
		$groupingFormat = '%Y %j';
		$phpformat = '%d %B';
		$diff = 86400;
		if($delay < 3600){
			$groupingFormat = '%Y %j %H';
			$phpformat = '%a %d %H';
			$diff = 3600;
		}
		$query = "SELECT DATE_FORMAT(FROM_UNIXTIME(a.opendate + $diffTime),'$groupingFormat') AS openday, a.opendate, COUNT(a.subid) AS totalopen ";
		$query .= 'FROM #__acymailing_userstats AS a WHERE opendate > 0 AND mailid = '.$mailid;
		$query .= ' GROUP BY openday ORDER BY openday DESC LIMIT 10';
		$db->setQuery($query);
		$datesOpen = $db->loadObjectList('openday');
		$query = "SELECT DATE_FORMAT(FROM_UNIXTIME(a.date + $diffTime),'$groupingFormat') AS clickday, a.date, COUNT(a.subid) AS totalclick ";
		$query .= 'FROM #__acymailing_urlclick AS a WHERE mailid = '.$mailid;
		$query .= ' GROUP BY clickday ORDER BY clickday DESC LIMIT 10';
		$db->setQuery($query);
		$datesClick = $db->loadObjectList('clickday');
		$openclickday = array();
		foreach($datesOpen as $time => $oneDate){
			$openclickday[$time] = array();
			$openclickday[$time]['date'] = acymailing::getDate($oneDate->opendate,$phpformat);
			$openclickday[$time]['nextdate'] = acymailing::getDate($oneDate->opendate-$diff,$phpformat);
			$openclickday[$time]['open'] = $oneDate;
		}
		foreach($datesClick as $time => $oneDate){
			if(!isset($openclickday[$time])){
				$openclickday[$time] = array();
				$openclickday[$time]['date'] = acymailing::getDate($oneDate->date,$phpformat);
				$openclickday[$time]['nextdate'] = acymailing::getDate($oneDate->date-$diff,$phpformat);
			}
			$openclickday[$time]['click'] = $oneDate;
		}
		krsort($openclickday);
		$this->assignRef('mailing',$mailing);
		$this->assignRef('mailingstats',$mailingstats);
		$this->assignRef('openclick',$openclick);
		$this->assignRef('openclickday',$openclickday);
		$this->setLayout('mailing');
	}
	function lists(){
		acymailing::setTitle(JText::_('CHARTS'),'stats','diagram&task=lists');
		$listsClass = acymailing::get('class.list');
		$lists = $listsClass->getLists('listid');
		$db =& JFactory::getDBO();
		$db->setQuery('SELECT listid, count(subid) as total FROM '.acymailing::table('listsub').' WHERE `status` = 1 group by listid');
		$subscribers = $db->loadObjectList('listid');
		$db->setQuery('SELECT listid, count(subid) as total FROM '.acymailing::table('listsub').' WHERE `status` = -1 group by listid');
		$unsubscribers = $db->loadObjectList('listid');
		$db->setQuery('SELECT listid, count(subid) as total FROM '.acymailing::table('listsub').' WHERE `status` = 2 group by listid');
		$waitsub = $db->loadObjectList('listid');
		$values = null;
		$values->labels = array();
		$values->subColumn = array();
		$values->unsubColumn = array();
		$values->waitColumn = array();
		foreach($lists as $listid => $oneList){
			$values->labels[] = addslashes($oneList->name);
			$values->subColumn[] = empty($subscribers[$listid]->total) ? 0 : (int) $subscribers[$listid]->total;
			$values->unsubColumn[] = empty($unsubscribers[$listid]->total) ? 0 : (int) $unsubscribers[$listid]->total;
			$values->waitColumn[] = empty($waitsub[$listid]->total) ? 0 : (int) $waitsub[$listid]->total;
		}
		?>
		<script language="JavaScript" type="text/javascript">
			function drawChart(){
				var dataTable = new google.visualization.DataTable();
				var checkboxes = document.adminForm.lists;
				nbvalues = 0;
				if(checkboxes.length){
					for (var i=0; i < checkboxes.length; i++){
					   if (checkboxes[i].checked){
					     nbvalues++;
						}
					}
				}else{
					if(checkboxes.checked) nbvalues++;
				}
				dataTable.addRows(nbvalues);
				a = 0;
				dataTable.addColumn('string');
				dataTable.addColumn('number','<?php echo JText::_('SUBSCRIBERS',true); ?>');
				dataTable.addColumn('number','<?php echo JText::_('UNSUBSCRIBERS',true); ?>');
				<?php if(!empty($maxWait)) echo "dataTable.addColumn('number','".JText::_('PENDING_SUBSCRIPTION',true)."');";
				 foreach($values->subColumn as $i => $nbsubs){
				 	echo "if((checkboxes[$i] && checkboxes[$i].checked) || (!checkboxes[$i] && checkboxes.checked)){";
				 	echo "dataTable.setValue(a, 0, '".$values->labels[$i]."');";
					echo "dataTable.setValue(a, 1, ".$values->subColumn[$i].");";
					echo "dataTable.setValue(a, 2, ".$values->unsubColumn[$i].");";
					if(!empty($maxWait)) echo "dataTable.setValue(a, 3, ".$values->waitColumn[$i].");";
					echo "a++; }";
				}?>
				var vis = new google.visualization.ColumnChart(document.getElementById('acychart'));
		        var options = {
		        	width:1200,
		    		height:500,
		    		legend:'bottom',
		    		colors:['#40A640','#A42B37'<?php if(!empty($maxWait)) echo ",'#FF9900'"; ?>],
		    		title: '<?php echo JText::_('NB_SUB_UNSUB',true); ?>',
		    		legendTextStyle: {color:'#333333'}
		        };
		        vis.draw(dataTable, options);
			}
			google.load("visualization", "1", {packages:["corechart"]});
			google.setOnLoadCallback(drawChart);
		</script>
		<?php
		$this->assignRef('lists',$lists);
	}
	function subscription(){
		acymailing::setTitle(JText::_('CHARTS'),'stats','diagram&task=subscription');
		$listsClass = acymailing::get('class.list');
		$lists = $listsClass->getLists('listid');
		$db =& JFactory::getDBO();
		$db->setQuery('SELECT min(subdate) as minsubdate, min(unsubdate) as minunsubdate FROM '.acymailing::table('listsub'));
		$dates = $db->loadObject();
		$spaces = array();
		$intervals = 10;
		$dates->maxsubdate = time();
		$delay = ($dates->maxsubdate - $dates->minsubdate)/$intervals;
		for($i=0;$i<$intervals;$i++){
			$spaces[$i] = (int) ($dates->minsubdate + $delay*$i);
		}
		$spaces[$intervals] = $dates->maxsubdate;
		$dateFormat = '%d %B %Y';
		if(date('Y',$dates->maxsubdate) == date('Y',$dates->minsubdate)){
			$dateFormat = '%d %B';
			if(date('m',$dates->maxsubdate) == date('m',$dates->minsubdate)){
				$dateFormat = '%A %d';
				if($delay < 172800){
					$dateFormat = '%a %d %H:%M';
				}
			}
		}
		$results = array();
		$legendX = array();
		for($i = 0; $i<=$intervals; $i++ ){
			$legendX[$i] = addslashes(acymailing::getDate($spaces[$i],$dateFormat));
			$db->setQuery('SELECT count(subid) as total, listid FROM '.acymailing::table('listsub').' WHERE `status` != 2 AND `subdate` < '.$spaces[$i].' AND (`status` = 1 OR `unsubdate`>'.$spaces[$i].') GROUP BY listid');
			$results[$i] = $db->loadObjectList('listid');
		}
		$colors = array();
		?>
		<script language="JavaScript" type="text/javascript">
			function drawChart(){
				var dataTable = new google.visualization.DataTable();
				dataTable.addRows(<?php echo $intervals+1; ?>);
				dataTable.addColumn('string');
				var checkboxes = document.adminForm.lists;
				a = 1;
				mycolors = '[';
				<?php
				foreach($legendX as $i => $oneLegend){
					echo "dataTable.setValue($i, 0, '$oneLegend');"."\n";
				}
				$a = 0;
				foreach($lists as $listid => $oneList){
					echo "if((checkboxes[$a] && checkboxes[$a].checked) || (!checkboxes[$a] && checkboxes.checked)){ dataTable.addColumn('number','".addslashes($oneList->name)."');";
					for($i = 0; $i<=$intervals; $i++ ){
						$val = intval(@$results[$i][$listid]->total);
						echo "dataTable.setValue($i, a, $val);";
					}
					if(empty($oneList->color)) $oneList->color = '#333333';
					echo "a++; if(mycolors.length > 1) mycolors += ','; mycolors += '\'".addslashes($oneList->color)."\'';}"."\n";
					$a++;
				}?>
				mycolors += ']';
				var vis = new google.visualization.LineChart(document.getElementById('acychart'));
				var options = {
					width:1200,
		    		height:500,
		    		legend:'bottom',
		    		colors:eval(mycolors),
		    		title: '<?php echo addslashes(JText::_('SUB_HISTORY')); ?>',
		    		legendTextStyle: {color:'#333333'}
				}
		        vis.draw(dataTable, options);
			}
			google.load("visualization", "1", {packages:["corechart"]});
			google.setOnLoadCallback(drawChart);
		</script>
		<?php
		$this->assignRef('lists',$lists);
	}
}