<?php 
/*
	Simple Accounting Book - 
		One file house spending accounting program

	@version 0.2.0 Beta

	@Feture:
			1,accounting book
			2,auto split to serval people
	
	@Using Bootstrap3 Chart.js jQuery

	@Author  Weicheng Huang
	@E-mail  huangwc94@gmail.com
	
	@license MIT

*/
/*
	Start Debugging. Comment out to disable print-out of error
*/
//==============================================================
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);
date_default_timezone_set('America/New_york');

//==============================================================

//================Config========================================

//system info
define('SAB_NAME','Simple Accounting Book');
define("SAB_VERSION",'0.2.0');
define('SAB_RELASE_STATUS','Beta');

//global switch for site
define('SAB_SITE_IS_UP',true);

//global user configuration
$g_config = array(
	'account'=>array(
		'huangwc94'=>array(
			'passwd'=>'17126huang',
			'name'  =>'Weicheng Huang',
			'phone' =>'312-866-3816',
			'account'=>'huangwc94'
			),
		'taylor142'=>array(
			'passwd'=>'taylor142yqy',
			'name'  =>'Qiaoyu Yang',
			'phone' =>'614-747-5188',
			'account'=>'taylor142'
			),
		'6142148222'=>array(
			'passwd'=>'5661466',
			'name'  =>'Zhongrong Zhuang',
			'phone' =>'614-214-8222',
			'account'=>'6142148222'
			),

		),
	'house'=>array(
		'name'=>'Taylor 142',
		'address'=>'5001 Olentangy River Rd,Apt142',
		'city'=>'Columbus',
		'state'=>'Ohio',
		'zip'=>'43214'
		),
	'database'=>array(
		'host'=>'localhost',
		'user'=>'root',
		'password'=>'',
		'db'=>'test',
		'db_prefix'=>'t_'
		)
	);


//================================================================
/*
*/

/* simple router */
if(SAB_SITE_IS_UP)
	main();
else
	renderOff();

function main(){
	session_start();
	$user = user();
	if($user){
		if(isset($_GET['ajax'])){//ajax protal
			if(function_exists('ajax'.ucfirst($_GET['ajax']))){
				$f = 'ajax'.ucfirst($_GET['ajax']);
				$arg = array();
				foreach($_GET as $k=>$v){
					$arg[$k] = safe($v);
				}
				$f($arg);
			}else{
				render404();
			}
			
		}else{// user portal
			proc_main($user);
		}
	}else{	
		proc_login();
	}
	
}

/* user action */

function proc_main($user){
	$data = array();
	$detail=array(
		array(
			'id'=>1,
			'user'=>'Weicheng Huang',
			'amount'=>34.20,
			'tag'  =>'Food',
			'description'=>'CAM',
			'create_time'=>'2015-3-12'
			),
		array(
			'id'=>2,
			'user'=>'Weicheng Huang',
			'amount'=>324.20,
			'tag'  =>'Piano',
			'description'=>'Music Ground',
			'create_time'=>'2015-3-11'
			),
		array(
			'id'=>3,
			'user'=>'Weicheng Huang',
			'amount'=>12.20,
			'tag'  =>'Food',
			'description'=>'Walmart',
			'create_time'=>'2015-3-10'
			)
		);
	$deleteUrl = ajaxLink('delete');
	$clearUrl  = ajaxLink('clearbalance');

	$chart_data = get_chart_data();
	$week = json_encode($chart_data['week']);
	$month = json_encode($chart_data['month']);
	$month_label = json_encode($chart_data['month_label']);
	$year  = json_encode($chart_data['year']);
	$year_label = json_encode($chart_data['year_label']);
	$script=<<<EOF
	
$(function(){
	
Chart.defaults.global.responsive = true;
var data = {
	week:{
	    labels: ["Monday","Tuesday", "Wednesday", "Thursday", "Friday", "Saturday","Sunday"],
	    datasets: [
	        {
	            label: "Spend",
	            fillColor: "rgba(151,187,205,0.2)",
	            strokeColor: "rgba(151,187,205,1)",
	            pointColor: "rgba(151,187,205,1)",
	            pointStrokeColor: "#fff",
	            pointHighlightFill: "#fff",
	            pointHighlightStroke: "rgba(151,187,205,1)",
	            data: {$week}
	        }
	    ]
	},
	month:{
	    labels: {$month_label},
	    datasets: [
	       
	        {
	            label: "Spend",
	            fillColor: "rgba(151,187,205,0.2)",
	            strokeColor: "rgba(151,187,205,1)",
	            pointColor: "rgba(151,187,205,1)",
	            pointStrokeColor: "#fff",
	            pointHighlightFill: "#fff",
	            pointHighlightStroke: "rgba(151,187,205,1)",
	            data: {$month}
	        }
	    ]
	},
	year:{
	    labels: {$year_label},
	    datasets: [
	        
	        {
	            label: "Spend",
	            fillColor: "rgba(151,187,205,0.2)",
	            strokeColor: "rgba(151,187,205,1)",
	            pointColor: "rgba(151,187,205,1)",
	            pointStrokeColor: "#fff",
	            pointHighlightFill: "#fff",
	            pointHighlightStroke: "rgba(151,187,205,1)",
	            data: {$year}
	        }
	    ]
	},
};


var char_list = new Array();

function draw(id){

	if(char_list[id]){
		char_list[id].destroy();
	}
	$("#"+id).html('<canvas id="Total_'+id+'"></canvas>');

	var ctx = document.getElementById('Total_'+id).getContext("2d");
	
	char_list[id] = new Chart(ctx).Line(data[id],{});
	
		
}
draw("week");
$(document).delegate('#chartm li a','click',function(e){
	var ca = $(this).attr('href');
	var ca = ca.substr(1);
	
	draw(ca);
});

	$('.delete').click(function(){
		var id = $(this).attr('data');
		if(confirm('Are you sure to delete this statement?')){
			$.getJSON('{$deleteUrl}',{id:id},function(result){
				if(result.status == '200'){
					alert('ok');
					window.location.href = 'index.php';
				}else{
					alert(result.message);
				}
			});
		}
		
	});
	$('#clear-balance').click(function(){
		var id = $(this).attr('data');
		if(confirm('Are you sure to clear the balance? This will clear both your roommate\'s balance')){
			$.getJSON('{$clearUrl}',{},function(result){
				if(result.status == '200'){
					alert('Clear Balance Successful');
					window.location.href = 'index.php';
				}else{
					alert('You can not delete a cleared transaction');
				}
			});
		}
	});

});




EOF;

	$form['amount'] = '';
	$form['description']='';
	if(isset($_GET['status'])&&$_GET['status']=='ok'){
		$data['notice'] = array('type'=>'success','message'=>'Add data successfully');
	}
	if(isset($_POST['amount'])&&isset($_POST['description'])){
		$form['amount'] = safe($_POST['amount']);
		$form['description']= safe($_POST['description']);
		if(!is_numeric($form['amount'])){
			$data['notice'] = array('type'=>'danger','message'=>'The amount is not a number');
		}else{
			if(add($form['amount'],$form['description'])){
				header('Location: index.php?status=ok');
			}else{
				$data['notice'] = array('type'=>'danger','message'=>'Database error');
			}
		}
	}
	$data['user'] = $user;
	$data['limit'] = 20;
	if(isset($_GET['page'])){
		$data['page'] = intval(safe($_GET['page']));
		$data['current_offset'] = $data['limit']*$data['page'];
	}else{
		$data['page'] = 0;
		$data['current_offset'] = 0;
	}
	
	
	global $g_config;
	$data['detail'] = get_list($data['current_offset'],$data['limit'] );
	

	$data['form'] = $form;

	foreach ($g_config['account'] as $key => $value) {
		$balance = get_user_current_balance($key);

		$data['current_balance_user'][$key]['balance'] = $balance[0];
		$data['current_balance_user'][$key]['name'] = $g_config['account'][$key]['name'];
		$data['current_balance_user'][$key]['phone'] = $g_config['account'][$key]['phone'];
		$data['current_balance'] = $balance[1];
	}






	renderMain($data,$script);
}
function proc_login(){
	if(isset($_POST['account'])&&isset($_POST['password'])){

		$usr = safe($_POST['account']);
		$psd = safe($_POST['password']);
		if(vaildate($usr,$psd)){
			renderJump('You are successfully login',ajaxLink());
		}else{
			renderLogin("Can not find match account and password");
		}
	}else{
		renderLogin();
	}
}



/* ajax action */
// function ajaxTest(){
// 	echo json_encode(get_user_current_balance('huangwc94'));
// }
function ajaxLogout(){
	logout();
	renderJump('You are log out!',ajaxLink());
}
function ajaxGet($arg){
	$id = $arg['id'];
	$re = get($id);
	if($re){
		echo json_encode($re);
	}else{
		echo json_encode(array('status'=>'404'));
	}
}

function ajaxDelete($arg){
	$re = delete($arg['id']);
	switch($re){
		case 0:
			echo json_encode(array('status'=>'404','message'=>'Can not find that balance'));
			break;
		case 1:
			echo json_encode(array('status'=>'403','message'=>'You can not delete a cleared balance'));
			break;
		case 2:
			echo json_encode(array('status'=>'403','message'=>'You can not delete someone else\'s balance '));
			break;
		case 3:
			echo json_encode(array('status'=>'200'));
			break;
	}
}
function ajaxClearbalance($arg){
	clear_balance();
	echo json_encode(array('status'=>'200'));
}
/* model */
function user(){
	if (!isset($_SESSION['is_login'])||$_SESSION['is_login']==false || !isset($_SESSION['user'])) {
	  return false;
	} else {
	  return $_SESSION['user'];
	}
}

function get_list($offset,$limit){
	$con = get_con();
	global $g_config;
	$re  = mysqli_query($con,"SELECT * FROM `".$g_config['database']['db_prefix']."accounting` ORDER BY create_time DESC LIMIT ".$offset.",".$limit);
	$return = array();
	while($tmp = mysqli_fetch_assoc($re)){
		$return[] = $tmp;
	}
	return $return;
}
function get_user_current_balance($name){
	$con = get_con();
	global $g_config;
	$re  = mysqli_query($con,"SELECT `amount`,`user`  FROM `".$g_config['database']['db_prefix']."accounting` WHERE is_finished='0'");
	$total_user = 0;
	$total = 0;
	while($tmp = mysqli_fetch_assoc($re)){
		if($tmp['user']==$name){
			$total_user += $tmp['amount'];
		}
			
		$total +=$tmp['amount'];
	}
	return array($total/count($g_config['account']) - $total_user ,$total);
}
function get_chart_data(){
	
	global $g_config;
	$this_year = date("Y");
	
	$this_month = intval(date("m"));
	
	$month_dir = array(
			1=>'Jan',
			2=>'Feb',
			3=>'Mar',
			4=>'Apr',
			5=>'May',
			6=>'Jue',
			7=>'Jun',
			8=>'Aug',
			9=>'Sept',
			10=>'Oct',
			11=>'Nov',
			12=>'Dec'
		);

	$this_week = array(
		date('Y-m-d',strtotime('this Monday')),
		date('Y-m-d',strtotime('this Tuesday')),
		date('Y-m-d',strtotime('this wednesday')),
		date('Y-m-d',strtotime('this thursday')),
		date('Y-m-d',strtotime('this friday')),
		date('Y-m-d',strtotime('this saturday')),
		date('Y-m-d',strtotime('this sunday')));

	$con = get_con();
	$re  = mysqli_query($con,"SELECT `amount`,`create_time`  FROM `".$g_config['database']['db_prefix']."accounting` WHERE year(create_time) =" .$this_year);
	

	//init return array
	$return_ = array();
	$return_['month_label'] = array();
	$return_['year_label']  = array();
	$return_['week']        = [0,0,0,0,0,0,0];
	$return_['month'] = [];
	$return_['year']   = [];

	
	while($act=mysqli_fetch_assoc($re)){
		$year_month_date = explode('-',$act['create_time']);
		$year = $year_month_date[0];
		$month = $year_month_date[1];
		$day   = $year_month_date[2];
		$amount = $act['amount'];
		

		if(in_array($month_dir[intval($month)],$return_['year_label'])){//year counter
			$return_['year'][$month] += $amount;
		}else{
			$return_['year_label'][]  = $month_dir[intval($month)];
			$return_['year'][$month]= $amount;
		}
		if(intval($month) == $this_month){
			if(in_array($day,$return_['month_label'])){//month counter
				$return_['month'][$day] += $amount;
			}else{
				$return_['month_label'][]  = $day;
				$return_['month'][$day]  = $amount;
			}
		}
		if(in_array($act['create_time'],$this_week))
			$return_['week'][array_search($act['create_time'],$this_week)] +=$amount;
	}
	
	// unset($tmp);
	// foreach($return_['month'] as $k =>$v){
	// 	$tmp[]
	// }
	ksort($return_['month']);
	ksort($return_['year']);
	sort($return_['month_label']);
	sort($return_['year_label']);
	$return_['month'] = array_values($return_['month']);
	$return_['year']  = array_values($return_['year']);
	return $return_;
}
function add($amount,$description){
	$user = user();
	global $g_config;
	if($user){
		$con = get_con();
		$user_account = $user['account'];

		if(mysqli_query($con,"INSERT INTO `".$g_config['database']['db_prefix']."accounting` (`id`, `is_finished`, `user`, `amount`, `create_time`, `description`) VALUES (NULL, '0', '".$user_account."', '".$amount."', CURRENT_DATE, '".$description."')")){
			return true;
		}
	}
	return false;
}
function get($id){
	global $g_config;
	$con = get_con();
	$re = mysqli_query($con,'SELECT * FROM `'.$g_config['database']['db_prefix'].'accounting` WHERE id='.$id);
	$result = mysqli_fetch_assoc($re);
	if($result){
		return $result;
	}else{
		return false;
	}
	
}
function clear_balance(){
	global $g_config;
	$con = get_con();
	$re = mysqli_query($con,'UPDATE `'.$g_config['database']['db_prefix'].'accounting` SET `is_finished` = 1 WHERE is_finished=0');
}
function delete($id){
	$user = user();
	$name = $user['account'];
	global $g_config;
	$con = get_con();
	$count = mysqli_query($con,'SELECT * FROM `'.$g_config['database']['db_prefix'].'accounting` WHERE id='.$id);
	$re = mysqli_fetch_assoc($count);
	if($re){
		if($re['is_finished']!=0){
			return 1;
		}
		if($re['user'] != $name){
			return 2;
		}
		$re = mysqli_query($con,'DELETE FROM `'.$g_config['database']['db_prefix'].'accounting` WHERE id='.$id);
		return 3;
	}else{
		return 0;
	}
		
}
function data_count(){
	global $g_config;
	$con = get_con();
	$count = mysqli_query($con,'SELECT COUNT(*) FROM `'.$g_config['database']['db_prefix'].'accounting`');
	return mysqli_fetch_assoc($count)['COUNT(*)'];
}


/* helper */
function get_con(){
	global $g_config;
	$con = mysqli_connect($g_config['database']['host'],$g_config['database']['user'],$g_config['database']['password']);
	mysqli_select_db($con,$g_config['database']['db']);
	return $con;
}
function vaildate($account,$passwd){
	global $g_config;

	if(array_key_exists($account, $g_config['account'])){
		$user = $g_config['account'][$account];
		if($user['passwd'] == $passwd){
			$_SESSION['is_login'] = true;
			$_SESSION['user']     = $g_config['account'][$account];
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}

function logout(){
	unset($_SESSION['is_login']);
	session_destroy();
}

//url builder
function ajaxLink($action='',$data=[]){
	if($action=='')
		return 'index.php';
	$url = 'index.php?ajax='.$action;
	foreach($data as $k =>$v){
		$url .="&".$k."=".$v;
	}
	return $url;
}

//filter
function safe($s){
	return sql_filter(xss_filter($s));
}
function sql_filter($s){
	return $s;
}
function xss_filter($s){
	return $s;
}

/* view */

//snippet
function renderHeader(){
	global $g_config;
	?>
		<!DOCTYPE html>
	<html>
	  <head>
	    <title><?php echo $g_config['house']['name'].' - '.SAB_NAME.' '.SAB_VERSION.' '.SAB_RELASE_STATUS?></title>
	    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	    <!-- Bootstrap -->
	    
	    <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
	    <link href="css/bootstrap-theme.min.css" rel="stylesheet" media="screen">
	    <link href="css/jquery.dataTables.min.css" rel="stylesheet" media="screen">
	    <link rel="icon" href="favicon.ico" type="image/x-icon" />
	    <meta charset="utf-8">
	  </head>
	  <body>
	<?php
}
function renderFooter($user_script=""){
	?>
	<hr>
	<div class="container">
		<div class="row text-center">
			<h5 class="small" style="color:gray"><?php echo SAB_NAME.' '.SAB_VERSION.' '.SAB_RELASE_STATUS?></h5>
			<h5 class="small" style="color:green">Weicheng Huang @ 2015</h5>
		</div>
	</div>
	</body>
	<script src="js/jquery-1.11.3.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/Chart.min.js"></script>
    <script ><?php echo $user_script ?></script>
    </html>
	<?php
	
}

//pages
function renderOff(){
	renderHeader();
	?>
	<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="error-template">
                <h1>
                    Sorry!</h1>
                <h2>
                    We are close</h2>
                <div class="error-details">
                    This site is currently close!
                </div>
                <hr>
               
            </div>
        </div>
    </div>
</div>
	<?php
	renderFooter();
}
function renderJump($display,$link){
		renderHeader();
	?>
	<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="error-template">
                <h1>
                    <?php echo $display ?></h1>
                <h2>
                    Jumpping after <strong id="time">1</strong> seconds...</h2>
                <hr>
                <p>If you are not jumpping after 1 second, click <a href="<?php echo $link ?>">here</a></p>
            </div>
        </div>
    </div>
</div>
	<?php

	$script=<<<EOF

	$(function(){
		var time = 1;
		setInterval(function(){
			time = time - 1;
			$('#time').html(time);
		},1000);
		
		setTimeout(function(){
			window.location.replace("{$link}");
		}, 1300);

	});


EOF;

	renderFooter($script);
}

function renderMain($data = [],$script=''){
	renderHeader();
	global $g_config;
	?>
	<nav class="navbar navbar-default">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#"><?php echo $g_config['house']['name']?></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          
          <ul class="nav navbar-nav navbar-right">
            <li><a href="<?php echo ajaxLink('logout')?>">Logout</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
    <div class="container">

      <!-- Main component for a primary marketing message or call to action -->
      
      <div class="row" >
      	<div class="page-header">
	        <h1>Member</h1>
	      </div>
      	<?php foreach($data['current_balance_user'] as $balance):?>
      	<div class="col-sm-4">
      		<div class="panel panel-primary">
            <div class="panel-heading">
              <h3 class="panel-title"><?php echo $balance['name'] ?></h3>
            </div>
            <div class="panel-body">
              	<?php if($balance['balance']>0): ?>
      				<h3 style="color:orange">Owned <strong>$<?php  printf('%.2f', $balance['balance'])?></strong></h3>
	      		<?php elseif($balance['balance']<0):?>
	      			<h3 style="color:red">Earned <strong>$<?php printf('%.2f', -$balance['balance'])?></strong></h3>
	      		<?php else:?>
	      			<h3 style="color:green">You are clear!</h3>
	      		<?php endif;?>
            </div>
            <div class="panel-footer">
            	<?php echo $balance['phone'] ?>
            </div>
          </div>

      		
      	</div>
      <?php endforeach;?>
  </div>
      <div class="row">
      <div class="col-md-6 ">
      	<div class="page-header">
	        <h1>Add</h1>
	      </div>
      	
      	<?php if(array_key_exists('notice',$data)): ?>
            <div class="alert alert-dismissible alert-<?php echo $data['notice']['type']?>">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <p><?php echo $data['notice']['message']?></p>
              </div>
          <?php endif;?>
      	      	<div class="col-md-12">
      		<form class="form-horizontal" method="post">
  <div class="form-group">
    <label  class="col-sm-2 control-label">Amount</label>
    <div class="col-sm-6">
      <input type="text" class="form-control" name="amount" autocomplete="off" value="<?php echo $data['form']['amount']?>">
    </div>
  </div>
  <div class="form-group">
    <label  class="col-sm-2 control-label">Description</label>
    <div class="col-sm-6">
      <input type="text" class="form-control" name="description" autocomplete="off" value="<?php echo $data['form']['description']?>">
    </div>
  </div>


  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-success">Add</button>
    </div>
  </div>
</form>
      	</div>
      </div>
            <div class="col-md-6">
      	<div class="page-header">
        <h1>Information</h1>
      </div>
      	<div class="col-md-12">

      		<dl class="dl-horizontal bg-primary">
			  <dt>Today</dt>
			  <dd><strong><?php echo date("Y-m-d H:i:s a")?></dd>
			  <dt>Current Total Balance</dt>
			  <dd><strong>$<?php echo $data['current_balance']?></dd>
			</dl>
      		<h3 style="color:purple"></strong></h3>
      		<address>
			  <strong><?php echo $g_config['house']['name']?></strong><br>
			  <?php echo $g_config['house']['address']?><br>
			  <?php echo $g_config['house']['city'].' '.$g_config['house']['state'].' '.$g_config['house']['zip']?><br>
			  
			</address>
      		<button class="btn btn-lg btn-success " id="clear-balance">Clear Balance</button>

      	</div>

      </div>
      </div>
<div class="row">
	<div class="page-header">
        <h1>Trend</h1>
      </div>
	
	<ul class="nav nav-tabs" role="tablist" id="chartm">
    <li role="presentation " class="active"><a href="#week" aria-controls="home" role="tab" data-toggle="tab">Week</a></li>
    <li role="presentation "><a href="#month" aria-controls="profile" role="tab" data-toggle="tab">Month</a></li>
    <li role="presentation "><a href="#year" aria-controls="messages" role="tab" data-toggle="tab">Year</a></li>
    
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="week"><canvas id="Total_week"></canvas></div>
    <div role="tabpanel" class="tab-pane" id="month"><canvas id="Total_month"></canvas></div>
    <div role="tabpanel" class="tab-pane" id="year"><canvas id="Total_year"></canvas></div>
    
  </div>

	

</div>
<div class="row">
	<div class="page-header">
        <h1>Detail Statement</h1>
      </div>
  	<div class="col-sm-12">
	<span class="label label-warning">Uncleared</span>
	<span class="label label-success">Cleared</span>
	<table class="table">
      <thead>
        <tr>
         
          <th>User</th>
          <th>Amount</th>
          <th>Description</th>
          <th>Create Time</th>
          
          
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      	<?php if(is_array($data['detail'])):?>
        <?php foreach($data['detail'] as $k): ?>
        <?php if($k['is_finished']==0):?>
        	<tr class="warning">
        <?php else:?>
        	<tr class="success">
        <?php endif;?>
        	
        	<td><?php echo $g_config['account'][$k['user']]['name'] ?></td>
        	<td>$<?php echo $k['amount'] ?></td>
        	<td><?php echo $k['description'] ?></td>
        	<td><?php echo $k['create_time']. " - ".date("D",strtotime($k['create_time'])) ?></td>

        	<td>
        		<?php if($k['is_finished']==0 && $k['user'] == $data['user']['account']):?>
        		<button class="btn btn-danger btn-sm delete" data="<?php echo $k['id']?>">Del</button>
        		<?php endif;?>
        	</td>
        </tr>
    	<?php endforeach;?>
    <?php endif;?>
      </tbody>
    </table>
    </div>
    <div class="col-sm-12">
    	<nav>
  <ul class="pager">
  	<?php if(intval($data['page'])>0): ?>
    	<li class="previous"><a href="index.php?page=<?php echo $data['page'] - 1?>">Newer<span aria-hidden="true">&larr;</span></a></li>
    <?php endif;?>
    <?php if(((intval($data['current_offset']))+$data['limit'])<data_count()): ?>

    	<li class="next"><a href="index.php?page=<?php echo (intval($data['page']) + 1) ?>">Older <span aria-hidden="true">&rarr;</span></a></li>
    <?php endif;?>
  </ul>
</nav>
    </div>
</div>
</div>
	<?php
	
	
	renderFooter($script);
}
function render404(){
	renderHeader();
	?>
	<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="error-template">
                <h1>
                    Oops!</h1>
                <h2>
                    404 Not Found</h2>
                <div class="error-details">
                    Sorry, an error has occured, Requested page not found!
                </div>
                <hr>
               <a class="btn btn-primary btn-sm" href="index.php">Back</a>
            </div>
        </div>
    </div>
</div>
	<?php
	renderFooter();
}

function renderLogin($notice='',$script=''){
	renderHeader();
	global $g_config;
	?>
		<link href="css/login.css" rel="stylesheet" media="screen">
	    <div class="container">
        <div class="card card-container">
            <!-- <img class="profile-img-card" src="//lh3.googleusercontent.com/-6V8xOA6M7BA/AAAAAAAAAAI/AAAAAAAAAAA/rzlHcD0KYwo/photo.jpg?sz=120" alt="" /> -->
            <img id="profile-img" class="profile-img-card" src="//ssl.gstatic.com/accounts/ui/avatar_2x.png" />
            <p id="profile-name" class="profile-name-card">Login To <?php echo $g_config['house']['name']?></p>
            <?php if($notice !=''):?>
            <div class="alert alert-dismissible alert-danger">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>Oh snap!</strong><p><?php echo $notice?></p>
              </div>
          <?php endif;?>
            <form class="form-signin" method="post">
                <span id="reauth-email" class="reauth-email"></span>
                <input type="text" id="inputEmail" class="form-control" name="account" placeholder="Account" required autofocus>
                <input type="password" id="inputPassword" class="form-control" name="password" placeholder="Password" required>
                
                <button class="btn btn-lg btn-primary btn-block btn-signin" type="submit">Sign in</button>
            </form><!-- /form -->
            <p class="text-center small"><?php echo SAB_NAME.' '.SAB_VERSION.' '.SAB_RELASE_STATUS?></p>
            
        </div><!-- /card-container -->
    </div><!-- /container -->
	<?php
	renderFooter($script);
}

?>