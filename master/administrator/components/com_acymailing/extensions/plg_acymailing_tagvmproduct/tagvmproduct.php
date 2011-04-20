<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class plgAcymailingTagvmproduct extends JPlugin
{
	function plgAcymailingTagvmproduct(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin =& JPluginHelper::getPlugin('acymailing', 'tagvmproduct');
			$this->params = new JParameter( $plugin->params );
		}
	}
	 function acymailing_getPluginType() {
	 	$onePlugin = null;
	 	$onePlugin->name = JText::_('ACY_PRODUCTS');
	 	$onePlugin->function = 'acymailingtagvmproduct_show';
	 	$onePlugin->help = 'plugin-tagvmproduct';
	 	return $onePlugin;
	 }
	 function acymailingtagvmproduct_show(){
		$app =& JFactory::getApplication();
		$contentType = array();
		$contentType[] = JHTML::_('select.option', "|type:title",JText::_('TITLE_ONLY'));
		$contentType[] = JHTML::_('select.option', "|type:intro",JText::_('INTRO_ONLY'));
		$contentType[] = JHTML::_('select.option', "|type:full",JText::_('FULL_TEXT'));
		$pageInfo = null;
		$paramBase = ACYMAILING_COMPONENT.'.tagvmproduct';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order',	'a.product_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$pageInfo->lang = $app->getUserStateFromRequest( $paramBase.".lang", 'lang','','string' );
		$pageInfo->contenttype = $app->getUserStateFromRequest( $paramBase.".contenttype", 'contenttype','|type:full','string' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$db =& JFactory::getDBO();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$db->getEscaped($pageInfo->search).'%\'';
			$filters[] = "a.product_id LIKE $searchVal OR a.product_s_desc LIKE $searchVal OR a.product_name LIKE $searchVal OR a.product_sku LIKE $searchVal";
		}
		$whereQuery = '';
		if(!empty($filters)){
			$whereQuery = ' WHERE ('.implode(') AND (',$filters).')';
		}
		$query = 'SELECT SQL_CALC_FOUND_ROWS a.product_id,a.product_s_desc,a.product_sku,a.product_name FROM '.acymailing::table('vm_product',false).' as a';
		if(!empty($whereQuery)) $query.= $whereQuery;
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$db->setQuery($query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $db->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = acymailing::search($pageInfo->search,$rows);
		}
		$db->setQuery('SELECT FOUND_ROWS()');
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		jimport('joomla.html.pane');
		$tabs	=& JPane::getInstance('tabs');
		echo $tabs->startPane( 'vmproduct_tab');
		echo $tabs->startPanel( JText::_( 'ACY_PRODUCTS' ), 'vm_product');
	?>
		<br style="font-size:1px"/>
			<script language="javascript" type="text/javascript">
		<!--
			function updateTag(productid){
				tag = '{vmproduct:'+productid;
				for(var i=0; i < document.adminForm.contenttype.length; i++){
				   if (document.adminForm.contenttype[i].checked){ tag += document.adminForm.contenttype[i].value; }
				}
				if(window.document.getElementById('jflang')  && window.document.getElementById('jflang').value != ''){
					tag += '|lang:';
					tag += window.document.getElementById('jflang').value;
				}
				tag += '}';
				setTag(tag);
				insertTag();
			}
		//-->
		</script>
		<table>
			<tr>
				<td width="100%">
					<?php echo JText::_( 'JOOMEXT_FILTER' ); ?>:
					<input type="text" name="search" id="acymailingsearch" value="<?php echo $pageInfo->search;?>" class="text_area" onchange="document.adminForm.submit();" />
					<button onclick="this.form.submit();"><?php echo JText::_( 'JOOMEXT_GO' ); ?></button>
					<button onclick="document.getElementById('acymailingsearch').value='';this.form.submit();"><?php echo JText::_( 'JOOMEXT_RESET' ); ?></button>
				</td>
			</tr>
		</table>
		<table width="100%" class="adminform">
			<tr>
				<td>
					<?php echo JText::_('DISPLAY');?>
				</td>
				<td colspan="2">
				<?php echo JHTML::_('select.radiolist', $contentType, 'contenttype' , 'size="1"', 'value', 'text', $pageInfo->contenttype); ?>
				</td>
				<td>
					<?php $jflanguages = acymailing::get('type.jflanguages');
						echo $jflanguages->display('lang',$pageInfo->lang); ?>
				</td>
			</tr>
		</table>
		<table class="adminlist" cellpadding="1" width="100%">
			<thead>
				<tr>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_( 'FIELD_TITLE'), 'a.product_name', $pageInfo->filter->order->dir,$pageInfo->filter->order->value ); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_( 'ACY_DESCRIPTION'), 'a.product_s_desc', $pageInfo->filter->order->dir,$pageInfo->filter->order->value ); ?>
					</th>
					<th class="title titleid">
						<?php echo JHTML::_('grid.sort',   JText::_( 'ACY_ID' ), 'a.product_id', $pageInfo->filter->order->dir, $pageInfo->filter->order->value ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3">
						<?php echo $pagination->getListFooter(); ?>
						<?php echo $pagination->getResultsCounter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
					$k = 0;
					for($i = 0,$a = count($rows);$i<$a;$i++){
						$row =& $rows[$i];
				?>
					<tr id="content<?php echo $row->product_id?>" class="<?php echo "row$k"; ?>" onclick="updateTag(<?php echo $row->product_id; ?>);" style="cursor:pointer;">
						<td>
						<?php
							echo acymailing::tooltip('SKU : '.$row->product_sku,$row->product_name,'',$row->product_name);
						?>
						</td>
						<td>
						<?php
							echo $row->product_s_desc;
						?>
						</td>
						<td align="center">
							<?php echo $row->product_id; ?>
						</td>
					</tr>
				<?php
						$k = 1-$k;
					}
				?>
			</tbody>
		</table>
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $pageInfo->filter->order->dir; ?>" />
	<?php
	echo $tabs->endPanel();
	echo $tabs->startPanel( JText::_( 'TAG_CATEGORIES' ), 'vm_auto');
	$type = JRequest::getString('type');
	$db->setQuery('SELECT a.*,b.* FROM `#__vm_category` as a LEFT JOIN `#__vm_category_xref` as b ON a.category_id = b.category_child_id ORDER BY `list_order`');
	$categories = $db->loadObjectList('category_id');
	$this->cats = array();
	foreach($categories as $oneCat){
		$this->cats[$oneCat->category_parent_id][] = $oneCat;
	}
		$ordering = array();
		$ordering[] = JHTML::_('select.option', "|order:product_id,DESC",JText::_('ID'));
		$ordering[] = JHTML::_('select.option', "|order:cdate,DESC",JText::_('CREATED_DATE'));
		$ordering[] = JHTML::_('select.option', "|order:mdate,DESC",JText::_('MODIFIED_DATE'));
		$ordering[] = JHTML::_('select.option', "|order:product_name,ASC",JText::_('FIELD_TITLE'));
	?>
		<br style="font-size:1px"/>
	<script language="javascript" type="text/javascript">
		<!--
			var selectedCat = new Array();
			function applyAutoProduct(catid,rowClass){
				if(selectedCat[catid]){
					window.document.getElementById('product_cat'+catid).className = rowClass;
					delete selectedCat[catid];
				}else{
					window.document.getElementById('product_cat'+catid).className = 'selectedrow';
					selectedCat[catid] = 'product';
				}
				updateTagAuto();
			}
			function updateTagAuto(){
				tag = '{autovmproduct:';
				for(var icat in selectedCat){
					if(selectedCat[icat] == 'product'){
						tag += icat+'-';
					}
				}
				for(var i=0; i < document.adminForm.contenttypeauto.length; i++){
				   if (document.adminForm.contenttypeauto[i].checked){ tag += document.adminForm.contenttypeauto[i].value; }
				}
				if(document.adminForm.min_article && document.adminForm.min_article.value && document.adminForm.min_article.value!=0){ tag += '|min:'+document.adminForm.min_article.value; }
				if(document.adminForm.max_article.value && document.adminForm.max_article.value!=0){ tag += '|max:'+document.adminForm.max_article.value; }
				if(document.adminForm.contentorder.value){ tag += document.adminForm.contentorder.value; }
				if(document.adminForm.contentfilter && document.adminForm.contentfilter.value){ tag += document.adminForm.contentfilter.value; }
				if(window.document.getElementById('jflangvm')  && window.document.getElementById('jflangvm').value != ''){
					tag += '|lang:';
					tag += window.document.getElementById('jflangvm').value;
				}
				tag += '}';
				setTag(tag);
			}
		//-->
	</script>
	<table width="100%" class="adminform">
		<tr>
			<td>
				<?php echo JText::_('DISPLAY');?>
			</td>
			<td colspan="2">
			<?php echo JHTML::_('select.radiolist', $contentType, 'contenttypeauto' , 'size="1" onclick="updateTagAuto();"', 'value', 'text', '|type:full'); ?>
			</td>
			<td>
				<?php $jflanguages = acymailing::get('type.jflanguages');
				if(!empty($jflanguages->values)){
					$jflanguages->id = 'jflangvm'; $jflanguages->onclick = 'onchange="updateTagAuto();"'; echo $jflanguages->display('language');
				}?>
			</td>
		</tr>
		<tr>
			<td>
			<?php echo JText::_('MAX_ARTICLE'); ?>
			 </td>
			 <td>
			 	<input name="max_article" size="10" value="" onchange="updateTagAuto();"/>
			</td>
			<td>
				<?php echo JText::_('ACY_ORDER'); ?>
			 </td>
			 <td>
			 	<?php echo JHTML::_('select.genericlist', $ordering, 'contentorder' , 'size="1" onchange="updateTagAuto();"'); ?>
			</td>
		</tr>
		<?php if($type == 'autonews') { ?>
		<tr>
			<td>
			<?php 	echo JText::_('MIN_ARTICLE'); ?>
			 </td>
			 <td>
			 <input name="min_article" size="10" value="1" onchange="updateTagAuto();"/>
			 </td>
			<td>
			<?php echo JText::_('FILTER'); ?>
			 </td>
			 <td>
			 	<?php $filter = acymailing::get('type.contentfilter'); $filter->onclick = 'updateTagAuto();'; echo $filter->display('contentfilter','|filter:created'); ?>
			</td>
		</tr>
		<?php } ?>
	</table>
	<table class="adminlist" cellpadding="1" width="100%">
	<?php $k=0; echo $this->displayChildren(0,$k); ?>
	</table>
	<?php
	echo $tabs->endPanel();
	echo $tabs->endPane();
	 }
	 function displayChildren($parentid,&$k,$level = 0){
	 	if(empty($this->cats[$parentid])) return;
	 	foreach($this->cats[$parentid] as $oneCat){
	 		$k = 1 - $k;
	 		echo '<tr id="product_cat'.$oneCat->category_id.'" class="row'.$k.'" onclick="applyAutoProduct('.$oneCat->category_id.',\'row'.$k.'\');" style="cursor:pointer;"><td>';
			echo str_repeat('- - ',$level).$oneCat->category_name.'</td></tr>';
	 		$this->displayChildren($oneCat->category_id,$k,$level+1);
		}
	 }
	 function acymailing_replacetags(&$email){
	 	$this->_replaceAuto($email);
	 	$this->_replaceProducts($email);
 	}
	function _replaceAuto(&$email){
		$this->acymailing_generateautonews($email);
		if(!empty($this->tags)){
			$email->body = str_replace(array_keys($this->tags),$this->tags,$email->body);
			if(!empty($email->altbody)) $email->altbody = str_replace(array_keys($this->tags),$this->tags,$email->altbody);
		}
	}
	function acymailing_generateautonews(&$email){
		$return = null;
		$return->status = true;
		$return->message = '';
		$time = time();
		$match = '#{autovmproduct:(.*)}#Ui';
		$variables = array('body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}
		if(!$found) return $return;
		$this->tags = array();
		$db =& JFactory::getDBO();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($this->tags[$oneTag])) continue;
				$arguments = explode('|',$allresults[1][$i]);
				$allcats = explode('-',$arguments[0]);
				$parameter = null;
				for($i=1;$i<count($arguments);$i++){
					$args = explode(':',$arguments[$i]);
					$arg0 = $args[0];
					if(isset($args[1])){
						$parameter->$arg0 = $args[1];
					}else{
						$parameter->$arg0 = true;
					}
				}
				$selectedArea = array();
				foreach($allcats as $oneCat){
					if(empty($oneCat)) continue;
					$selectedArea[] = (int) $oneCat;
				}
				$query = 'SELECT DISTINCT b.`product_id` FROM `#__vm_product_category_xref` as a LEFT JOIN `#__vm_product` as b ON a.product_id = b.product_id';
				$where = array();
				if($this->params->get('stock',0) == '1') $where[] = 'b.product_in_stock > 0';
				if(!empty($selectedArea)){
					$where[] = 'a.category_id IN ('.implode(',',$selectedArea).')';
				}
				$where[] = "b.`product_publish` = 'Y'";
				if(!empty($parameter->filter) AND !empty($email->params['lastgenerateddate'])){
					$condition = 'b.`cdate` >\''.$email->params['lastgenerateddate'].'\'';
					if($parameter->filter == 'modify'){
						$condition .= ' OR b.`mdate` >\''.$email->params['lastgenerateddate'].'\'';
					}
					$where[] = $condition;
				}
				$query .= ' WHERE ('.implode(') AND (',$where).')';
				if(!empty($parameter->order)){
					$ordering = explode(',',$parameter->order);
					$query .= ' ORDER BY b.`'.acymailing::secureField($ordering[0]).'` '.acymailing::secureField($ordering[1]);
				}
				if(!empty($parameter->max)) $query .= ' LIMIT '.(int) $parameter->max;
				$db->setQuery($query);
				$allArticles = $db->loadResultArray();
				if(!empty($parameter->min) AND count($allArticles)< $parameter->min){
					$return->status = false;
					$return->message = 'Not enough products for the tag '.$oneTag.' : '.count($allArticles).' / '.$parameter->min;
				}
				$stringTag = '';
				if(!empty($allArticles)){
					if(file_exists(ACYMAILING_MEDIA.'plugins'.DS.'autovmproduct.php')){
						ob_start();
						require(ACYMAILING_MEDIA.'plugins'.DS.'autovmproduct.php');
						$stringTag = ob_get_clean();
					}else{
						$stringTag .= '<table>';
						foreach($allArticles as $oneArticleId){
							$stringTag .= '<tr><td>';
							$args = array();
							$args[] = 'vmproduct:'.$oneArticleId;
							if(!empty($parameter->type)) $args[] = 'type:'.$parameter->type;
							if(!empty($parameter->lang)) $args[] = 'lang:'.$parameter->lang;
							$stringTag .= '{'.implode('|',$args).'}';
							$stringTag .= '</td></tr>';
						}
						$stringTag .= '</table>';
					}
				}
				$this->tags[$oneTag] = $stringTag;
			}
		}
		return $return;
	}
 	function _replaceProducts(&$email){
		$match = '#{vmproduct:(.*)}#Ui';
		$variables = array('body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}
		if(!$found) return;
		$mailerHelper = acymailing::get('helper.mailer');
		$resultshtml = array();
		$resultstext = array();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($resultshtml[$oneTag])) continue;
				$resultshtml[$oneTag] = $this->_replaceProduct($allresults,$i);
				$resultstext[$oneTag] = $mailerHelper->textVersion($resultshtml[$oneTag]);
			}
		}
		$email->body = str_replace(array_keys($resultshtml),$resultshtml,$email->body);
		$email->altbody = str_replace(array_keys($resultstext),$resultstext,$email->altbody);
	 }
	 function _replaceProduct(&$allresults,$i){
		$arguments = explode('|',$allresults[1][$i]);
		$tag = null;
		$tag->id = (int) $arguments[0];
		for($i=1,$a=count($arguments);$i<$a;$i++){
			$args = explode(':',$arguments[$i]);
			if(isset($args[1])){
				$tag->$args[0] = $args[1];
			}else{
				$tag->$args[0] = true;
			}
		}
	 	$time = time();
		$query = 'SELECT d.*, c.*, b.*, a.* FROM '.acymailing::table('vm_product',false).' as a ';
		$query .= 'LEFT JOIN '.acymailing::table('vm_product_price',false).' as b on a.product_id = b.product_id ';
		$query .= 'LEFT JOIN '.acymailing::table('vm_tax_rate',false).' as c on a.product_tax_id = c.tax_rate_id ';
		$query .= 'LEFT JOIN '.acymailing::table('vm_product_discount',false).' as d on a.`product_discount_id` = d.`discount_id` AND d.`start_date` < '.$time.' AND (d.`end_date` = 0 OR d.`end_date` > '.$time.') ';
		$query .= 'WHERE a.product_id = '.$tag->id.' LIMIT 1';
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$product = $db->loadObject();
		if(empty($product)){
			$app =& JFactory::getApplication();
			if($app->isAdmin()){
				$app->enqueueMessage('The product "'.$tag->id.'" could not be loaded','notice');
			}
			return '';
		}
		if(!empty($tag->lang)){
			$langid = (int) substr($tag->lang,strpos($tag->lang,',')+1);
			if(!empty($langid)){
				$query = "SELECT reference_field, value FROM `#__jf_content` WHERE `published` = 1 AND `reference_table` = 'vm_product' AND `language_id` = $langid AND `reference_id` = ".$tag->id;
				$db->setQuery($query);
				$translations = $db->loadObjectList();
				if(!empty($translations)){
					foreach($translations as $oneTranslation){
						if(!empty($oneTranslation->value)){
							$translatedfield =  $oneTranslation->reference_field;
							$product->$translatedfield = $oneTranslation->value;
						}
					}
				}
			}
		}
		switch($product->product_currency) {
			case 'USD': $product->product_currency='$';break;
			case 'EUR': $product->product_currency='€';break;
			case 'GBP': $product->product_currency='£';break;
			case 'JPY': $product->product_currency='¥';break;
			case 'AUD': $product->product_currency='AUD $';break;
			case 'CAD': $product->product_currency='CAD $';break;
			case 'HKD': $product->product_currency='HKD $';break;
			case 'NZD': $product->product_currency='NZD $';break;
			case 'SGD': $product->product_currency='SGD $';break;
		}
		if($this->params->get('vat',1) AND !empty($product->tax_rate)) $product->product_price = $product->product_price * (1 + $product->tax_rate);
		$description = (empty($tag->type) || $tag->type == 'full') ? $product->product_desc : $product->product_s_desc;
		$link = ACYMAILING_LIVE.'index.php?option=com_virtuemart&page=shop.product_details&product_id='.$product->product_id;
		if(!empty($tag->lang)) $link.= '&lang='.substr($tag->lang, 0,strpos($tag->lang,','));
		if(!empty($product->amount)){
			$price2 = empty($product->is_percent) ? $product->product_price - $product->amount : $product->product_price - ($product->amount * $product->product_price / 100);
		}
		if($this->params->get('priceformat','english') == 'french'){
			$price = number_format($product->product_price, 2, ',', ' ').' '.$product->product_currency;
			if(!empty($price2)) $price2 = number_format($price2, 2, ',', ' ').' '.$product->product_currency;
		}else{
			$price = $product->product_currency.number_format($product->product_price, 2, '.', '');
			if(!empty($price2)) $price2 = $product->product_currency.number_format($price2, 2, '.', '');
		}
		$finalPrice = empty($price2) ? $price : '<strike>'.$price.'</strike> '.$price2;
		if(file_exists(ACYMAILING_MEDIA.'plugins'.DS.'tagvmproduct.php')){
			ob_start();
			require(ACYMAILING_MEDIA.'plugins'.DS.'tagvmproduct.php');
			return ob_get_clean();
		}
		$result = '';
		$astyle = '';
		if(empty($tag->type) || $tag->type != 'title'){
			$result .= '<div class="acymailing_content">';
			$astyle = 'style="text-decoration:none;" name="product-'.$product->product_id.'"';
		}
		$result .= '<a '.$astyle.' target="_blank" href="'.$link.'">';
		if(empty($tag->type) || $tag->type != 'title') $result .= '<h2 class="acymailing_title">';
		$result .= $product->product_name.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$finalPrice;
		if(empty($tag->type) || $tag->type != 'title') $result .= '</h2>';
		$result .= '</a>';
		if(empty($tag->type) || $tag->type != 'title'){
			if(!empty($product->product_thumb_image)){
				$img = $product->product_thumb_image;
				if(file_exists(ACYMAILING_ROOT.'components'.DS.'com_virtuemart'.DS.'shop_image'.DS.'product'.DS.'resized'.DS.substr($img,0,strrpos($img,'.')).'_90x90'.substr($img,strrpos($img,'.')))){
					$img = 'resized/'.substr($img,0,strrpos($img,'.')).'_90x90'.substr($img,strrpos($img,'.'));
				}
				$result .= '<a target="_blank" style="text-decoration:none;border:0px" href="'.$link.'" ><img style="float:left;margin:5px;border:0px" src="'.ACYMAILING_LIVE.'components/com_virtuemart/shop_image/product/'.$img.'"/></a>'.$description;
			}else{
				$result .= $description;
			}
		}
		if(empty($tag->type) || $tag->type != 'title') $result .= '</div>';
		return $result;
	}
	function onAcyDisplayFilters(&$type){
		$db =& JFactory::getDBO();
		$db->setQuery("SELECT `product_id` as value, CONCAT(`product_sku`,' ( ',`product_name`,' ) ') as text FROM #__vm_product ORDER BY `product_sku` ASC");
		$allProducts = $db->loadObjectList();
		if(!empty($allProducts)){
			$selectOne = null;
			$selectOne->value = 0;
			$selectOne->text = JText::_('ACY_ONE_PRODUCT');
			array_unshift($allProducts,$selectOne);
		}
		$vmbuy = array();
		$vmbuy[] = JHTML::_('select.option', '0', JText::_('ACY_DIDNOTBOUGHT') );
		$vmbuy[] = JHTML::_('select.option', '1', JText::_('ACY_BOUGHT') );
		$vmgroupsparams = acymailing::get('type.operatorsin');
		$operators = acymailing::get('type.operators');
		$db->setQuery('SELECT `shopper_group_id` as value, `shopper_group_name` as text FROM `#__vm_shopper_group` ORDER BY `shopper_group_name` ASC');
		$vmgroups = $db->loadObjectList();
		$fields = reset($db->getTableFields('#__vm_user_info'));
		$vmfield = array();
		foreach($fields as $oneField => $fieldType){
			$vmfield[] = JHTML::_('select.option',$oneField,$oneField);
		}
		$db->setQuery('SELECT order_status_code as code, order_status_name as name FROM `#__vm_order_status` ORDER BY `list_order` ASC');
		$allStatus = $db->loadObjectList();
		if(!empty($allStatus)){
			$firstStatus = null;
			$firstStatus->name = JText::_('ALL_STATUS');
			$firstStatus->code = '';
			array_unshift($allStatus,$firstStatus);
		}
		$db->setQuery("SELECT `payment_method_name` as name, `payment_method_id` as id FROM `#__vm_payment_method` WHERE `payment_enabled` = 'Y' ORDER BY `list_order` ASC");
		$allPayments = $db->loadObjectList();
		if(!empty($allPayments)){
			$firstPayment = null;
			$firstPayment->name = JText::_('ACY_ALL');
			$firstPayment->id = '';
			array_unshift($allPayments,$firstPayment);
		}
		$return = '';
		if(!empty($allStatus)){
			$return .= '<div id="filter__num__vmallorders">'.$vmgroupsparams->display("filter[__num__][vmallorders][type]").' ';
			$return .= JHTML::_('select.genericlist',   $allStatus, "filter[__num__][vmallorders][status]", 'class="inputbox" size="1"', 'code', 'name');
			$return .= JHTML::_('select.genericlist',   $allPayments, "filter[__num__][vmallorders][payment]", 'class="inputbox" size="1"', 'id', 'name');
			$return .= '<br/> <input name="filter[__num__][vmallorders][cdateinf]" /> < '.JText::_('CREATED_DATE').' < <input name="filter[__num__][vmallorders][cdatesup]" />';
			$return .= '<br/> <input name="filter[__num__][vmallorders][mdateinf]" /> < '.JText::_('MODIFIED_DATE').' < <input name="filter[__num__][vmallorders][mdatesup]" />';
			$return .= '</div>';
			$type['vmallorders'] = 'VirtueMart : '.JText::_('ACY_ORDERS');
		}
		if(!empty($allProducts)){
			$return .= '<div id="filter__num__vmorder">'.JHTML::_('select.genericlist', $vmbuy, "filter[__num__][vmorder][type]", 'class="inputbox" size="1"', 'value', 'text').' ';
			$return .= JHTML::_('select.genericlist',   $allProducts, "filter[__num__][vmorder][product]", 'class="inputbox" size="1"', 'value', 'text');
			$return .= '<br/> <input name="filter[__num__][vmorder][creationdateinf]" /> < '.JText::_('CREATED_DATE').' < <input name="filter[__num__][vmorder][creationdatesup]" />';
			$return .= '</div>';
			$type['vmorder'] = 'VirtueMart : '.JText::_('ACY_PRODUCTS');
		}
		if(!empty($vmgroups)){
			$return .= '<div id="filter__num__vmgroups">'.$vmgroupsparams->display("filter[__num__][vmgroups][type]").' ';
			$return .= JHTML::_('select.genericlist', $vmgroups, "filter[__num__][vmgroups][group]", 'class="inputbox" size="1"', 'value', 'text');
			$return .= '</div>';
			$type['vmgroups'] = 'VirtueMart : '.JText::_('SHOPPER_GROUP');
		}
		if(!empty($vmfield)){
			$return .= '<div id="filter__num__vmfield">'.JHTML::_('select.genericlist',   $vmfield, "filter[__num__][vmfield][map]", 'class="inputbox" size="1"', 'value', 'text');
			$return .= ' '.$operators->display("filter[__num__][vmfield][operator]").' <input class="inputbox" type="text" name="filter[__num__][vmfield][value]" size="50" value="">';
			$return .= '</div>';
			$type['vmfield'] = 'VirtueMart : '.JText::_('FIELD');
		}
		return $return;
	}
	function onAcyProcessFilter_vmfield(&$query,$filter,$num){
		$myquery = "SELECT DISTINCT a.user_email FROM #__vm_user_info as a WHERE ".$query->convertQuery('a',$filter['map'],$filter['operator'],$filter['value']);
		$query->db->setQuery($myquery);
		$allEmails  = $query->db->loadResultArray();
		if(empty($allEmails)) $allEmails[] = 'none';
		$query->where[] = "sub.email IN ('".implode("','",$allEmails)."')";
	}
	function onAcyProcessFilter_vmallorders(&$query,$filter,$num){
		$db =& JFactory::getDBO();
	 	$lj = "`#__vm_orders` as vmallorders$num ON vmallorders$num.`user_id` = sub.`userid`";
	 	if(!empty($filter['status'])) $lj .= " AND vmallorders$num.`order_status` = ".$db->Quote($filter['status']);
	 	if(!empty($filter['cdateinf'])){
	 		$filter['cdateinf'] = acymailing::replaceDate($filter['cdateinf']);
	 		if(!is_numeric($filter['cdateinf'])) $filter['cdateinf'] = strtotime($filter['cdateinf']);
	 		$lj .= " AND vmallorders$num.`cdate` > ".$db->Quote($filter['cdateinf']);
	 	}
	 	if(!empty($filter['cdatesup'])){
	 		$filter['cdatesup'] = acymailing::replaceDate($filter['cdatesup']);
	 		if(!is_numeric($filter['cdatesup'])) $filter['cdatesup'] = strtotime($filter['cdatesup']);
	 		$lj .= " AND vmallorders$num.`cdate` < ".$db->Quote($filter['cdatesup']);
	 	}
	 	if(!empty($filter['mdateinf'])){
	 		$filter['mdateinf'] = acymailing::replaceDate($filter['mdateinf']);
	 		if(!is_numeric($filter['mdateinf'])) $filter['mdateinf'] = strtotime($filter['mdateinf']);
	 		$lj .= " AND vmallorders$num.`mdate` > ".$db->Quote($filter['mdateinf']);
	 	}
	 	if(!empty($filter['mdatesup'])){
	 		$filter['mdatesup'] = acymailing::replaceDate($filter['mdatesup']);
	 		if(!is_numeric($filter['mdatesup'])) $filter['mdatesup'] = strtotime($filter['mdatesup']);
	 		$lj .= " AND vmallorders$num.`mdate` < ".$db->Quote($filter['mdatesup']);
	 	}
	 	$query->leftjoin['vmallorders_'.$num] = $lj;
	 	if(!empty($filter['payment'])){
	 		$plj = "`#__vm_order_payment` as vmpay$num ON vmallorders$num.`order_id` = vmpay$num.`order_id` AND `payment_method_id` = ".intval($filter['payment']);
	 		$query->leftjoin['vmpay_'.$num] = $plj;
	 	}
	 	$query->where['member'] = 'sub.userid > 0';
		$operator = ($filter['type'] == 'IN') ? 'IS NOT NULL' : 'IS NULL';
		if(!empty($filter['payment'])) $query->where[] = "vmpay$num.`order_id` ".$operator;
		else $query->where[] = "vmallorders$num.`user_id` ".$operator;
	}
	function onAcyProcessFilter_vmgroups(&$query,$filter,$num){
		$myquery = 'SELECT DISTINCT b.`user_email` FROM `#__vm_shopper_vendor_xref` as a LEFT JOIN `#__vm_user_info` as b on a.`user_id` = b.`user_id` WHERE a.`shopper_group_id` ';
		$myquery .= ($filter['type'] == 'IN') ? '= ' : "!= ";
		$myquery .= (int) $filter['group'];
		$query->db->setQuery($myquery);
		$allEmails  = $query->db->loadResultArray();
		if(empty($allEmails)) $allEmails[] = 'none';
		$query->where[] = "sub.email IN ('".implode("','",$allEmails)."')";
	}
	function onAcyProcessFilter_vmorder(&$query,$filter,$num){
		$myquery = "SELECT DISTINCT b.user_email FROM #__vm_order_item as a LEFT JOIN #__vm_user_info as b on a.user_info_id = b.user_info_id WHERE a.order_status IN ('C','S') AND b.user_id < 1";
		$myqueryid = "SELECT DISTINCT b.user_id FROM #__vm_order_item as a LEFT JOIN #__vm_user_info as b on a.user_info_id = b.user_info_id WHERE a.order_status IN ('C','S') AND b.user_id > 0";
		if(!empty($filter['product']) AND is_numeric($filter['product'])){
			$myquery .= " AND a.product_id = ".(int) $filter['product'];
			$myqueryid .= " AND a.product_id = ".(int) $filter['product'];
		}
		$datesVar = array('creationdatesup','creationdateinf');
		foreach($datesVar as $oneDate){
			if(empty($filter[$oneDate])) continue;
			$filter[$oneDate] = acymailing::replaceDate($filter[$oneDate]);
			if(!is_numeric($filter[$oneDate])) $filter[$oneDate] = strtotime($filter[$oneDate]);
		}
		if(!empty($filter['creationdateinf'])) $myquery .= ' AND a.cdate > '.$filter['creationdateinf'];
		if(!empty($filter['creationdatesup'])) $myquery .= ' AND a.cdate < '.$filter['creationdatesup'];
		if(!empty($filter['creationdateinf'])) $myqueryid .= ' AND a.cdate > '.$filter['creationdateinf'];
		if(!empty($filter['creationdatesup'])) $myqueryid .= ' AND a.cdate < '.$filter['creationdatesup'];
		$query->db->setQuery($myquery);
		$allEmails  = $query->db->loadResultArray();
		$query->db->setQuery($myqueryid);
		$allIds  = $query->db->loadResultArray();
		if(empty($allEmails)) $allEmails[] = 'none';
		if(empty($allIds)) $allIds[] = 'none';
		if(empty($filter['type'])){
			$query->where[] = "sub.email NOT IN ('".implode("','",$allEmails)."') AND sub.userid NOT IN ('".implode("','",$allIds)."')";
		}else{
			$query->where[] = "sub.email IN ('".implode("','",$allEmails)."') OR sub.userid IN ('".implode("','",$allIds)."')";
		}
	}
}//endclass