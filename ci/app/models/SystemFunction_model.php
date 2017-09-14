<?php

/**
 * Created by PhpStorm.
 * User: guangpeng
 * Date: 8/15-015
 * Time: 21:05
 */
class SystemFunction_model  extends CI_Model
{
    /**
     * @var $db_sdk CI_DB_driver
     */
    private $db_sdk;
    private $appid;
    public function __construct()
    {
        parent::__construct();
        $this->db_sdk = $this->load->database('sdk', TRUE);
    }
    public function setAppid($appid)
    {
        $this->appid = $appid;
    }
    
    /**
     * 社团入侵
     * @param unknown $where
     * @return boolean
     * @author 王涛 20170515
     */
    public function ActionByInvasion($where = array() , $field = '*' ,$group = '')
    {
    	$date = date("Ymd",$where['begintime']);
    	$utable   = "u_behavior_$date";
    	$sql = "select $field from (select accountid,count(*) cid,floor(user_level/10) as level from $utable where 1=1 ";
    
    	if($where['params']){
    		$sql .= " AND param IN(".implode(',', $where['params']).")";
    	}
    	if($where['viplev_min']){
    		$sql .= " AND vip_level >=".$where['viplev_min'];
    	}
    	if($where['viplev_max']){
    		$sql .= " AND vip_level <=".$where['viplev_max'];
    	}
    	if($where['serverids']){
    		$sql .= " AND serverid IN(".implode(',', $where['serverids']).")";
    	}
    	if($where['channels']){
    		$sql .= " AND channel IN(".implode(',', $where['channels']).")";
    	}
    	if($where['typeids']){
    		$sql .= " AND act_id IN(".implode(',', $where['typeids']).")";
    	}
    	$sql .= " group by accountid) a ";
    	if($where['cid']){
    		$sql .= " where cid={$where['cid']}";
    	}
    	if($group){
    		$sql .= " group by $group";
    	}
    	
    	$query = $this->db_sdk->query($sql);
    	if ($query) return $query->result_array();
    	return array();
    }
    /**
     * 植树节获得奖励数统计
     * @param unknown $where
     * @return boolean
     * @author 王涛 20170316
     */
    public function ActionByTree($where = array() , $field = '*' )
    {
    	$date = date("Ymd",$where['begintime']);
    	$utable   = "u_behavior_$date";
    	$sql = "select $field from (select act_id,count(id) cid,user_level from $utable where 1=1 ";

    	if($where['params']){
    		$sql .= " AND param IN(".implode(',', $where['params']).")";
    	}
    	if($where['viplev_min']){
    		$sql .= " AND vip_level >=".$where['viplev_min'];
    	}
    	if($where['viplev_max']){
    		$sql .= " AND vip_level <=".$where['viplev_max'];
    	}
    	if($where['serverids']){
    		$sql .= " AND serverid IN(".implode(',', $where['serverids']).")";
    	}
    	if($where['channels']){
    		$sql .= " AND channel IN(".implode(',', $where['channels']).")";
    	}
    	if($where['typeids']){
    		$sql .= " AND act_id IN(".implode(',', $where['typeids']).")";
    	}
    	$sql .= " group by accountid) a group by level";
    	$query = $this->db_sdk->query($sql);
    	if ($query) return $query->result_array();
    	return array();
    }
    public function PlayerDevelop($serverid, $channel, $viplev_min, $viplev_max=0)
    {
        $sql = "SELECT count(*) as cnt,viplev FROM u_login_new WHERE appid={$this->appid}";
        if (is_numeric($serverid) && $serverid>0) $sql .= " AND serverid=$serverid";
        elseif (is_array($serverid) && count($serverid)>0) $sql .= " AND serverid IN(".implode(',', $serverid).")";
        if (is_numeric($channel) && $channel>0) $sql .= " AND channel=$channel";
        elseif (is_array($channel) && count($channel)>0) $sql .= " AND channel IN(".implode(',', $channel).")";
        if ($viplev_min>0)         $sql .= " AND viplev>=$viplev_min";
        if ($viplev_max>0)        $sql .= " AND viplev<=$viplev_max";
        $sql .= " GROUP BY viplev ORDER BY viplev ASC";
        $query = $this->db_sdk->query($sql);
        if ($query) return $query->result_array();
        return false;
    }

    public function PlayerDevelopDetail()
    {

    }

    public function money_use($timestamp1, $timestamp2, $serverid, $channel)
    {
        $table   = "type_005_" . $this->appid;
        $sql = "SELECT SUM(currency_num) as money,get_or_use,currency_type FROM $table";
        $sql .= " WHERE appid={$this->appid} AND created_at between $timestamp1 AND $timestamp2 ";
        if (is_numeric($serverid) && $serverid>0) $sql .= " AND serverid=$serverid";
        elseif (is_array($serverid) && count($serverid)>0) $sql .= " AND serverid IN(".implode(',', $serverid).")";
        if (is_numeric($channel) && $channel>0) $sql .= " AND channel=$channel";
        elseif (is_array($channel) && count($channel)>0) $sql .= " AND channel IN(".implode(',', $channel).")";
        $sql .= " GROUP BY currency_type,get_or_use ORDER BY currency_type ASC";
        $query = $this->db_sdk->query($sql);
        //echo $sql;
        if ($query) return $query->result_array();
        return false;
    }

    public function props_shop($timestamp1, $timestamp2, $serverid, $channel)
    {
        $table   = "type_007_" . $this->appid;
        $sql = "SELECT buy_item_id,buy_item_name,COUNT(*) AS cnt,SUM(buy_item_num) as num,SUM(currency_num) AS money FROM $table";
        $sql .= " WHERE appid={$this->appid} AND created_at between $timestamp1 AND $timestamp2 ";
        if (is_numeric($serverid) && $serverid>0) $sql .= " AND serverid=$serverid";
        elseif (is_array($serverid) && count($serverid)>0) $sql .= " AND serverid IN(".implode(',', $serverid).")";
        if (is_numeric($channel) && $channel>0) $sql .= " AND channel=$channel";
        elseif (is_array($channel) && count($channel)>0) $sql .= " AND channel IN(".implode(',', $channel).")";
        $sql .= " GROUP BY buy_item_id";
        $query = $this->db_sdk->query($sql);
        //echo $sql;
        if ($query) return $query->result_array();
        return false;
    }
    public function BehaviorProduceSale($timestamp1, $timestamp2, $serverid, $channel, $account_id, $userid)
    {
        $table   = "type_018_" . $this->appid;
        $sql = "SELECT * FROM $table";
        $sql .= " WHERE appid={$this->appid} AND created_at between $timestamp1 AND $timestamp2 ";
        if (is_numeric($serverid) && $serverid>0) $sql .= " AND serverid=$serverid";
        elseif (is_array($serverid) && count($serverid)>0) $sql .= " AND serverid IN(".implode(',', $serverid).")";
        if (is_numeric($channel) && $channel>0) $sql .= " AND channel=$channel";
        elseif (is_array($channel) && count($channel)>0) $sql .= " AND channel IN(".implode(',', $channel).")";
        if ($account_id) $sql .= " AND accountid=$account_id";
        if ($userid) $sql .= " AND userid=$userid";
        $sql .= " ORDER BY created_at DESC";
        $query = $this->db_sdk->query($sql);
        //echo $sql;
        if ($query) return $query->result_array();
        return false;
    }
    /**
     * 行为产销统计
     * @param unknown $where
     * @return boolean
     * @author 王涛 20161230
     */
    public function ActionProduceSaleByBehavior($where = array() , $field = '*' ,$group = '',$order ='',$limit='')
    { 
    	$date = date("Ymd",$where['begintime']);
    	$utable   = "u_behavior_$date";
    	$sql = "select $field FROM $utable where 1=1";
    	 
    	if ($where ['beginserver'] && $where ['endserver']) {
    		$server_list = $this->db_sdk->query ( "select serverid from server_date where serverdate>={$where['beginserver']} and  serverdate<={$where['endserver']}" );
    			
    		if ($server_list) {
    			foreach ( $server_list->result_array () as $k => $v ) {
    					
    				$server_list_new .= $v ['serverid'] . ',';
    			}
    			$server_list_new = rtrim ( $server_list_new, ',' );
    		}
    	}
    	
    	if ($server_list) {
    		$sql .= " AND serverid IN($server_list_new)";
    	}
    	
    	if($where['params']){
    		$sql .= " AND param IN(".implode(',', $where['params']).")";
    	}
    	if($where['userid']){
    		$sql .= " AND userid =".$where['userid'];
    	}
    	if($where['viplev_min']){
    		$sql .= " AND vip_level >=".$where['viplev_min'];
    	}
    	if($where['viplev_max']){
    		$sql .= " AND vip_level <=".$where['viplev_max'];
    	}
    	if($where['serverids']){
    		$sql .= " AND serverid IN(".implode(',', $where['serverids']).")";
    	}
    	if($where['channels']){
    		$sql .= " AND channel IN(".implode(',', $where['channels']).")";
    	}
    	if($where['typeids']){
    		$sql .= " AND act_id IN(".implode(',', $where['typeids']).")";
    	}
    	if($where['beginh']){
    		$sql .= " AND from_unixtime(client_time,'%H%i') between {$where['beginh']} and {$where['endh']}";
    	}
    	if($group){
    		$sql .= " group by $group";
    	}
    	if($where['cid']){
    		$sql .= " having cid IN({$where['cid']})";
    	}
    	if($limit){
    		$sql .= " limit $limit";
    	} 
    	$query = $this->db_sdk->query($sql);    	
    	if ($query) return $query->result_array();
    	return array();
    }
    
    
    
    /**
     * 行为产销统计(多天)
     * @param unknown $where
     * @return boolean
     * @author zzl 20170724 
     */
    public function ActionProduceSaleByBehaviorMore($where = array() , $field = '*' ,$group = '',$order ='',$limit='')  
    {  
    	$date = date("Ymd",$where['begintime']);
    	$utable   = "u_behavior_{$where['logdate']}";
    	$sql = "select $field FROM $utable where 1=1";
    
    	if($where['params']){
    		$sql .= " AND param IN(".implode(',', $where['params']).")";
    	}
    	if($where['userid']){
    		$sql .= " AND userid =".$where['userid'];
    	}
    	if($where['viplev_min']){
    		$sql .= " AND vip_level >=".$where['viplev_min'];
    	}
    	if($where['viplev_max']){
    		$sql .= " AND vip_level <=".$where['viplev_max'];
    	}
    	if($where['serverids']){
    		$sql .= " AND serverid IN(".implode(',', $where['serverids']).")";
    	}
    	if($where['channels']){
    		$sql .= " AND channel IN(".implode(',', $where['channels']).")";
    	}
    	if($where['beginh']){
    		$sql .= " AND from_unixtime(client_time,'%H%i') between {$where['beginh']} and {$where['endh']}";
    	}
    	if($group){
    		$sql .= " group by $group";
    	}
    	if($where['cid']){
    		$sql .= " having cid IN({$where['cid']})";
    	}
    	if($limit){
    		$sql .= " limit $limit";
    	}
   
    	$query = $this->db_sdk->query($sql);
    	if ($query) return $query->result_array();
    	return array();
    }
    
    /**
     * 行为产销统计
     * @param unknown $where
     * @return boolean
     * @author 王涛 20161230
     */
    public function ActionProduceSaleNew($where = array() , $field = '*' ,$group = '')
    {
    	$date = date("Ymd",$where['begintime']);
    	$itable   = "item_trading_$date";
    	$utable   = "u_behavior_$date";
    	$u_register   = "u_register";
    	//$sql = "SELECT $field FROM $itable i inner join $utable u on i.behavior_id=u.id inner join (select act_id,count(DISTINCT accountid) as caccountid FROM $utable  group by act_id)as n on u.act_id=n.act_id";
    //	$sql = "SELECT $field FROM $itable i inner join $utable u on i.behavior_id=u.id where 1=1 ";
    	$sql = "SELECT $field FROM $itable i inner join $utable u on i.behavior_id=u.id inner join $u_register r on u.accountid=r.accountid  where 1=1 ";
    
    	if ($where ['beginserver'] && $where ['endserver']) {
    		$server_list = $this->db_sdk->query ( "select serverid from server_date where serverdate>={$where['beginserver']} and  serverdate<={$where['endserver']}" );
    		 
    		if ($server_list) {
    			foreach ( $server_list->result_array () as $k => $v ) {
    					
    				$server_list_new .= $v ['serverid'] . ',';
    			}
    			$server_list_new = rtrim ( $server_list_new, ',' );
    		}
    	}
    	 
    	if ($server_list) {
    		$sql .= " AND u.serverid IN($server_list_new)";
    	}  
    	if($where['params']){
    		$sql .= " AND param IN(".implode(',', $where['params']).")";
    	}
    	if($where['userid']){
    		$sql .= " AND userid =".$where['userid'];
    	}
    	if($where['serverids']){
    		$sql .= " AND u.serverid IN(".implode(',', $where['serverids']).")";
    	}
    	if($where['channels']){
    		$sql .= " AND u.channel IN(".implode(',', $where['channels']).")";
    	}
    	if($where['typeids']){
    		$sql .= " AND u.act_id IN(".implode(',', $where['typeids']).")";
    	}
    	if(isset($where['type'])){
    		$sql .= " AND i.type = {$where['type']}";
    	}
    	if(isset($where['register_start'])){
    	    $sql .= " AND r.reg_data = {$where['register_start']}";
    	}
    	
    	if(isset($where['item_id'])){
    		$sql .= " AND item_id = {$where['item_id']}";
    	}
    	if(isset($where['accountid'])){
    		if($where['accountid']==0){
    			$sql .= " AND accountid!=0";
    		}
    	}
    	 
    	if($group){
    		$sql .= " group by $group";
    	} 
    	echo $sql;
    	//var_dump($sql);
    	$query = $this->db_sdk->query($sql);
    	if ($query) return $query->result_array();
    	return false;
    }
    /**
     * 道具产销统计
     * @param unknown $where
     * @return boolean
     * @author 王涛 20161230
     */
    public function BehaviorProduceSaleNew($where = array() , $field = '*' ,$group = '' ,$order = '')
    {
    	$date = date("Ymd",$where['begintime']);
    	$itable   = "item_trading_$date";
    	$utable   = "u_behavior_$date";
    	$sql = "SELECT $field FROM $itable i inner join $utable u on i.behavior_id=u.id where item_id not between 1000000000 and 1999999999";

    	if ($where ['beginserver'] && $where ['endserver']) {
    		$server_list = $this->db_sdk->query ( "select serverid from server_date where serverdate>={$where['beginserver']} and  serverdate<={$where['endserver']}" );
    		 
    		if ($server_list) {
    			foreach ( $server_list->result_array () as $k => $v ) {
    					
    				$server_list_new .= $v ['serverid'] . ',';
    			}
    			$server_list_new = rtrim ( $server_list_new, ',' );
    		}
    	}
    	 
    	if ($server_list_new) {
    		$sql .= " AND u.serverid IN($server_list_new)";
    	}
    	
    	if($where['params']){
    		$sql .= " AND param IN(".implode(',', $where['params']).")";
    	}
    	if($where['viplev_min']){
    		$sql .= " AND vip_level >=".$where['viplev_min'];
    	}
    	if($where['viplev_max']){
    		$sql .= " AND vip_level <=".$where['viplev_max'];
    	}
    	if($where['itemid']){
    		$sql .= "  AND item_id in ({$where['itemid']})";
    	}
    	if($where['userid']){
    		$sql .= " AND userid =".$where['userid'];
    	}

    	if($where['serverids']){
    		$sql .= " AND u.serverid IN(".implode(',', $where['serverids']).")";
    	}
    	if($where['channels']){
    		$sql .= " AND u.channel IN(".implode(',', $where['channels']).")";
    	}
    	if($where['typeids']){
    		$sql .= " AND act_id IN(".implode(',', $where['typeids']).")";
    	}
    	if(isset($where['type'])){
    		$sql .= " AND i.type = {$where['type']}";
    	}
    	if(isset($where['accountid'])){
    		if($where['accountid']==0){
    			$sql .= " AND accountid!=0";
    		}
    	}
    	
    	if($group){
    		$sql .= " group by $group";
    	}
    	if($order){
    		$sql .= " order by $order";
    	}  
    	$query = $this->db_sdk->query($sql);
    	if ($query) return $query->result_array();
    	return false;
    }
    
    /**
     * 行为产销统计记录总表
     * @param unknown $where
     * @return boolean
     * @author 王涛 20170203
     */
    public function ActionCount($group='' , $date='')
    {
    	$types = array(
    			'act_id'=>0,
    			'serverid'=>1,
    			'channel'=>2
    	);
    	if(!$group){
    		$group='act_id';
    	}
    	if(!$date){ //前7天的数据
    		$date = date("Ymd",strtotime("-1 days"));
    	}
    	$mysql = "insert into mydb.sum_act_by_type(logdate,type,typeid,consume_money,consume_diamond,consume_tired,get_money,get_diamond,get_tired)  ";
    	$field = "$date as logdate,{$types[$group]} as type,$group as typeid,";
    	$field .= "sum(if(item_id=1&&type=1,item_num,0)) as consume_money,sum(if(item_id=3&&type=1,item_num,0)) as consume_diamond,sum(if(item_id=2&&type=1,item_num,0)) as consume_tired,";
    	$field .= "sum(if(item_id=1&&type=0,item_num,0)) as get_money,sum(if(item_id=3&&type=0,item_num,0)) as get_diamond,sum(if(item_id=2&&type=0,item_num,0)) as get_tired";
    	$itable   = "sdk.item_trading_$date";
    	$utable   = "sdk.u_behavior_$date";
    	$sql = $mysql . "SELECT $field FROM $itable i inner join $utable u on i.behavior_id=u.id where item_id not between 1000000000 and 1999999999";
    	//$sql .= " and  u.created_at between " . strtotime($date) . " AND " . (strtotime($date)+86399);
    	$sql .= " group by typeid ON DUPLICATE KEY UPDATE `consume_money`=VALUES(consume_money),`consume_diamond`=VALUES(consume_diamond),`consume_tired`=VALUES(consume_tired),
    			`get_money`=VALUES(get_money),`get_diamond`=VALUES(get_diamond),
`get_tired`=VALUES(get_tired)";
    	$db_sdk = $this->load->database('rootsdk', TRUE);
    	$query = $db_sdk->query($sql);
    	print_r($db_sdk->error());
    	$mysql = "insert into mydb.sum_act_by_type(logdate,type,typeid,account_num)  ";
    	$field = "$date as logdate,{$types[$group]} as type,$group as typeid,count(distinct(accountid)) as account_num";
    	$utable   = "sdk.u_behavior_$date";
    	$sql = $mysql . "SELECT $field FROM $utable ";
    	$sql .= " group by typeid ON DUPLICATE KEY UPDATE `account_num`=VALUES(account_num)";
    	$query = $db_sdk->query($sql);
    	print_r($db_sdk->error());
    	return true;
    }
    
    
    
    /**
     * 运营道具增加区服详细
     * @param unknown $where
     * @return boolean
     * @author zzl 20170704
     */
    public function areaDistribution($where = array() , $field = '*' ,$group = '' ,$order = '')
    {
    	$date = date("Ymd",$where['begintime']);
    	$itable   = "item_trading_$date";
    	$utable   = "u_behavior_$date";
    	$sql = "SELECT $field FROM $itable i inner join $utable u on i.behavior_id=u.id where item_id not between 1000000000 and 1999999999";
    
    	if($where['params']){
    		$sql .= " AND param IN(".implode(',', $where['params']).")";
    	}
    	if($where['viplev_min']){
    		$sql .= " AND vip_level >=".$where['viplev_min'];
    	}
    	if($where['viplev_max']){
    		$sql .= " AND vip_level <=".$where['viplev_max'];
    	}
    	if($where['itemid']){
    		$sql .= "  AND item_id in ({$where['itemid']})";
    	}
    	if($where['userid']){
    		$sql .= " AND userid =".$where['userid'];
    	}
    
    	if($where['serverids']){
    		$sql .= " AND u.serverid IN(".implode(',', $where['serverids']).")";
    	}
    	if($where['channels']){
    		$sql .= " AND u.channel IN(".implode(',', $where['channels']).")";
    	}
  /*   	if($where['typeids']){
    		$sql .= " AND act_id IN(".implode(',', $where['typeids']).")";
    	} */
    	if(isset($where['type'])){
    		$sql .= " AND i.type = {$where['type']}";
    	}
    	if(isset($where['accountid'])){
    		if($where['accountid']==0){
    			$sql .= " AND accountid!=0";
    		}
    	}
    	 
    	if($group){
    		$sql .= " group by $group";
    	}
    	if($order){
    		$sql .= " order by $order";
    	}
    	$query = $this->db_sdk->query($sql);    	
    
    	if ($query) return $query->result_array();
    	return false;
    }
    
    
    
    
    /**
     * 运营道具增加活动档次详细
     * @param unknown $where
     * @return boolean
     * @author zzl 20170704
     */
    public function levelDistribution($where = array() , $field = '*' ,$group = '' ,$order = '')
    {
    	$date = date("Ymd",$where['begintime']);
    	$itable   = "item_trading_$date";
    	$utable   = "u_behavior_$date";
    	$sql = "SELECT $field FROM $itable i inner join $utable u on i.behavior_id=u.id where item_id not between 1000000000 and 1999999999";
    
    	if($where['params']){
    		$sql .= " AND param IN(".implode(',', $where['params']).")";
    	}
    	if($where['viplev_min']){
    		$sql .= " AND vip_level >=".$where['viplev_min'];
    	}
    	if($where['viplev_max']){
    		$sql .= " AND vip_level <=".$where['viplev_max'];
    	}
    	if($where['itemid']){
    		$sql .= "  AND item_id in ({$where['itemid']})";
    	}
    	if($where['userid']){
    		$sql .= " AND userid =".$where['userid'];
    	}
    
    	if($where['serverids']){
    		$sql .= " AND u.serverid IN(".implode(',', $where['serverids']).")";
    	}
    	if($where['channels']){
    		$sql .= " AND u.channel IN(".implode(',', $where['channels']).")";
    	}
    	if($where['typeids']){
    		$sql .= " AND act_id IN(".implode(',', $where['typeids']).")";
    	}
    	if(isset($where['type'])){
    		$sql .= " AND i.type = {$where['type']}";
    	}
    	if(isset($where['accountid'])){
    		if($where['accountid']==0){
    			$sql .= " AND accountid!=0";
    		}
    	}
    
    	if($group){
    		$sql .= " group by $group";
    	}
    	if($order){
    		$sql .= " order by $order";
    	}
    	$query = $this->db_sdk->query($sql);  	 
  
    	if ($query) return $query->result_array();
    	return false;
    }
    
    /**
     * 参与度统计
     *
     * @author 王涛 20170330
     */
    public function joinCount($date='')
    {
    //	ini_set('memory_limit', '2048M');
    	/*$sorttype = [1=>1200,6=>3400,7=>400,8=>600,9=>300,10=>1000,11=>1100,12=>2700,13=>5200,14=>5300,15=>5500,16=>5900,18=>5000,19=>3300,20=>1500,
21=>700,22=>800,23=>1400,24=>3200,25=>5400,26=>5600,27=>2400,28=>2500,29=>4100,30=>500,32=>6000,35=>4500,37=>6100,41=>6200,42=>1600,43=>1700,44=>1800,
45=>2100,46=>200,47=>1900,48=>900,49=>2000,50=>2200,52=>2800,53=>2300,54=>5700,55=>3000,56=>100,57=>2600,58=>5800,59=>4200,60=>4300,61=>4400,62=>1300,
63=>3100,66=>4600,67=>4700,68=>4800,69=>4900,71=>5100,72=>2900];*/
    	if(!$date){ //前天的数据
    		$date = date("Ymd",strtotime("-1 days"));
    	}
    	$ym = date('Ym',strtotime($date));
    	$db_sdk = $this->load->database('rootsdk', TRUE);
    	//$mysql = "insert into sum_join_$ym(logdate,act_id,serverid,param,act_count,act_account,vip_level,mysort) values ";
    	$mysql = "insert into sum_join_$ym(logdate,act_id,serverid,param,act_count,act_account,vip_level)  ";
    	$field = " $date as logdate,act_id,serverid,param,COUNT(*) act_count,COUNT(DISTINCT accountid) act_account,vip_level ";
    	$utable   = "u_behavior_$date";
    	$sql =  "SELECT $field FROM  $utable where act_id not in (49,13,14,25,15,26,29,20,43,44,101) GROUP BY act_id,param,serverid,vip_level ORDER BY act_id";
    	$query = $db_sdk->query($mysql.$sql." ON DUPLICATE KEY UPDATE `act_count`=VALUES(act_count),`act_account`=VALUES(act_account),`mysort`=VALUES(mysort)");
    	print_r($db_sdk->error());
    	/*if($query){
    		$data = $query->result_array();
    		foreach ($data as &$v){
    			$sor = $sorttype[$v['act_id']]?$sorttype[$v['act_id']]:0;
    			$mysql .= "({$v['logdate']},{$v['act_id']},{$v['serverid']},{$v['param']},{$v['act_count']},{$v['act_account']},{$sor},{$v['vip_level']}),";
    			unset($v);
    		}	
    	}
    	$db_sdk->reconnect();*/
    	$field = " $date as logdate,act_id,serverid,0 as param,COUNT(*) act_count,COUNT(DISTINCT accountid) act_account,vip_level ";
    	$sql =  "SELECT $field FROM  $utable where act_id in (49,13,14,25,15,26,29,20,43,44,101) GROUP BY act_id,serverid,vip_level ORDER BY act_id";
    	$query = $db_sdk->query($mysql.$sql." ON DUPLICATE KEY UPDATE `act_count`=VALUES(act_count),`act_account`=VALUES(act_account),`mysort`=VALUES(mysort)");
    	print_r($db_sdk->error());
    	/*if($query){
    		$data = $query->result_array();
    		foreach ($data as &$v){
    			$sor = $sorttype[$v['act_id']]?$sorttype[$v['act_id']]:0;
    			$mysql .= "({$v['logdate']},{$v['act_id']},{$v['serverid']},0,{$v['act_count']},{$v['act_account']},{$sorttype[$v['act_id']]},{$v['vip_level']}),";
    			unset($v);
    		}
    	}
    	$db_sdk->reconnect();
    	$mysql = rtrim($mysql,',') ." ON DUPLICATE KEY UPDATE `act_count`=VALUES(act_count),`act_account`=VALUES(act_account),`mysort`=VALUES(mysort)";
    	$query = $db_sdk->query($mysql);
    	print_r($db_sdk->error());*/
    	return true;
    }
    /**
     * 道具产销统计记录总表
     * 
     * @author 王涛 20170204
     */
    public function ItemCount($date='')
    {
    	if(!$date){ //前天的数据
    		$date = date("Ymd",strtotime("-1 days"));
    	}
    	$db_sdk = $this->load->database('rootsdk', TRUE);
    	$mysql = "insert into mydb.sum_item_by_type(logdate,type,typeid,itemid,consume_num,get_num)  ";
    	$field = "$date as logdate,act_id as type,param as typeid,item_id,sum(if(type=1,item_num,0)) as consume_num,sum(if(type=0,item_num,0)) as get_num ";
    	$itable   = "sdk.item_trading_$date";
    	$utable   = "sdk.u_behavior_$date";
    	$sql = $mysql . "SELECT $field FROM $itable i inner join $utable u on i.behavior_id=u.id where item_id not between 1000000000 and 1999999999";
    	$sql .= " and  u.created_at between " . strtotime($date) . " AND " . (strtotime($date)+86399) . ' and act_id in (1,41) ';
    	$sql .= " group by item_id,act_id,param";
    	$sql .= " ON DUPLICATE KEY UPDATE logdate=values(logdate),type=values(type),typeid=values(typeid),itemid=values(itemid),consume_num=values(consume_num),get_num=values(get_num)";
    	$query = $db_sdk->query($sql);
    	print_r($db_sdk->error());
    	
    	$mysql = "insert into mydb.sum_item(logdate,type,itemid,consume_num,get_num)  ";
    	$field = "$date as logdate,act_id as type,item_id,sum(if(type=1,item_num,0)) as consume_num,sum(if(type=0,item_num,0)) as get_num ";
    	$sql = $mysql . "SELECT $field FROM $itable i inner join $utable u on i.behavior_id=u.id where item_id not between 1000000000 and 1999999999";
    	$sql .= " and  u.created_at between " . strtotime($date) . " AND " . (strtotime($date)+86399) ;
    	$sql .= " group by item_id,act_id";
    	$sql .= " ON DUPLICATE KEY UPDATE logdate=values(logdate),type=values(type),itemid=values(itemid),consume_num=values(consume_num),get_num=values(get_num)";
    	 
    	$query = $db_sdk->query($sql);
    	print_r($db_sdk->error());
    	return true;
    }

    public function FoolBird($timestamp1, $timestamp2, $channel, $typeid_list)
    {
        //编号：process_index
        //结果：process_result字段
        //总数：process_index字段=1的数据条数
        $table   = "u_game_process_".date('Ym',$timestamp1);
        $sql_total = "SELECT count(*) as cnt from {$table}";
        $where = " WHERE appid={$this->appid} AND created_at between $timestamp1 AND $timestamp2 ";
        if (is_numeric($channel) && $channel>0) $where .= " AND channel=$channel";
        elseif (is_array($channel) && count($channel)>0) $where .= " AND channel IN(".implode(',', $channel).")";
        $sql_total = $sql_total . $where . " AND process_index=1";
        $query_total = $this->db_sdk->query($sql_total);
        //echo $sql_total;
        if (!$query_total) return false;
        if (is_numeric($typeid_list) && $typeid_list>0) $where .= " AND process_index=$typeid_list";
        elseif (is_array($typeid_list) && count($typeid_list)>0) $where .= " AND process_index IN(".implode(',', $typeid_list).")";
        $total = $query_total->result_array();
        $sql_query = <<<SQL
select process_index,process_result,count(*) as cnt from {$table} $where
group by process_index,process_result
ORDER BY process_index asc,process_result asc
SQL;
        //echo $sql_query;
        $query = $this->db_sdk->query($sql_query);
        $result = $query->result_array();
        $output = [];
        foreach ($result as $item) {
            $output[$item['process_index']][$item['process_result']]['cnt'] = $item['cnt'];
            $output[$item['process_index']][$item['process_result']]['per'] = number_format($item['cnt'] / $total[0]['cnt'] * 100,2);
        }
        return ['total'=>$total[0]['cnt'], 'data'=>$output];
    }

    public function BehaviorProductSaleConf()
    {
        return [
            1=> ['title'=>'商店购买', 'params'=>[1=> '道具商店',2=> '联盟商店',3=> '冠军商店', 4=> '全球商店',6=> '神秘商店',7=> '友好商店']],
            2=> ['title'=>'普通关卡', 'params'=>'通关的关卡id'],
            3=> ['title'=>'精英关卡', 'params'=>'通关的关卡id'],
            4=> ['title'=>'试练挑战', 'params'=>'通关的关卡id'],
            5=> ['title'=>'关卡任务', 'params'=>'完成的任务id'],
            6=> ['title'=>'联盟大赛', 'params'=>[1=> '挑战',2=> '排名结算']],
            7=> ['title'=>'祈愿', 'params'=>[1=> '第一次',2=> '第二次',3=> '第三次',4=> '第四次']],
            8=> ['title'=>'好友体力赠送', 'params'=>'今日领取的体力次数'],
            9=> ['title'=>'七日礼包', 'params'=>'领取第几天的礼包'],
            10=>['title'=>'购买金币', 'params'=>'购买次数'],
            11=>['title'=>'购买体力', 'params'=>'记录购买次数'],
            12=>['title'=>'副本评星奖励', 'params'=>'领取的副本片区id'],
            13=>['title'=>'精灵进化', 'params'=>'精灵的id'],
            14=>['title'=>'分配努力值', 'params'=>'精灵的id'],
            15=>['title'=>'图鉴升级', 'params'=>'精灵的类型id'],
            16=>['title'=>'vip特权礼包', 'params'=>'领取第几级的VIP特权礼包'],
            17=>['title'=>'成就奖励', 'params'=>'成就任务id'],
            18=>['title'=>'狩猎场', 'params'=>[1=> '初级狩猎场',2=> '中级狩猎场',3=> '高级狩猎场']],
            19=>['title'=>'全球对战', 'params'=>[1=> '对战',2=> '星级宝箱',3=> '赛季结算']],
            20=>['title'=>'固定交换', 'params'=> [1=> '初级交换',2=> '中级交换',3=> '高级交换']],
            21=>['title'=>'社团捐献', 'params'=>'捐献第几次'],
            22=>['title'=>'扭蛋', 'params'=>[1=> '免费扭蛋',2=> '购买一次',3=> '购买十次']],
            23=>['title'=>'商店刷新', 'params'=>[2=> '联盟商店',3=> '冠军商店',4=> '全球商店',6=> '神秘商店',7=>'友好商店']],
            24=>['title'=>'活跃礼包', 'params'=>[1=> '第一个活跃礼包',2=> '第二个活跃礼包',3=> '第三个活跃礼包']],
            25=>['title'=>'精灵融合', 'params'=>'精灵id'],
            26=>['title'=>'图鉴合成', 'params'=>'',],
            27=>['title'=>'道具出售', 'params'=>'',],
            28=>['title'=>'精灵放生', 'params'=>'',],
            29=>['title'=>'购买精英副本次数', 'params'=>'精英副本关卡id'],
            30=>['title'=>'一日三餐', 'params'=>[1=> '午餐',2=> '晚餐',3=> '夜宵']],
            31=>['title'=>'日常任务', 'params'=>'任务id'],
            32=>['title'=>'兑换码礼包', 'params'=>''],
            33=>['title'=>'封测排名礼包', 'params'=>'名次'],
            34=>['title'=>'封测冲级礼包', 'params'=>'领取礼包对应的等级'],
        ];
    }
    
    //  活跃玩家钻石途径  zzl 20170629
    public function actDistribute($where,$field,$group){
    	$sql = "select  $field  FROM `u_behavior_{$where['date']}` a,item_trading_{$where['date']} b WHERE a.vip_level={$where['vip_level']} and b.item_id=3 AND type={$where['type']} and a.id=b.behavior_id ";
    	    	
	    if($group){
    		$sql .= " group by $group";    	}

    	$query = $this->db_sdk->query($sql);    
        
    	if ($query) return $query->result_array();
    	return array();    
    }
    
    
    /**
     * 行为产销统计(多天)
     * @param unknown $where
     * @return boolean
     * @author zzl  20170721
     */
    public function behaviorProduceSaleMore($where = array() , $field = '*' ,$group = '')
    {
    	$date = date("Ymd",$where['begintime']);
    	$itable   = "item_trading_$date";
    	$utable   = "u_behavior_$date";
    	//$sql = "SELECT $field FROM $itable i inner join $utable u on i.behavior_id=u.id inner join (select act_id,count(DISTINCT accountid) as caccountid FROM $utable  group by act_id)as n on u.act_id=n.act_id";
    	$sql = "SELECT $field FROM $itable i inner join $utable u on i.behavior_id=u.id ";
    	 
    	if($where['params']){
    		$sql .= " AND param IN(".implode(',', $where['params']).")";
    	}
    	if($where['userid']){
    		$sql .= " AND userid =".$where['userid'];
    	}
    	if($where['serverids']){
    		$sql .= " AND u.serverid IN(".implode(',', $where['serverids']).")";
    	}
    	if($where['channels']){
    		$sql .= " AND u.channel IN(".implode(',', $where['channels']).")";
    	}
    	if($where['typeids']){    	
    		$sql .= " AND typeid = {$where['typeids']}";
    	}
    	if(isset($where['type'])){
    		$sql .= " AND i.type = {$where['type']}";
    	}
    	 
    	if(isset($where['item_id'])){
    		$sql .= " AND item_id = {$where['item_id']}";
    	}
    	if(isset($where['accountid'])){
    		if($where['accountid']==0){
    			$sql .= " AND accountid!=0";
    		}
    	}
    
    	if($group){
    		$sql .= " group by $group";
    	} 
    	$query = $this->db_sdk->query($sql);
    	if ($query) return $query->result_array();
    	return false;
    }
    
    
    /**
     * 行为产销统计
     * @param unknown $where
     * @return boolean
     * @author zzl 20170721
     */
    public function behaviorProduceSaleByBehavior($where = array() , $field = '*' ,$group = '',$order ='',$limit='')
    {
    	$date = date("Ymd",$where['begintime']);
    	$utable   = "u_behavior_$date";
    	$sql = "select $field FROM $utable where 1=1";
    
    	if($where['params']){
    		$sql .= " AND param IN(".implode(',', $where['params']).")";
    	}
    	if($where['userid']){
    		$sql .= " AND userid =".$where['userid'];
    	}
    	if($where['viplev_min']){
    		$sql .= " AND vip_level >=".$where['viplev_min'];
    	}
    	if($where['viplev_max']){
    		$sql .= " AND vip_level <=".$where['viplev_max'];
    	}
    	if($where['serverids']){
    		$sql .= " AND serverid IN(".implode(',', $where['serverids']).")";
    	}
    	if($where['channels']){
    		$sql .= " AND channel IN(".implode(',', $where['channels']).")";
    	}
    	if($where['typeids']){
    		$sql .= " AND act_id IN(".implode(',', $where['typeids']).")";
    	}
    	if($where['beginh']){
    		$sql .= " AND from_unixtime(client_time,'%H%i') between {$where['beginh']} and {$where['endh']}";
    	}
    	if($group){
    		$sql .= " group by $group";
    	}
    	if($where['cid']){
    		$sql .= " having cid IN({$where['cid']})";
    	}
    	if($limit){
    		$sql .= " limit $limit";
    	}
    	$query = $this->db_sdk->query($sql);
    	if ($query) return $query->result_array();
    	return array();
    }
   //   获取一天的 vip分布  zzl 2017.8.2
    public function  vipDistribution( $where, $field, $group){    	
  
    	$date0 = $where['date'];
    	$date1 = date('Ymd',strtotime("$date0 +1 days"));
    	$date3 = date('Ymd',strtotime("$date0 +2 days"));
    	$date7 = date('Ymd',strtotime("$date0 +6 days"));
    	$data['day0'] = $data['day1'] = $data['day3'] =$data['day7']= array();
    	$wsql = '';
    	if($where['serverids']){
    		$wsql .= " AND a.serverid IN(".implode(',', $where['serverids']).")";
    	}
    	if($where['channels']){
    		$wsql .= " AND a.channel IN(".implode(',', $where['channels']).")";
    	}
    	$wsql .= " group by a.viplev";
    	if ($where ['beginserver'] && $where ['endserver']) {
    		$server_list = $this->db_sdk->query ( "select serverid from server_date where serverdate>={$where['beginserver']} and  serverdate<={$where['endserver']}" );
    		 
    		if ($server_list) {
    			foreach ( $server_list->result_array () as $k => $v ) {
    					
    				$server_list_new .= $v ['serverid'] . ',';
    			}
    			$server_list_new = rtrim ( $server_list_new, ',' );
    		}
    	}
    	
    	if ($server_list) {
    		$wsql .= " AND a.serverid IN($server_list_new)";
    	}
    	$sql = "SELECT COUNT(DISTINCT a.serverid,a.accountid) accountid_total,a.viplev FROM u_login_{$date0} a WHERE 1=1 ".$wsql;//当天登录数
    
    	$result =$this->db_sdk->query($sql);
    	if($result){
    		$data['day0'] = $result->result_array();
    	}    
   
    	return $data;    	
    	
    }
    
    /*
     *  点击 区服分布  zzl   20170810
     */
    public function  areaClickDistribution( $where, $field, $group){    
   
    	$sql = "SELECT viplev,serverid,count(*) as total FROM activity_click_{$where['date']} group by serverid";//当天登录数
    	
    	$result =$this->db_sdk->query($sql);
    	if($result){
    		$data = $result->result_array();
    	}
    	 
    	return $data;
    }
    
    /*
     * 全球对战-战斗回合数统计  段位分布   zzl 20170815
     */
    public function  danDistribution( $where, $field, $group){
    	

    	$Ym = '20'.substr($where['begintime'], 0,4);    
    	$sql = "select $field from game_data_$Ym gd inner join game_user_$Ym gu on gd.id=gu.gameid  where 1=1";
    	if($where['begintime']){
    		$sql .= " and gd.endTime>={$where['begintime']}";
    	}
    	if($where['endtime']){
    		$sql .= " and gd.endTime<={$where['endtime']}";
    	}
    	if($where['serverids']){
    		$sql .= " AND gu.serverid IN(".implode(',', $where['serverids']).")";
    	}
    	
    	if($where['accountid']){
    		$sql .= " and gu.accountid = {$where['accountid']}";
    	}
    	if($where['dan_s'] && $where['dan_e']){
    		$sql .= " and (gu.dan >= {$where['dan_s']} and  gu.dan <= {$where['dan_e']})";
    	}
    	if($where['continuous']){
    		$sql .= " and gd.continuous = {$where['continuous']}";
    	}
    	if($where['serverids']){
    	    $sql .= " AND gu.serverid IN(".implode(',', $where['serverids']).")";
    	}
    	
    	if(isset($where['type']) && $where['type'] != -1){
    		$sql .= " and gd.type={$where['type']}";
    	}
    	if($where['btype']){
    		$sql .= " and gd.btype={$where['btype']}";
    	}
    	if($group){
    		$sql .= " group by $group";
    	}
    	if($order){
    		$sql .= " order by dan";
    	}
    	$query = $this->db_sdk->query($sql);
    	if ($query) {
    		return $query->result_array();
    	}
    	return array();
    	
    }
    
    
    /*
     * 积分分布  zzl 2017 0908
     */
    public function  bonusDistribution( $where, $field, $group){
    
      if (! $field) {
          $field = '*';
      }
      $date0 = $where ['date']; 
      
      $sql = "select $field from u_behavior_{$where['date']} a inner join item_trading_{$where['date']} b on a.id=b.behavior_id and b.item_id=10034  where 1=1";      
   
      if ($where ['serverids']) {
          $sql .= " AND a.serverid IN(" . implode ( ',', $where ['serverids'] ) . ")";
      }
    
      if (  $where ['vip_level']) {
          $sql .= " and  a.vip_level={$where ['vip_level']}";
      }
      if ($group) {
          $sql .= " group by $group";
      }
      if ($order) {
          $sql .= " order by $order";
      }
      if ($limit) {
          $sql .= " limit $limit";
      }

      $this->db_sdk = $this->load->database('sdk', TRUE);
      $query = $this->db_sdk->query ( $sql );
      
      $result = array ();
      if ($query) {
          $result = $query->result_array ();
      }
      
      return $result;
      
      
  }
  
  
  /**
   *  精灵塔  增加条件
   * @author zzl 20170908
   */
  public function ActionByParam($where = array() , $field = '*' ,$group = '',$order ='',$limit='')
  {
  
      $date = date("Ymd",$where['begintime']);
      $utable   = "u_behavior_$date";
      $sql = "select $field FROM $utable where 1=1";
      
      if ($where ['beginserver'] && $where ['endserver']) {
          $server_list = $this->db_sdk->query ( "select serverid from server_date where serverdate>={$where['beginserver']} and  serverdate<={$where['endserver']}" );
           
          if ($server_list) {
              foreach ( $server_list->result_array () as $k => $v ) {
                  	
                  $server_list_new .= $v ['serverid'] . ',';
              }
              $server_list_new = rtrim ( $server_list_new, ',' );
          }
      }
       
      if ($server_list) {
          $sql .= " AND serverid IN($server_list_new)";
      }
       
      if($where['params']){
          $sql .= " AND param IN(".implode(',', $where['params']).")";
      }
      
      if($where['param_list']){
          $sql .= " AND param in ({$where['param_list']})";
      }
      if($where['userid']){
          $sql .= " AND userid =".$where['userid'];
      }
      if($where['viplev_min']){
          $sql .= " AND vip_level >=".$where['viplev_min'];
      }
      if($where['viplev_max']){
          $sql .= " AND vip_level <=".$where['viplev_max'];
      }
      if($where['serverids']){
          $sql .= " AND serverid IN(".implode(',', $where['serverids']).")";
      }
      if($where['channels']){
          $sql .= " AND channel IN(".implode(',', $where['channels']).")";
      }
      if($where['typeids']){
          $sql .= " AND act_id IN(".implode(',', $where['typeids']).")";
      }
      if($where['beginh']){
          $sql .= " AND from_unixtime(client_time,'%H%i') between {$where['beginh']} and {$where['endh']}";
      }
      if($where['typeids']){
          $sql .= " AND act_id IN(".implode(',', $where['typeids']).")";
      }
      if($group){
          $sql .= " group by $group";
      }
    
/*       if($where['cid']){
          $sql .= " having cid IN({$where['cid']})";
      } */
      if($limit){
          $sql .= " limit $limit";
      }
      $query = $this->db_sdk->query($sql);
      if ($query) return $query->result_array();
      return array();      
  
  }
  

}