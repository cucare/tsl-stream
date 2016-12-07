<?php

$add_conf_button = '<input type="button" id="btn_add_conf"/ value="add new conference">';

$day_fields = array(
		'evt_upper_id' => array(
			'type' => 'hidden', 
			'td' => 'skip',
			'new' => 'db',
			'insert'=>true
		), 
		'evt_id' => array(
			'type' => 'hidden', 
			'td' => 'skip',
			'new' => 0
		), 
		'evt_beg_dt' => array(
			'type' => 'db', 
			'view' => 'show',
			'new' => 'day_beg_dt', 
			'edit' => 'input',
			'update'=>true,
			'insert'=>true
		), 
		'evt_beg_tm' => array(
			'type' => 'db', 
			'view' => 'show',
			'new' => '', 
			'edit' => 'input',
			'update'=>true,
			'insert'=>true
		), 
		'evt_title' => array(
			'type' => 'db', 
			'new' => '', 
			'edit' => 'input',
			'update'=>true,
			'insert'=>true
		),
		'btn' => array(
			'type' => 'button') 
			//'view' => '<input type="button" class="btn_run" value="RUN"/>',
			//'new' => '<input type="button" class="btn_save" value="SAVE"/>', 
			//'edit' => '<input type="button" class="btn_save" value="SAVE"/>')
	);
	
//=======================================================================================================
	
require("pass.php");

$connect = db_connect($pass);

//=======================================================================================================


//=======================================================================================================
//---------------------------------------------- AJAX ---------------------------------------------------

if(isset($_REQUEST['function']))
{
	if($connect['status'] != 'ok') $response = $connect;

	$dbh = $connect['dbh'];
	
	switch($_REQUEST['function'])
	{
		//------------------------------------------------------------------
		case 'create_conf_day':
			$response = create_conf_day();
		break;
		
		//------------------------------------------------------------------
		case 'get_main_table':
			$response = get_main_table($_REQUEST['conf_id']);
			//$response = $_REQUEST;
		break;
		
		//------------------------------------------------------------------
		case 'save_item':
			$response = save_item();
		break;
		
		//------------------------------------------------------------------
		case 'delete_item':
			$response = delete_item();
		break;
		
		//------------------------------------------------------------------
		case 'save_day_header':
			$response = save_day_header();
		break;
		
		//------------------------------------------------------------------
		case 'delete_day_header':
			$response = delete_day_header();
		break;
		
		//------------------------------------------------------------------
		case 'add_conf':
			$response = add_conf();
		break;
		
		//------------------------------------------------------------------
		case 'delete_conf':
			$response = delete_conf();
		break;
		
		//------------------------------------------------------------------
		default:
			$response = array('status'=>'fail', 'errm'=>'invalid function');
	}

	//--------------------------------------------------------------------------------
	header('Content-type: application/json'); // заголовок json
	echo json_encode($response); // сформированные данные преобразуем в формат json
	exit; // закончили
}
//=======================================================================================================

if($connect['status'] != 'ok')
{
	echo '<html><title></title><body><b> could not connect database </b><br/><br/> <pre>';
	print_r($connect);
	echo '</pre></body></html>';
	exit;
}

$dbh = $connect['dbh'];

//=======================================================================================================

$conf_data = get_confs_dd();

$first_conf_id = $conf_data['first_id'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <title></title>
        <meta name="google" value="notranslate">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style type="text/css" media="screen">
			html, body	{ height:100%; }
			body { overflow:auto; text-align:left;
			       background-color: #FFFFFF; }
			object:focus { outline:none; }
			#flashContent { display:none; }
			
			.template
			{
				display:none;
			}
			.day_table
			{
				border: 1px solid;
				border-collapse: collapse;
			}
			.updated
			{
				background-color: #ffa;
			}
			.running
			{
				background-color: #8f8;
			}
			.day_header
			{
				text-align:center;
				background-color:#aaa;
			}
        
        </style>
		<head>
<link rel="stylesheet" href="css/jquery-ui.css">
<script src="js/jquery.js" type="text/javascript"></script>
<script src="js/jquery-ui.js" type="text/javascript"></script>

<script language="JavaScript">
$(function() {

	var script_name = '<?= basename($_SERVER["SCRIPT_FILENAME"]); ?>';
	
	var conf_id = <?= $first_conf_id ?>;
	
	var day_fields = <?= json_encode($day_fields) ?>;
	
	var day_flds = Object.keys(day_fields);
	
	var day_span = 0;

	for(var i in day_flds)
	{
		var fld = day_flds[i];
		
		if(day_fields[fld].td != 'skip') day_span++;
	}
	
	
	var items = [];
	var day_headers = [];
	var running_evt_id;
	
	var spinners = [];
	var switches_req_arr = [];

	//----- собираем все спиннеры и формируем строку из их названий и соответствующих селекторов команад -------------------------------------------------------------------
	$(".spinner").each(function(){
		var spinner_name = $(this).attr('name');
		//var switch_name = $(this).parent().parent().find("button[data-action=run]").attr('data-switch');
		var switch_name = $(this).attr('data-switch');

		spinners.push(spinner_name);
		switches_req_arr.push(spinner_name + '--' + switch_name);
	});

//alert(JSON.stringify(switches_req_arr));

	//------ отправляем запрос на получение текущих значений спиннеров и положения радио-баттона ------------------------------------------------------------------
	//      $.ajax({
	//      	url: 'ffmpeg_control.php?action=get_saved_params&switches='+switches_req_arr.join(),
	//      	dataType: "json",
	//      	success: function(response){ init_params(response); },
	//      	error: function(){ alert('params init fail'); }
	//      });

	//------- инициализация полей ввода - спиннеров и радио-баттона ---------------
	function init_params(response)
	{
		for(var i in spinners)
		{
			$(".spinner[name="+spinners[i]+"]").val(response[spinners[i]]); // вписываем значения спиннеров
		}

		$(".action_radio[name=translation_source][value="+response.translation_source+"]").prop("checked", true); // устанавливаем радио-баттон
	}

	//------ инициализация спиннера ------------------------------------------------------------
    $( ".spinner" ).spinner({min:0});

	//------- клик на кнопку ---------------------------------------------------------
	$('.action_btn').click(function(){ exec_request('ffmpeg_control.php?action='+$(this).attr('data-action') + '&switch='+$(this).attr('data-switch') + '&' + $('#ffmpeg_form').serialize()); });

	//------- переключение радио-баттона -----------------------------------------------------------
	$('.action_radio').change(function(){ exec_request('ffmpeg_control.php?action=select&switch='+$(this).attr('name') + '&value=' + $(this).val()); });

	//------- отправка запроса команды -----------------------------------------------------------
	function exec_request(href)
	{
		refresh_status(href, 'waiting for response...', '#DCB220'); // сообщаем, что запрос отправлен

		$.ajax({
			url: href,
			success: function(response){ refresh_status(href, response, 'grey'); },
			error: function(){ refresh_status(href, 'request failed', 'red'); }
		});
	}

	//------ отображение информации в области служебной информации ------------------------------------------------
	function refresh_status(href, text, color)
	{
		var now = new Date();

		$("#request_str").text(href); // строка запроса
		$("#status_str").html('<span style="color:'+color+';">'+ now.toString().substring(16) +'<br/>'+ text +'</span>'); // время отправки запроса

		$(".status_cell").addClass('status_highlight'); // выделение цветом служебной области
		setTimeout(function() { $(".status_cell").removeClass('status_highlight'); }, 300); // снятие выделения цветом через таймаут
	}
	
	//==================================================================
	
	//------------------------------------------------------------------
	$("#btn_add_day").on('click', function(){

		$.ajax({
			url: script_name+'?function=create_conf_day&conf_id='+conf_id,
			dataType: 'json',
			success: function(response){
//alert(JSON.stringify(response));
				draw_table(response);
			},
			error: function(){
				alert('server error');
			}
		});
		
	});
	
	//------------------------------------------------------------------
	$('body').on('change', '#conf_dd', function(){
		
		conf_id = $(this).val();
//alert(conf_id);
		get_table();
	});
		
	//------------------------------------------------------------------
	$(document).ready(function(){
		
		get_table();
	});
		
	//------------------------------------------------------------------
	function get_table()
	{
		$.ajax({
			url: script_name+'?function=get_main_table&conf_id='+conf_id,
			dataType: 'json',
			success: function(response){
//alert(JSON.stringify(response));
				draw_table(response);
			},
			error: function(){
				alert('server error');
			}
		});
	}
	
	//------------------------------------------------------------------
	function draw_table(response)
	{
		if(typeof response.result != 'undefined')
		{
			$("#conf_title").html('<h2>'+response.conf_header.evt_title+'</h2>');
			$("#day_table").html('');
					
			day_ids = Object.keys(response.result);
			
			if(day_ids.length > 0)
			{
				var dates = [];
				var dates_by_id = [];
				
				//-------- сортировка по дате -----------
				
				for(var i in day_ids)
				{
					var day_id = day_ids[i];
					
					var dt = response.result[day_id].day_header.evt_beg_dt;
					dates.push(dt);
					dates_by_id[day_id] = dt;
				}

				dates.sort();

				var day_ids_sorted = [];
				
				for(var i in dates)
				{
					var dt = dates[i];
					
					for(var j in day_ids)
					{
						var day_id = day_ids[j];
						
						if(dates_by_id[day_id] == dt) { day_ids_sorted.push(day_id); dates_by_id[day_id] = ''; }
					}	
				}
						
				//------------------------------------------------------
				
				var day_num = 0;
				
				for(var i in day_ids_sorted)
				{
					var day_id = day_ids_sorted[i];

					items[day_id] = [];

					day_num++;

					var day_data = response.result[day_id].day_data;
					var day_header = response.result[day_id].day_header;
					day_headers[day_id] = day_header;
//alert(day_header.evt_beg_dt);
//alert(typeof response.result[day_id]);
//alert(JSON.stringify(response.result[day_id]));				
					
					append_table( make_day_header(day_num, day_id) );

					if(Array.isArray(day_data) && day_data.length > 0)
					{
	
						for(var item_num in day_data)
						{
							var item = day_data[item_num];
							
							items[day_id][item.evt_id] = item;
							
							var $tr = make_day_item(item);
							
							append_table($tr);
							
							
							if(item.evt_id == running_evt_id) $tr.addClass('running');
						}
					}
	
					var empty_item = make_empty_item({"evt_upper_id":day_id, 'day_beg_dt':day_header.evt_beg_dt});
					
					//empty_item.new = true;
	
					items[day_id][0] = empty_item;
					
					append_table(make_day_item(empty_item));
				}
			}
		}
		else if(response.status == 'ok') // -- после удаления не осталось конференций 
		{
			$("#conf_title").html('');
		}

	}
	
	//==================================================================
	
	//------------------------------------------------------------------
	$('body').on('click', '.td_click', function(){

		close_edit_items();
		
		var $tr = $(this).parent('tr');

		var evt_id = $tr.attr('data-evt_id');
		var day_id = $tr.attr('data-evt_upper_id');

		$tr.replaceWith(make_day_item(items[day_id][evt_id], {'edit':true, 'new':(evt_id == 0 ? true : false)}));
	});
	
	//------------------------------------------------------------------
	$('body').on('click', '.btn_run_item', function(){
		
		var $tr = $(this).parent().parent();
		
		running_evt_id = $tr.attr('data-evt_id');
		
		$("#day_table tr").removeClass('running');
		$tr.addClass('running');

		//alert(running_evt_id);
	});
	
	//------------------------------------------------------------------
	$('body').on('click', '.btn_save_item', function(){
		
		$.ajax({
			url: script_name+'?function=save_item&conf_id='+conf_id+'&'+$('#day_form').serialize(),
			dataType: 'json',
			success: function(response){
				
				if(response.status == 'ok')
				{
					draw_table(response);

					$('#day_table tr[data-evt_id='+response.upd_id+']').children('td').addClass('updated');
				}
				else
				{
					alert('save error');
				}
			},
			error: function(response){
					alert('server error');
			}
		});

		//alert(item_id);
	});
	
	//------------------------------------------------------------------
	$('body').on('click', '.btn_delete_item', function(){
		
		if( !confirm('are you sure?') ) return false;
		
		var item_id = $(this).parent().parent().attr('data-evt_id');

		$.ajax({
			url: script_name+'?function=delete_item&conf_id='+conf_id+'&evt_id='+item_id,
			dataType: 'json',
			success: function(response){
				
				if(response.status == 'ok')
				{
					draw_table(response);
				}
				else
				{
					alert('delete error');
				}
			},
			error: function(response){
					alert('server error');
			}
		});

		//alert(item_id);
	});
	
	//------------------------------------------------------------------
	$('body').on('click', '.btn_cancel_item', function(){
		
		close_edit_items();
	});
	
	//------------------------------------------------------------------
	function cancel_edit($tr)
	{
		var evt_id = $tr.attr('data-evt_id');
		var day_id = $tr.attr('data-evt_upper_id');
		
		$tr.replaceWith(make_day_item(items[day_id][evt_id]));
	}
	
	//------------------------------------------------------------------
	function append_table(tr){ $("#day_table").append(tr); }

	//------------------------------------------------------------------
	function make_day_header(day_num, day_id)
	{
		var day_header = day_headers[day_id];
		var title = 'DAY '+day_num;
		var day_date = ''
		
		if( ! (!day_header.evt_title) ) title = day_header.evt_title;
		
		if( ! (!day_header.evt_beg_dt) ) day_date = day_header.evt_beg_dt;
		
		return '<tr class="tr_day_header day_header" data-evt_id='+day_header.evt_id+' data-day_num='+day_num+'>'
			+'<td width="20%" nowrap>'+day_date+'</td>'
			+'<td colspan="'+(day_span*1-1)+'" width="80%">'+title+'</td>'
			+'</tr>';
	}
	
	//------------------------------------------------------------------
	$('body').on('click', '.tr_day_header', function(){
		
		close_edit_items();
		
		var $tr = $(this);

		var day_id = $tr.attr('data-evt_id');
		var day_num = $tr.attr('data-day_num');
		var day_header = day_headers[day_id];
		
		$tr.replaceWith('<tr class="tr_day_header_edit day_header" data-evt_id='+day_id+' data-day_num='+day_num+'><td colspan="'+(day_span*1-1)+'">'
			+'<input type="text" name="evt_beg_dt" value="'+day_header.evt_beg_dt+'"/>'
			+'<input type="text" name="evt_title" value="'+day_header.evt_title+'"/>'
			+'</td><td>'
			+'<input type="button" class="btn_day_header_save" value="SAVE"/>'
			+'<input type="button" class="btn_day_header_cancel" value="cancel"/>'
			+'<input type="button" class="btn_day_header_delete" value="DEL"/>'
			+'</td></tr>');
//alert(day_id);
	});
	
	//------------------------------------------------------------------
	$('body').on('click', '.btn_day_header_save', function(){

		var item_id = $(this).parent().parent().attr('data-evt_id');

		$.ajax({
			url: script_name+'?function=save_day_header&conf_id='+conf_id+'&evt_id='+item_id+'&'+$('#day_form').serialize(),
			dataType: 'json',
			success: function(response){
				
				if(response.status == 'ok')
				{
					draw_table(response);
				}
				else
				{
					alert('save error');
				}
			},
			error: function(response){
					alert('server error');
			}
		});
	});
	
	//------------------------------------------------------------------
	$('body').on('click', '.btn_day_header_delete', function(){

		var item_id = $(this).parent().parent().attr('data-evt_id');

		$.ajax({
			url: script_name+'?function=delete_day_header&conf_id='+conf_id+'&evt_id='+item_id,
			dataType: 'json',
			success: function(response){
				
				if(response.status == 'ok')
				{
					draw_table(response);
				}
				else
				{
					alert(response.errm);
				}
			},
			error: function(response){
					alert('server error');
			}
		});
	});
	
	//------------------------------------------------------------------
	$('body').on('click', '.btn_day_header_cancel', function(){
		
		close_edit_items();
	});

	//------------------------------------------------------------------
	function close_edit_items(){

		$('body .tr_edit').each(function(obj){
			var $tr = $(this);
	
			var evt_id = $tr.attr('data-evt_id');
			var day_id = $tr.attr('data-evt_upper_id');
		
			$tr.replaceWith(make_day_item(items[day_id][evt_id]));
		});
		
		
		$('body tr.tr_day_header_edit').each(function(){
			$(this).replaceWith( make_day_header($(this).attr('data-day_num'), $(this).attr('data-evt_id')) );
		});
	}

	//------------------------------------------------------------------
	function make_empty_item(param)
	{
		var item = {'new':true};
		
		for(var i in day_flds)
		{
			var fld = day_flds[i];
			var type = day_fields[fld].type;
			var new_val = day_fields[fld].new;
			
			if(new_val == 'db')
			{
				item[fld] = param[fld];
			}
			else if(new_val == 'day_beg_dt')
			{
				item[fld] = param.day_beg_dt;
			}
			else
				item[fld] = new_val;
		}

//alert(JSON.stringify(item));
		return item;
	}
	
	//------------------------------------------------------------------
	function make_day_item(item, param)
	{
		var mode = (typeof param != 'undefined' && typeof param.edit != 'undefined' && param.edit ? 'edit' : 'view');
	
		var new_flag = (typeof item.new != 'undefined' && item.new  ? true : false);
	
		var $tr = $('<tr data-evt_id="'+item.evt_id+'" data-evt_upper_id="'+item.evt_upper_id+'"></tr>');
		
		var day_id = item.evt_upper_id;

		for(var i in day_flds)
		{
			var fld = day_flds[i];
			var type = day_fields[fld].type;
			var val = item[fld];
			
			if(type == 'db')
			{	
				$tr.append(make_db_td(fld, val, mode, new_flag));
			}
			else if(type == 'button')
			{	
				$tr.append(make_button_td(mode, new_flag));
			}
			else if(type == 'hidden' && mode == 'edit')
			{
				$tr.append(make_hidden(fld, val));
			}
		}
		
		if(mode == 'edit') $tr.addClass('tr_edit');

		return $tr;
	}

	//----------------------------------------------------------------------
	function make_db_td(fld, val, mode, new_flag)
	{
		if(typeof day_fields[fld].td != 'undefined' && day_fields[fld].td == 'skip') return '';
		
		var td = $('<td class="day_table"></td>');
		
		if(mode == 'edit')
		{
			td.append('<input type="text" name="'+fld+'" value="'+val+'"/>');
		}
		else
		{
			td.addClass('td_click');

			if(new_flag)
				td.html('&nbsp;');
			else
				td.text(val);
		}
		
		return td;
	}

	//------------------------------------------------------------------
	$('body').on('click', '#btn_add_conf', function(){
		
		$('div#add_conf').html('<form id="add_conf_form">'
			+ 'дата: <input type="text" name="evt_beg_dt" value="<?= date("Y-m-d") ?>"/>&nbsp;&nbsp;&nbsp;'
			+ 'название: <input type="text" name="evt_title" value=""/>&nbsp;&nbsp;&nbsp;'
			+ '<input type="button" id="save_new_conf" value="SAVE"/>'
			+ '<input type="button" id="btn_add_conf_cancel" value="cancel"/>'
			+'</form>');
	});
	
	//------------------------------------------------------------------
	$('body').on('click', '#save_new_conf', function(){

		var item_id = $(this).parent().parent().attr('data-evt_id');

		$.ajax({
			url: script_name+'?function=add_conf&'+$('#add_conf_form').serialize(),
			dataType: 'json',
			success: function(response){
				
				if(response.status == 'ok')
				{
					draw_table(response);
		
					conf_id = response.new_conf_id;
					
					$('div#div_conf_dd').html(response.confs_dd.html);
		
					$('div#add_conf').html('<?= $add_conf_button ?>');
				}
				else
				{
					alert(response.errm);
				}
			},
			error: function(response){
					alert('server error');
			}
		});

		//alert(item_id);
	});
	
	//------------------------------------------------------------------
	$('body').on('click', '#delete_conf', function(){

		$.ajax({
			url: script_name+'?function=delete_conf&conf_id='+conf_id,
			dataType: 'json',
			success: function(response){
				
				if(response.status == 'ok')
				{
					draw_table(response);
		
					conf_id = response.conf_id;
					
					$('div#div_conf_dd').html(response.confs_dd.html);
		
					$('div#add_conf').html('<?= $add_conf_button ?>');
				}
				else
				{
					alert(response.errm);
				}
			},
			error: function(response){
					alert('server error');
			}
		});

		//alert(item_id);
	});
	
	//------------------------------------------------------------------
	$('body').on('click', '#btn_add_conf_cancel', function(){
		
		$('div#add_conf').html('<?= $add_conf_button ?>');
	});
	
});
//======================================================================

//----------------------------------------------------------------------
function make_hidden(fld, val)
{
	return $('<input type="hidden" name="'+fld+'" value="'+val+'"/>');
}

//----------------------------------------------------------------------
function make_button_td(mode, new_flag)
{
	var td = $('<td class="day_table"></td>');
	
	if(mode == 'edit')
		td.append('<input type="button" name="save" class="btn_save_item" value="SAVE"/>'
			+'<input type="button" name="cancel" class="btn_cancel_item" value="cancel"/>'
			+'<input type="button" name="delete" class="btn_delete_item" value="DEL"/>');
	else if(!new_flag)
		td.append('<input type="button" name="run" class="btn_run_item" value="RUN"/>');
	
	return td;
}

//----------------------------------------------------------------------
</script>

<style>
.fftd { padding:5px; }
.col1 { text-align:right; white-space:nowrap;}
.status_highlight { background-color:#ff7; }
</style>
    </head>


<body>

<h3 style="text-align: center;">FFmpeg control</h3>

<hr/>

<div id="div_conf_dd"><?= $conf_data['html'] ?></div><br/>

<div id="add_conf"><?= $add_conf_button ?></div><br/>

<input type="button" id="delete_conf" value="delete conference"/>

<h2 id="conf_title"></h2>

<form name="day_form" id="day_form" method="post">
<table class="day_table" id="day_table" width="70%">
</table>
</form>

<input type="button" id="btn_add_day" value="add new day"/>

</body>
</html>

<?php
//=======================================================================================================
function get_main_table($p_conf_id)
{
	global $dbh;
	
	if(!is_valid_id($p_conf_id)) return array('status'=>'fail', 'errm'=>'get_main_table() missing evt_id');

	//---- conference header -------
	$query = "SELECT
				evt_id,
				evt_title,
				evt_beg_dt
			FROM
				event WHERE evt_id =".$p_conf_id;

	if( !($result = $dbh->query($query)) || $result->num_rows == 0) return array('status'=>'fail', 'errm'=>'get_main_table() conf header retrieve error', 'sql'=>$query);
	
	$conf_header = $result->fetch_assoc();

	//------------------------------------------------------------------
	
	//---- day header -------
	$query = "SELECT
				evt_id,
				evt_title,
				evt_beg_dt
			FROM
				event WHERE evt_upper_id =".$p_conf_id." ORDER BY evt_beg_dt";

	if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'day header sql error', 'sql'=>$query);
	
	$main_data = array();

//return array('status'=>'ok');
	if($result->num_rows > 0)
	while($row = $result->fetch_assoc())
	{
		$day_id = $row['evt_id'];
		
		$day_data = get_day($day_id);
		
		if($day_data['status'] == 'ok')
		{
			//array_push($main_data, $day_data['day_data']);
			$main_data[$day_id]['day_header'] = $row;
			$main_data[$day_id]['day_data'] = $day_data['day_data'];
		}
	}
	
	$result->close();

	//$html = make_conf_table($main_data);

	return array('status'=>'ok', 'result'=>$main_data, 'conf_header'=>$conf_header); //, 'conf_table'=>$html);
}
//=======================================================================================================
function make_conf_table($p_data)
{
	global $day_item_template;
	
	if(!is_array($p_data) || count($p_data)==0) return '<table><tr><td>empty</td></tr></table>';

	$span = template_span($day_item_template);
	
	$day_cnt = 1;
	
	$html = '
<table class="day_table">';

	foreach($p_data as $day_id => $day)
	{
		$html .= '
  <tr><td class="day_table" colspan="'.$span.'" style="text-align:center;">day '.$day_cnt++.'</td></tr>';
  
		if(is_array($day) && count($day))
		{
			foreach($day as $item)
			{
				$html .= template_substitute($item, $day_item_template);
			}
		}

		$html .= '
  <tr><td class="day_table" colspan="'.$span.'"><input type="button" data-day-id="'.$day_id.'" class="btn_add_day_item" value="add item"</td></tr>';
	}
	
	$html .= '
</table>';

	return $html;
} 

//=======================================================================================================
function get_day($p_day_id)
{
	global $dbh;
	
	$query = "
	SELECT
		evt_id,
		evt_upper_id,
		evt_title,
		evt_subtitle,
		evt_abbr,
		evt_beg_dt,	
		-- DATE_ADD(evt_beg_dt, INTERVAL 1 DAY) AS beg_dt_next,
		evt_beg_tm,
		evt_end_dt,
		evt_end_tm
	FROM
		event
	WHERE
		evt_upper_id = ".$p_day_id."
	ORDER BY	
		evt_beg_dt, evt_beg_tm";


		
	//	INNER JOIN event_type ON(evt_evttp_id = evttp_id)
	//WHERE
	//	evttp_abbr LIKE 'day'";
	
	if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'sql error');
	
	$day_data = array();

	if($result->num_rows > 0)
	while($row = $result->fetch_assoc())
	{
		//$day_data[$row['evt_id']] = $row;
		array_push($day_data, $row);
	}
	
	$result->close();
	
	return array('status'=>'ok', 'day_data'=>$day_data);
}
//=======================================================================================================
function template_substitute($p_row, $p_template)
{
	$str = $p_template;
				
	foreach($p_row as $fld_name => $val)
	{
		$str = str_replace('__'.$fld_name.'__', $val, $str);
	}

	return $str;
}
//=======================================================================================================
function template_span($p_template)
{
	return preg_match_all('/<td/', $p_template);
}
//=======================================================================================================
function save_item()
{
	global $day_fields, $dbh;
	
	//if(!is_valid_req_id('evt_id')) return array('status'=>'fail', 'errm'=>'missing evt_id');
	
	if(is_valid_req_id('evt_id')) //------ edit -------
	{
		$query = "UPDATE event SET ";
		
		$updates = array();
		foreach($day_fields as $fld => $def)
		{
			if(isset($def['update']) && $def['update']) array_push($updates, $fld." = '".$_REQUEST[$fld]."'");
		}
		
		$query .= implode(', ', $updates) . " WHERE evt_id = ".$_REQUEST['evt_id'];
		
		if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'UPDATE error', 'sql'=>$query);
		
		$upd_id = $_REQUEST['evt_id'];
	}
	else //------- new ----------
	{
		$query = "SELECT evttp_id FROM event_type WHERE evttp_abbr LIKE 'part'";
	
		if( !($result = $dbh->query($query)) ) return  array('status'=>'fail', 'errm'=>'create_conf_day(): day type id retrieve error', 'sql'=>$query);
		
		$evttp_id = $result->fetch_assoc()['evttp_id'];
		$result->close();
	
		//------------------------------------------------------------------

		$query = "INSERT INTO event(";
		
		$flds = array();
		foreach($day_fields as $fld => $def)
		{
			if(isset($def['insert']) && $def['insert']) array_push($flds, $fld);
		}

		$query .= implode(', ', $flds) . ", evt_evttp_id) VALUES(";

		$vals = array();
		foreach($day_fields as $fld => $def)
		{
			if(isset($def['insert']) && $def['insert']) array_push($vals, "'".$_REQUEST[$fld]."'");
		}
		
		$query .= implode(', ', $vals) . ", ".$evttp_id.")";
		
		if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'insert error', 'sql'=>$query);
		
		$upd_id = $dbh->insert_id;
	}
	
	return array_merge( get_main_table($_REQUEST['conf_id']), array('upd_id'=>$upd_id, 'sql'=>$query) );
}

//=======================================================================================================
function get_confs_dd()
{
	$html = '
  <select id="conf_dd" name="evt_id">';
  
	$conf_data = get_conf_list();
	
	if(is_array($conf_data) && count($conf_data)>0)
	{
		$first_id = $conf_data[0]['evt_id'];
		
		foreach($conf_data as $row)
		{
			$html .= '
    <option value="'.$row['evt_id'].'">'.$row['evt_title'].'</option>';
		}
		
	}
	else
	{
		$first_id = 0;
		
		$html .= '
    <option value="0">&nbsp;</option>';
	}
	
	$html .= '
  </select>';

	return array('html'=>$html, 'first_id'=>$first_id);
}
//=======================================================================================================
function get_conf_list($p_evt_id=null)
{
	global $dbh;
	
	$query = "
	SELECT
		evt_id,
		evt_upper_id,
		evt_title,
		evt_subtitle,
		evt_abbr,
		evt_beg_dt,	
		evt_beg_tm,
		evt_end_dt,
		evt_end_tm
	FROM
		event
		INNER JOIN event_type ON(evt_evttp_id = evttp_id)
	WHERE
		evttp_abbr LIKE 'conf'";

	if(!is_null($p_evt_id)) $query .= " AND evt_id = ".$p_evt_id;
	
	$query .= "
	ORDER BY 	
		evt_stamp DESC";

	if( !($result = $dbh->query($query)) ) return null;
	
	$conf_data = array();

	if($result->num_rows > 0)
	while($row = $result->fetch_assoc())
	{
		//$day_data[$row['evt_id']] = $row;
		array_push($conf_data, $row);
	}
	
	$result->close();
	
	return $conf_data;
}
//=======================================================================================================
function create_conf_day()
{
	global $dbh;
	
	//------------------------------------------------------------------

	$query = "SELECT DATE_ADD(evt_beg_dt, INTERVAL 1 DAY) AS beg_dt FROM event WHERE evt_upper_id = ".$_REQUEST['conf_id']." ORDER BY evt_beg_dt DESC";

	if( !($result = $dbh->query($query)) ) return  array('status'=>'fail', 'errm'=>'create_conf_day() conference date retrieve error', 'sql'=>$query);
	
	if($result->num_rows > 0)
	{
		$evt_beg_dt = $result->fetch_assoc()['beg_dt'];
		$result->close();
	}
	else
	{
		$query = "SELECT evt_beg_dt FROM event WHERE evt_id = ".$_REQUEST['conf_id'];
	
		if( !($result = $dbh->query($query)) ) return  array('status'=>'fail', 'errm'=>'create_conf_day(): conference date retrieve error.', 'sql'=>$query);
		
		$evt_beg_dt = $result->fetch_assoc()['evt_beg_dt'];
		$result->close();
	}

	//------------------------------------------------------------------

	$query = "SELECT evttp_id FROM event_type WHERE evttp_abbr LIKE 'day'";

	if( !($result = $dbh->query($query)) ) return  array('status'=>'fail', 'errm'=>'create_conf_day(): day type id retrieve error', 'sql'=>$query);
	
	$evttp_id = $result->fetch_assoc()['evttp_id'];
	$result->close();

	//------------------------------------------------------------------
	
	$query = "INSERT INTO event(evt_upper_id, evt_title, evt_beg_dt, evt_evttp_id) VALUES(".$_REQUEST['conf_id'].", '', '".$evt_beg_dt."', ".$evttp_id.")";
	
	if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'create_conf_day error', 'sql'=>$query);
		
	return array_merge( get_main_table($_REQUEST['conf_id']), array('new_id'=>$dbh->insert_id, 'sql'=>$query) );
}

//=======================================================================================================
function delete_item()
{
	global $dbh;
	
	$query = "DELETE FROM event WHERE evt_id = ".$_REQUEST['evt_id'];
	
	if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'delete_item error', 'sql'=>$query);
		
	return get_main_table($_REQUEST['conf_id']);
}

//=======================================================================================================
function save_day_header()
{
	global $dbh;
	
	$query = "UPDATE
				event
			SET 
				evt_title = '".$_REQUEST['evt_title']."',
				evt_beg_dt = '".$_REQUEST['evt_beg_dt']."'
			WHERE
				evt_id = ".$_REQUEST['evt_id'];
	
	if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'save_day_header() UPDATE error', 'sql'=>$query);
	
	$upd_id = $_REQUEST['evt_id'];
	
	return array_merge( get_main_table($_REQUEST['conf_id']), array('upd_id'=>$upd_id, 'sql'=>$query) );
}

//=======================================================================================================
function delete_day_header()
{
	global $dbh;

	//------------------------------------------------------------------

	$query = "SELECT * FROM event WHERE evt_upper_id = ".$_REQUEST['evt_id'];;

	if( !($result = $dbh->query($query)) ) return  array('status'=>'fail', 'errm'=>'delete_day_header() count error', 'sql'=>$query);
	
	if($result->num_rows > 0) return  array('status'=>'fail', 'errm'=>'day not empty', 'sql'=>$query);
	
	$result->close();

	//------------------------------------------------------------------
	
	$query = "DELETE FROM event WHERE evt_id = ".$_REQUEST['evt_id'];
	
	if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'delete_day_header() error', 'sql'=>$query);
		
	return get_main_table($_REQUEST['conf_id']);
}

//=======================================================================================================
function add_conf()
{
	global $dbh;
	
	$query = "SELECT evttp_id FROM event_type WHERE evttp_abbr LIKE 'conf'";

	if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'add_conf() event type retrieve error', 'sql'=>$query);
	
	$evttp_id = $result->fetch_assoc()['evttp_id'];

	$result->close();
	
	//------------------------------------------------------------------
	
	$query = "INSERT INTO event(evt_title, evt_evttp_id, evt_beg_dt) VALUES('".$_REQUEST['evt_title']."', ".$evttp_id.", '".$_REQUEST['evt_beg_dt']."')";
	
	if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'add_conf() insert error', 'sql'=>$query);
		
		$new_id = $dbh->insert_id;
		
	return array_merge( get_main_table($new_id), array('new_conf_id'=>$new_id, 'confs_dd'=>get_confs_dd(), 'sql'=>$query) );
}
//=======================================================================================================
function delete_conf()
{
	global $dbh;

	//------------------------------------------------------------------

	$query = "SELECT * FROM event WHERE evt_upper_id = ".$_REQUEST['conf_id'];

	if( !($result = $dbh->query($query)) ) return  array('status'=>'fail', 'errm'=>'delete_conf() count error', 'sql'=>$query);
	
	if($result->num_rows > 0) return  array('status'=>'fail', 'errm'=>'conference not empty', 'sql'=>$query);
	
	$result->close();

	//------------------------------------------------------------------
	
	$query = "DELETE FROM event WHERE evt_id = ".$_REQUEST['conf_id'];
	
	if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'delete_conf() error', 'sql'=>$query);
		
	$confs_dd = get_confs_dd();
	
	$main_table = ( is_valid_id($confs_dd['first_id']) ? get_main_table($confs_dd['first_id']) : array('status'=>'ok') );
		
	return array_merge( $main_table, array('conf_id'=>$confs_dd['first_id'], 'confs_dd'=>$confs_dd, 'sql'=>$query) );
}

//=======================================================================================================
//=======================================================================================================
//=======================================================================================================
//=======================================================================================================
//=======================================================================================================
//=======================================================================================================
//=======================================================================================================
//=======================================================================================================
function is_valid_req_id($p_id_name)
{
	return isset($_REQUEST[$p_id_name])  &&  is_valid_id($_REQUEST[$p_id_name]);
}

//=======================================================================================================
function is_valid_id($p_id)
{
	return !is_null($p_id) && strlen($p_id)>0 && !preg_match('/\D/', $p_id) && (int)($p_id)>0;
}

//=======================================================================================================
function db_connect($p_pass)
{
	$rep_level = error_reporting();
	error_reporting(0);
	
	$mysqli = new mysqli('localhost', 'tslrussia', $p_pass, 'tsl_events');

	error_reporting($rep_level);

	if($mysqli->connect_errno)
	{
		return array('status'=>'fail', 'dbh'=>null, 'errm'=> "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
	}

	return array('status'=>'ok', 'dbh'=>$mysqli, 'errm'=> $mysqli->host_info);
}

//=======================================================================================================
//=======================================================================================================
?>
