<?
require_once ('init.php');

header("Content-Type: text/html; charset=utf-8");

$task = JRequest::getVar('task');


switch($task){

	case 'searchuser':
		$search = JRequest::getVar('search');//введенная строка для поиска
		$search = addslashes($search);
		$query = "SELECT `id`,`name` FROM #__users where (name like '%{$search}%' or username like '%{$search}%') and name not like 'Protect user%'";
		$db->setQuery( $query );
		$users = $db->loadRowList();
		$f = true;
		if (is_array($users)){
			foreach($users as $u){
				if (!$f) echo ";";
				echo $u[0].":".$u[1];
				$f = false;
			}
		}
	break;

	case 'searchfriends':
		$search = JRequest::getVar('search');
		
		$query = "SELECT u.id, u.name
				FROM #__users as u, #__idoblog_connections as icon, #__idoblog_communities as icom 
				WHERE u.username=icom.community_keyword AND icon.id='".$user->id."' AND icon.idfriend=icom.id_community AND icon.both='1' AND icon.type='3'";
		$db->setQuery( $query );
		$users = $db->loadRowList();
		$f = true;
		if (is_array($users)) {
			foreach($users as $u){
				if (!$f) echo ";";
				echo $u[0].":".$u[1];
				$f = false;
			}
		}
	break;
	
	case 'showinfo':
		$useridtoshow = JRequest::getVar('useridtoshow');
		
		require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_idoblog'.DS.'config.php' );
		GLOBAL $config,$live_site;
		$config = new KBconfig();
		$q = JURI::base(true);
		$live_site = "/";
		//if ( empty( $live_site ) ) $live_site="/";		
		
		
		require_once( JPATH_COMPONENT.DS.'com_idoblog'.DS.'idobloghelper.php' );
		$ido = new idobloghelper();
		
		$sql = "select * from #__idoblog_users as t1 inner join #__users as t2 on t1.iduser=t2.id where t1.iduser='{$useridtoshow}'";
		$db->setQuery($sql);
		$u = $db->loadObject();
		$avatar = addslashes($ido->getavatar($u,2));
		
		$link = $ido->getVar("profile",$u);
		$link = substr($link,strlen($q));
		// /images/idoblog/{$u->avatar}
		echo "{
			avatar : '$avatar',
			karma : '{$u->karma}',
			rating : '{$u->place}',
			mood : '".$ido->replace_bbcode($ido->replace_smile($u->mood))."',
			link : '".$link."'
		}";
	break;
	
}



?>