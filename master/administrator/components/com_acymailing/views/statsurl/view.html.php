<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class StatsurlViewStatsurl extends JView
{
	var $searchFields = array('b.subject','a.mailid','a.urlid','c.name','c.url','a.click');
	var $selectFields = array('b.subject','a.mailid','a.urlid','c.name','c.url','COUNT(a.click) as uniqueclick','SUM(a.click) as totalclick');
	var $detailSearchFields = array('b.subject','a.mailid','a.urlid','a.subid','c.name','c.url','d.name','d.email');
	var $detailSelectFields = array('d.*','a.*','b.subject','c.name as urlname','c.url');
	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function listing(){
		$app =& JFactory::getApplication();
		JHTML::_('behavior.modal','a.modal');
		$pageInfo = null;
		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName().$this->getLayout();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order',	'','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$selectedMail = $app->getUserStateFromRequest( $paramBase."filter_mail",'filter_mail',0,'int');
		$selectedUrl = $app->getUserStateFromRequest( $paramBase."filter_url",'filter_url',0,'int');
		$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	=& JFactory::getDBO();
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$this->searchFields)." LIKE $searchVal";
		}
		if(!empty($selectedMail)) $filters[] = 'a.mailid = '.$selectedMail;
		if(!empty($selectedUrl)) $filters[] = 'a.urlid = '.$selectedUrl;
		$query = 'SELECT SQL_CALC_FOUND_ROWS '.implode(' , ',$this->selectFields);
		$query .= ' FROM '.acymailing::table('urlclick').' as a';
		$query .= ' LEFT JOIN '.acymailing::table('mail').' as b on a.mailid = b.mailid';
		$query .= ' LEFT JOIN '.acymailing::table('url').' as c on a.urlid = c.urlid';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (',$filters).')';
		$query .= ' GROUP BY a.mailid,a.urlid';
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$database->setQuery($query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		$database->setQuery('SELECT FOUND_ROWS()');
		$pageInfo->elements->total = $database->loadResult();
		if(!empty($pageInfo->search)){
			$rows = acymailing::search($pageInfo->search,$rows);
		}
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		$filtersType = null;
		$mailType = acymailing::get('type.urlmail');
		$urlType = acymailing::get('type.url');
		$filtersType->mail = $mailType->display('filter_mail',$selectedMail);
		$filtersType->url = $urlType->display('filter_url',$selectedUrl);
		acymailing::setTitle(JText::_('CLICK_STATISTICS'),'stats','statsurl');
		$bar = & JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Link', 'back', JText::_('GLOBAL_STATISTICS'), acymailing::completeLink('stats') );
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','statsurl-listing');
		$this->assignRef('filters',$filtersType);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
	}
	function form(){
		$urlid = acymailing::getCID('urlid');
		$urlClass = acymailing::get('class.url');
		$this->assignRef('url',$urlClass->get($urlid));
	}
	function detaillisting(){
		require(dirname(__FILE__).DS.'view.detaillisting.php');
	}
}