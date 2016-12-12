<?php

$day_fields = array(
		'evt_upper_id' => array(
			'table'=>'event',
			'type' => 'hidden', 
			'td' => 'skip',
			'new' => 'db',
			'insert'=>true
		), 
		'evt_id' => array(
			'table'=>'event',
			'type' => 'hidden', 
			'td' => 'skip',
			'new' => 0
		), 
		'evt_beg_dt' => array(
			'table'=>'event',
			'type' => 'db', 
			'view' => 'show',
			'new' => 'day_beg_dt', 
			'edit' => 'input',
			'update'=>true,
			'insert'=>true
		), 
		'evt_beg_tm' => array(
			'table'=>'event',
			'type' => 'db', 
			'view' => 'show',
			'new' => '', 
			'edit' => 'input',
			'update'=>true,
			'insert'=>true
		), 
		'evt_title' => array(
			'table'=>'event',
			'type' => 'db', 
			'new' => '', 
			'edit' => 'input',
			'update'=>true,
			'insert'=>true
		),
		'evtmd_media_file' => array(
			'table'=>'event_media',
			'type' => 'db',
			'src' => 'media_list',
			 
			'new' => 'option', 
			'edit' => 'option',
			'update'=>true,
			'insert'=>true
		),
		'evtmd_duration' => array(
			'table'=>'event_media',
			'type' => 'db', 
			'src' => 'media_list',
			'new' => 'option', 
			'edit' => 'option',
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
		case 'save_conf':
			$response = save_conf();
		break;
		
		//------------------------------------------------------------------
		case 'delete_conf':
			$response = delete_conf();
		break;
		
		//------------------------------------------------------------------
		case 'run_event':
			$response = run_event();
		break;
		
		//------------------------------------------------------------------
		case 'get_media_list':
			$response = get_media_list();
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
$first_beg_dt = $conf_data['first_beg_dt'];
$date_now = $conf_data['date_now'];

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
        
        
        
   label, input { display:block; }
    input.text { margin-bottom:12px; width:95%; padding: .4em; }
    fieldset { padding:0; border:0; margin-top:25px; }
    h1 { font-size: 1.2em; margin: .6em 0; }
    div#users-contain { width: 350px; margin: 20px 0; }
    div#users-contain table { margin: 1em 0; border-collapse: collapse; width: 100%; }
    div#users-contain table td, div#users-contain table th { border: 1px solid #eee; padding: .6em 10px; text-align: left; }
    .ui-dialog .ui-state-error { padding: .3em; }
    .validateTips { border: 1px solid transparent; padding: 0.3em; }        
        
        </style>
		<head>
<link rel="stylesheet" href="css/jquery-ui.css">
<script src="js/jquery.js" type="text/javascript"></script>
<script src="js/jquery-ui.js" type="text/javascript"></script>

<script language="JavaScript">
$(function() {

	var script_name = '<?= basename($_SERVER["SCRIPT_FILENAME"]); ?>';
	
	var conf_id = <?= $first_conf_id ?>;
	var conf_beg_dt = '<?= $first_beg_dt ?>';
	var date_now = '<?= $date_now ?>';
	
	var day_fields = <?= json_encode($day_fields) ?>;
	
	var day_flds = Object.keys(day_fields);
	
	var day_span = 0;

	var confirm_function;
	
	for(var i in day_flds)
	{
		var fld = day_flds[i];
		
		if(day_fields[fld].td != 'skip') day_span++;
	}
	
	
	var items = [];
	var day_headers = [];
	//var running_evt_id;
	
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
	//function exec_request(href)
	//{
	//	refresh_status(href, 'waiting for response...', '#DCB220'); // сообщаем, что запрос отправлен
    //
	//	$.ajax({
	//		url: href,
	//		success: function(response){ refresh_status(href, response, 'grey'); },
	//		error: function(){ refresh_status(href, 'request failed', 'red'); }
	//	});
	//}

	//------ отображение информации в области служебной информации ------------------------------------------------
	//function refresh_status(href, text, color)
	//{
	//	var now = new Date();
    //
	//	$("#request_str").text(href); // строка запроса
	//	$("#status_str").html('<span style="color:'+color+';">'+ now.toString().substring(16) +'<br/>'+ text +'</span>'); // время отправки запроса
    //
	//	$(".status_cell").addClass('status_highlight'); // выделение цветом служебной области
	//	setTimeout(function() { $(".status_cell").removeClass('status_highlight'); }, 300); // снятие выделения цветом через таймаут
	//}
	
	//==================================================================
	
	//------------------------------------------------------------------
	dialog_info = $( "#info" ).dialog({
      autoOpen: false,
      height: 400,
      width: 500,
      modal: true,
      buttons: {
        "OK": function() {
          dialog_info.dialog( "close" );
        }
      }
    });
 
	//------------------------------------------------------------------
	dialog_confirm = $( "#confirm" ).dialog({
      autoOpen: false,
      height: 400,
      width: 500,
      modal: true,
      buttons: {
        "OK": function() {
			dialog_confirm.dialog( "close" );
			confirm_callback(true);
		},
        "Cancel": function() {
			dialog_confirm.dialog( "close" );
			confirm_callback(false);
		}
      }
    });
 
	function confirm_callback(value){
		if(value) confirm_function();
    }
	//------------------------------------------------------------------
	dialog_conf = $( "#form_conf" ).dialog({
      autoOpen: false,
      height: 500,
      width: 700,
      modal: true,
      buttons: {
        "Save": save_conf,
        Cancel: function() {
          dialog_conf.dialog( "close" );
        },
        'Delete': delete_conf
      },
      close: function() {
        dialog_conf.find( "form" )[0].reset();
      }
    });
 
    form_conf = dialog_conf.find( "form" );
    
	//------------------------------------------------------------------
	dialog_day = $( "#form_day" ).dialog({
      autoOpen: false,
      height: 500,
      width: 700,
      modal: true,
      buttons: {
        "Save": save_day_header,
        Cancel: function() {
          dialog_day.dialog( "close" );
        },
        'Delete': delete_day_header
      },
      close: function() {
        dialog_day.find( "form" )[0].reset();
      }
    });
 
    form_day = dialog_day.find( "form" );
    
	//------------------------------------------------------------------
	dialog_item = $( "#form_item" ).dialog({
      autoOpen: false,
      height: 500,
      width: 700,
      modal: true,
      buttons: {
        "Save": save_item,
        Cancel: function() {
          dialog_item.dialog( "close" );
        },
        'Delete': delete_item
      },
      close: function() {
        dialog_item.find( "form" )[0].reset();
      }
    });
 
    form_item = dialog_item.find( "form" );
    
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
		conf_beg_dt = $(this).find('option:selected').attr('data-evt_beg_dt');
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
							
							item.day_num = day_num;
							
							items[day_id][item.evt_id] = item;
							
							var $tr = make_day_item(item);
							
							append_table($tr);
							
							
							//if(item.evt_id == running_evt_id) $tr.addClass('running');
							if(item.evtmd_run_flag == 1) $tr.addClass('running');
						}
					}
	
					var empty_item = make_empty_item({"evt_upper_id":day_id, 'day_beg_dt':day_header.evt_beg_dt, 'day_num':day_num});
					
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

		//close_edit_items();

		var $tr = $(this).parent('tr');

		var evt_id = $tr.attr('data-evt_id');
		var day_id = $tr.attr('data-evt_upper_id');
		var day_num = $tr.attr('data-day_num');
		
		var item = items[day_id];
//alert(JSON.stringify(item));
		var media_list = '';
        
		$.ajax({
			url: script_name+'?function=get_media_list&conf_id='+conf_id+'&day_num='+day_num,
			dataType: 'json',
			success: function(response){
				//alert(JSON.stringify(response));
		
				form_item.find('select').html(response.options_html);

				form_item.find('input[name="evt_upper_id"]').val(item[evt_id].evt_upper_id);
				form_item.find('input[name="evt_beg_dt"]').val(item[evt_id].evt_beg_dt);
				
				if(evt_id == 0)
				{
					form_item.find('input[name="evt_id"]').val(0);
					form_item.find('input[name="evt_beg_tm"]').val('00:00');
				}
				else
				{
					form_item.find('input[name="evt_id"]').val(evt_id);
//alert(item[0].evt_beg_tm);
					form_item.find('input[name="evt_beg_tm"]').val(item[evt_id].evt_beg_tm);
					
					form_item.find('input[name="evt_title"]').val(item[evt_id].evt_title);
					
					form_item.find('select option').each(function(){
						if($(this).val() == item[evt_id].evtmd_media_file) $(this).prop('selected', true);
					});
				}
				
				dialog_item.dialog('open');
			}
		});
        
		//$tr.replaceWith(make_day_item(items[day_id][evt_id], {'edit':true, 'new':(evt_id == 0 ? true : false), 'media_list':media_list}));
	});
	
	//------------------------------------------------------------------
	$('body').on('click', '.btn_run_item', function(){
		
		var $tr = $(this).parent().parent();
		
		var evt_id = $tr.attr('data-evt_id');
		
		$.ajax({
			url: script_name+'?function=run_event&conf_id='+conf_id + '&evt_id='+evt_id,
			dataType: 'json',
			success: function(response){
				
				if(response.status == 'ok')
				{
					$("#day_table tr").removeClass('running');
					
					$tr.children().removeClass('updated');

					$tr.removeClass('updated').addClass('running');
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
	//$('body').on('click', '.btn_save_item', function(){
	
	function save_item()
	{
		var duration = form_item.find('option:selected').attr('data-duration');

		$.ajax({
			url: script_name+'?function=save_item&conf_id='+conf_id + '&evtmd_duration='+duration + '&'+form_item.serialize(),
			dataType: 'json',
			success: function(response){
				
				if(response.status == 'ok')
				{
					draw_table(response);

					if( ! $('#day_table tr[data-evt_id='+response.upd_id+']').hasClass('running') )
					{
						$('#day_table tr[data-evt_id='+response.upd_id+']').children('td').addClass('updated');
					}
					
					dialog_item.dialog('close');
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
	}
	
	//------------------------------------------------------------------
	//$('body').on('click', '.btn_delete_item', function(){
	function delete_item()
	{
		confirm_function = delete_item_confirmed;
		
		dialog_confirm.dialog('open');
	}
			
	function delete_item_confirmed()
	{
		var item_id = form_item.find('input[name="evt_id"]').val();
		
		//var item_id = $(this).parent().parent().attr('data-evt_id');

		$.ajax({
			url: script_name+'?function=delete_item&conf_id='+conf_id+'&evt_id='+item_id,
			dataType: 'json',
			success: function(response){
				
				if(response.status == 'ok')
				{
					draw_table(response);

					dialog_item.dialog('close');
				}
				else
				{
					//alert(response.errm);
					dialog_info.find('p').text(response.errm);
					dialog_info.dialog( "open" );
				}
			},
			error: function(response){
					alert('server error');
			}
		});

		//alert(item_id);
	}
	
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
	function make_day_header(day_num, day_id, day_beg_dt)
	{
		var day_header = day_headers[day_id];
		var title = 'День '+day_num;
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

		var day_id = $(this).attr('data-evt_id');
		var day_header = day_headers[day_id];
		
		form_day.find('input[name=evt_id]').val( day_id );
		form_day.find('input[name=evt_beg_dt]').val( day_header.evt_beg_dt );
		form_day.find('input[name=evt_title]').val( day_header.evt_title );
		
		dialog_day.dialog('open');
		
		//$tr.replaceWith('<tr class="tr_day_header_edit day_header" data-evt_id='+day_id+' data-day_num='+day_num+'><td colspan="'+(day_span*1-1)+'">'
		//	+'<input type="text" name="evt_beg_dt" value="'+day_header.evt_beg_dt+'"/>'
		//	+'<input type="text" name="evt_title" value="'+day_header.evt_title+'"/>'
		//	+'</td><td>'
		//	+'<input type="button" class="btn_day_header_save" value="SAVE"/>'
		//	+'<input type="button" class="btn_day_header_cancel" value="cancel"/>'
		//	+'<input type="button" class="btn_day_header_delete" value="DEL"/>'
		//	+'</td></tr>');
	});
	
	//------------------------------------------------------------------
	//$('body').on('click', '.btn_day_header_save', function(){
	function save_day_header()
	{

		//var item_id = $(this).parent().parent().attr('data-evt_id');

		$.ajax({
			url: script_name+'?function=save_day_header&conf_id='+conf_id+'&'+form_day.serialize(),
			dataType: 'json',
			success: function(response){
				
				if(response.status == 'ok')
				{
					draw_table(response);
					dialog_day.dialog('close');
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
	}
	
	//------------------------------------------------------------------
	//$('body').on('click', '.btn_day_header_delete', function(){
	function delete_day_header()
	{

		var item_id = form_day.find('input[name="evt_id"]').val();

		$.ajax({
			url: script_name+'?function=delete_day_header&conf_id='+conf_id+'&evt_id='+item_id,
			dataType: 'json',
			success: function(response){
				
				if(response.status == 'ok')
				{
					draw_table(response);
					dialog_day.dialog('close');
				}
				else
				{
					//alert(response.errm);
					dialog_info.find('p').text(response.errm);
					dialog_info.dialog( "open" );
				}
			},
			error: function(response){
					alert('server error');
			}
		});
	}
	
	///------------------------------------------------------------------
	//$('body').on('click', '.btn_day_header_cancel', function(){
	//	
	//	close_edit_items();
	//});

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
		var item = {'new':true, 'day_num':param.day_num};
		
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
	
		var $tr = $('<tr data-evt_id="'+item.evt_id+'" data-evt_upper_id="'+item.evt_upper_id+'" data-day_num="'+item.day_num+'"></tr>');
		
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
			//else if(type == 'option')
			//{
			//	$tr.append(make_select(param.media_list));
			//}
		}
		
		if(mode == 'edit') $tr.addClass('tr_edit');

		return $tr;
	}

	//------------------------------------------------------------------
	//function make_day_item_tr(item, param)
	//{
	//	var tr = '<tr data-evt_id="'+item.evt_id+'" data-evt_upper_id="'+item.evt_upper_id+'" data-day_num="'+item.day_num+'">';
	//	
	//	if(mode == 'new')
	//	{
	//		tr += '<td class=="day_table"';
	//	}
	//	else if(mode == 'edit')
	//	{
	//		
	//	}
	//	else
	//	{
	//		
	//	}
	//	
	//	tr += '</tr>';
	//}
	
	//----------------------------------------------------------------------
	//function make_select(media_list)
	//{
	//	var td = $('<td class="day_table">'+media_list+'</td>');
	//
	//	return td;
	//}
	
	//----------------------------------------------------------------------
	function make_db_td(fld, val, mode, new_flag)
	{
		if(typeof day_fields[fld].td != 'undefined' && day_fields[fld].td == 'skip') return '';
		
		edit_val_type = '';
		if(typeof day_fields[fld].edit != 'undefined') var edit_val_type = day_fields[fld].edit;
		
		new_val_type = ''
		if(typeof day_fields[fld].new != 'undefined') var new_val_type = day_fields[fld].new;
		
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
		
		
		form_conf.find('input[name="evt_id"]').val(0);
		form_conf.find('input[name="evt_beg_dt"]').val(date_now);
		
		dialog_conf.dialog('open');
		
		//$('div#add_conf').html('<form id="add_conf_form">'
		//	+ 'дата: <input type="text" name="evt_beg_dt" value="<?= date("Y-m-d") ?>"/>&nbsp;&nbsp;&nbsp;'
		//	+ 'название: <input type="text" name="evt_title" value=""/>&nbsp;&nbsp;&nbsp;'
		//	+ '<input type="button" id="save_new_conf" value="SAVE"/>'
		//	+ '<input type="button" id="btn_ю_cancel" value="cancel"/>'
		//	+'</form>');
	});
	
	//------------------------------------------------------------------
	//$('body').on('click', '#save_new_conf', function(){
	function save_conf()
	{

		//var item_id = $(this).parent().parent().attr('data-evt_id');

		$.ajax({
			url: script_name+'?function=save_conf&'+form_conf.serialize(),
			dataType: 'json',
			success: function(response){
				
				if(response.status == 'ok')
				{
					draw_table(response);
					conf_id = response.new_conf_id;
					conf_beg_dt = response.new_beg_dt;
					$('div#div_conf_dd').html(response.confs_dd.html);
					dialog_conf.dialog('close');
				}
				else
				{
					//alert(response.errm);
					dialog_info.find('p').text(response.errm);
					dialog_info.dialog( "open" );
				}
			},
			error: function(response){
					alert('server error');
			}
		});

		//alert(item_id);
	}
	
	//------------------------------------------------------------------
	//$('body').on('click', '#delete_conf', function(){
	function delete_conf()
	{

		$.ajax({
			url: script_name+'?function=delete_conf&conf_id='+conf_id,
			dataType: 'json',
			success: function(response){
				
				if(response.status == 'ok')
				{
					draw_table(response);
		
					conf_id = response.conf_id;
					
					$('div#div_conf_dd').html(response.confs_dd.html);
					
					dialog_conf.dialog( "close" );
				}
				else
				{
					//alert(response.errm);
					dialog_info.find('p').text(response.errm);
					dialog_info.dialog( "open" );
				}
			},
			error: function(response){
					alert('server error');
			}
		});

		//alert(item_id);
	}
	
	//------------------------------------------------------------------
	$('body').on('click', '#conf_title', function(){
		
		form_conf.find('input[name="evt_id"]').val(conf_id);
		form_conf.find('input[name="evt_beg_dt"]').val(conf_beg_dt);
		form_conf.find('input[name="evt_title"]').val($(this).find('h2').text());

		dialog_conf.dialog('open');
	});
	
	//----------------------------------------------------------------
	$('.btn_test').click(function(){

		var name_fun = $(this).attr('name');
		
		confirm_function = window[name_fun];
        
        dialog_confirm.dialog( "open" );
	});
});
//======================================================================

//---------------------------------------------------------------------
function test1()
{
	alert('test #1');
}

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

<div id="add_conf"><input type="button" id="btn_add_conf" value="add conference"/></div><br/>

<!-- input type="button" class="btn_test" name="test1" id="btn_test1" value="test1"/>
<input type="button" class="btn_test" name="test2" id="btn_test2" value="test2"/ -->

<div id="conf_title"><h2></h2></div>

<form name="day_form" id="day_form" method="post">
<table class="day_table" id="day_table" width="70%">
</table>
</form>

<div id="info" title="info">
<p></p>
</div>

<div id="confirm" title="">
<p>Подтвердите действие</p>
</div>

<div id="form_conf" title="редактирование заголовка конференции">
  <form>
    <fieldset>
      <input type="hidden" name="evt_id" id="evt_id"/>

      <label for="evt_beg_dt">Дата начала</label>
      <input type="text" name="evt_beg_dt" id="evt_beg_dt" class="text ui-widget-content ui-corner-all"/>
      
      <label for="evt_title">Название</label>
      <input type="text" name="evt_title" id="evt_title" class="text ui-widget-content ui-corner-all"/>
      
      <!-- Allow form submission with keyboard without duplicating the dialog button -->
      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px"/>
    </fieldset>
  </form>
</div>

<div id="form_day" title="редактирование заголовока дня">
  <!-- p class="validateTips">торопышка был голодный.</p -->
 
  <form>
    <fieldset>
      <input type="hidden" name="evt_upper_id" id="evt_upper_id"/>
      <input type="hidden" name="evt_id" id="evt_id"/>

      <label for="evt_beg_dt">Дата начала</label>
      <input type="text" name="evt_beg_dt" id="evt_beg_dt" class="text ui-widget-content ui-corner-all"/>
      
      <label for="evt_title">Название</label>
      <input type="text" name="evt_title" id="evt_title" class="text ui-widget-content ui-corner-all"/>
      
      <!-- Allow form submission with keyboard without duplicating the dialog button -->
      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px"/>
    </fieldset>
  </form>
</div>

<div id="form_item" title="редактирование события">
  <!-- p class="validateTips">проглотил утюг холодный.</p -->
 
  <form>
    <fieldset>
      <input type="hidden" name="evt_upper_id" id="evt_upper_id"/>
      <input type="hidden" name="evt_id" id="evt_id"/>

      <label for="evt_beg_dt">Дата начала</label>
      <input type="text" name="evt_beg_dt" id="evt_beg_dt" class="text ui-widget-content ui-corner-all"/>
      
      <label for="evt_beg_tm">Время начала</label>
      <input type="text" name="evt_beg_tm" id="evt_beg_tm" class="text ui-widget-content ui-corner-all"/>
      
      <label for="evt_title">Название</label>
      <input type="text" name="evt_title" id="evt_title" class="text ui-widget-content ui-corner-all"/>
 
      <label for="evtmd_media_file">Файл</label>
      <select name="evtmd_media_file" id="evtmd_media_file"></select>
      
      <!-- Allow form submission with keyboard without duplicating the dialog button -->
      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px"/>
    </fieldset>
  </form>
</div>

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
		evt_end_tm,
		evtmd_media_file,
		evtmd_duration,
		evtmd_run_flag
	FROM
		event
		LEFT JOIN event_media ON(evtmd_evt_id = evt_id)
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
			if(isset($def['table']) && $def['table']=='event' && isset($def['update']) && $def['update']) array_push($updates, $fld." = '".$_REQUEST[$fld]."'");
		}
		
		$query .= implode(', ', $updates) . " WHERE evt_id = ".$_REQUEST['evt_id'];
		
		if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'save_item(): event UPDATE error', 'sql'=>$query);
		
		$upd_id = $_REQUEST['evt_id'];

		//------------------------------------------------------------------
		$query = "DELETE FROM event_media WHERE evtmd_evt_id = ".$_REQUEST['evt_id'];
		
		if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'save_item(): event_media DELETE error', 'sql'=>$query);

		//-------------------------------------------------
		//$query = "UPDATE event_media SET ";
		//
		//$updates = array();
		//foreach($day_fields as $fld => $def)
		//{
		//	if(isset($def['table']) && $def['table']=='event_media' && isset($def['update']) && $def['update']) array_push($updates, $fld." = '".$_REQUEST[$fld]."'");
		//}
		//
		//$query .= implode(', ', $updates) . " WHERE evtmd_evt_id = ".$_REQUEST['evt_id'];
		//
		//if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'save_item(): event_media UPDATE error', 'sql'=>$query);
		
		//$upd_id = $_REQUEST['evt_id'];
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
			if(isset($def['table']) && $def['table']=='event' && isset($def['insert']) && $def['insert']) array_push($flds, $fld);
		}

		$query .= implode(', ', $flds) . ", evt_evttp_id) VALUES(";

		$vals = array();
		foreach($day_fields as $fld => $def)
		{
			if(isset($def['table']) && $def['table']=='event' && isset($def['insert']) && $def['insert']) array_push($vals, "'".$_REQUEST[$fld]."'");
		}
		
		$query .= implode(', ', $vals) . ", ".$evttp_id.")";
		
		if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'save_item(): event INSERT error', 'sql'=>$query);
		
		$upd_id = $dbh->insert_id;
		//------------------------------------------------------------------

		
		//$upd_id = $dbh->insert_id;
	}
	
	$query = "INSERT INTO event_media(";
	
	$flds = array();
	foreach($day_fields as $fld => $def)
	{
		if(isset($def['table']) && $def['table']=='event_media' && isset($def['insert']) && $def['insert']) array_push($flds, $fld);
	}

	$query .= implode(', ', $flds) . ", evtmd_evt_id) VALUES(";

	$vals = array();
	foreach($day_fields as $fld => $def)
	{
		if(isset($def['table']) && $def['table']=='event_media' && isset($def['insert']) && $def['insert']) array_push($vals, "'".$_REQUEST[$fld]."'");
	}
	
	$query .= implode(', ', $vals) . ", ".$upd_id.")";
	
	if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'save_item(): event_media INSERT error', 'sql'=>$query);

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
		$first_beg_dt = $conf_data[0]['evt_beg_dt'];
		
		foreach($conf_data as $row)
		{
			$html .= '
    <option data-evt_beg_dt="'.$row['evt_beg_dt'].'" value="'.$row['evt_id'].'">'.$row['evt_title'].'</option>';
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

	return array('html'=>$html, 'first_id'=>$first_id, 'first_beg_dt'=>$first_beg_dt, 'date_now'=>date("Y-m-d"));
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
	
	//------------------------------------------------------------------

	$query = "SELECT evtmd_run_flag FROM event_media WHERE evtmd_evt_id = ".$_REQUEST['evt_id'];

	if( !($result = $dbh->query($query)) ) return  array('status'=>'fail', 'errm'=>'delete_item(): cannot determine run status', 'sql'=>$query);
	
	if( $result->fetch_assoc()['evtmd_run_flag'] == '1' ) return  array('status'=>'fail', 'errm'=>'нельзя удалить активное событие');
	$result->close();

	//------------------------------------------------------------------
	$query = "DELETE FROM event_media WHERE evtmd_evt_id = ".$_REQUEST['evt_id'];
	
	if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'delete_item(): event_media DELETE error', 'sql'=>$query);
		
	//------------------------------------------------------------------
	$query = "DELETE FROM event WHERE evt_id = ".$_REQUEST['evt_id'];
	
	if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'delete_item(): event DELETE error', 'sql'=>$query);
		
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
	
	if($result->num_rows > 0) return  array('status'=>'fail', 'errm'=>'нельзя удалить: есть содержимое', 'sql'=>$query);
	
	$result->close();

	//------------------------------------------------------------------
	
	$query = "DELETE FROM event WHERE evt_id = ".$_REQUEST['evt_id'];
	
	if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'delete_day_header() error', 'sql'=>$query);
		
	return get_main_table($_REQUEST['conf_id']);
}

//=======================================================================================================
function save_conf()
{
	global $dbh;
	
	$query = "SELECT evttp_id FROM event_type WHERE evttp_abbr LIKE 'conf'";

	if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'add_conf() event type retrieve error', 'sql'=>$query);
	
	$evttp_id = $result->fetch_assoc()['evttp_id'];

	$result->close();
	
	if(!preg_match('/\S/', $_REQUEST['evt_title']))  return array('status'=>'fail', 'errm'=>'необходимо задать название');
		
	if(is_valid_req_id('evt_id'))
	{
		//------------------------------------------------------------------
		$query = "UPDATE
					event
				SET 
					evt_title  = '".$_REQUEST['evt_title']."',
					evt_beg_dt = '".$_REQUEST['evt_beg_dt']."'
				WHERE
					evt_id = ".$_REQUEST['evt_id'];
		
		if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'add_conf(): event UPDATE error', 'sql'=>$query);
		
		$new_id = $_REQUEST['evt_id'];
	}
	else
	{
		//------------------------------------------------------------------
		
		$query = "INSERT INTO event(evt_title, evt_evttp_id, evt_beg_dt) VALUES('".$_REQUEST['evt_title']."', ".$evttp_id.", '".$_REQUEST['evt_beg_dt']."')";
		
		if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'add_conf(): event INSERT error', 'sql'=>$query);
			
			$new_id = $dbh->insert_id;
	}		
		
	return array_merge( get_main_table($new_id), array('new_conf_id'=>$new_id, 'new_beg_dt'=>$_REQUEST['evt_beg_dt'], 'confs_dd'=>get_confs_dd(), 'sql'=>$query) );
}
//=======================================================================================================
function delete_conf()
{
	global $dbh;

	//------------------------------------------------------------------

	$query = "SELECT * FROM event WHERE evt_upper_id = ".$_REQUEST['conf_id'];

	if( !($result = $dbh->query($query)) ) return  array('status'=>'fail', 'errm'=>'delete_conf() count error', 'sql'=>$query);
	
	if($result->num_rows > 0) return  array('status'=>'fail', 'errm'=>'нельзя удалить: есть содержимое', 'sql'=>$query);
	
	$result->close();

	//------------------------------------------------------------------
	
	$query = "DELETE FROM event WHERE evt_id = ".$_REQUEST['conf_id'];
	
	if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'delete_conf() error', 'sql'=>$query);
		
	$confs_dd = get_confs_dd();
	
	$main_table = ( is_valid_id($confs_dd['first_id']) ? get_main_table($confs_dd['first_id']) : array('status'=>'ok') );
		
	return array_merge( $main_table, array('conf_id'=>$confs_dd['first_id'], 'confs_dd'=>$confs_dd, 'sql'=>$query) );
}

//=======================================================================================================
function run_event()
{
	global $dbh;
	
	//------------------------------------------------------------------
	//$query = "SELECT * FROM event WHERE evt_upper_id = ".$_REQUEST['conf_id'];
    //
	//if( !($result = $dbh->query($query)) ) return  array('status'=>'fail', 'errm'=>'delete_conf() count error', 'sql'=>$query);
	//
	//if($result->num_rows > 0) return  array('status'=>'fail', 'errm'=>'conference not empty', 'sql'=>$query);
	//
	//$result->close();

	//------------------------------------------------------------------
	$query = "UPDATE
				event_media
			SET 
				evtmd_run_flag = -1
			WHERE
				evtmd_run_flag = 1";
	
	if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'run_event(): reset run flags error', 'sql'=>$query);
	
	//------------------------------------------------------------------
	$query = "UPDATE
				event_media
			SET 
				evtmd_run_flag = 1
			WHERE
				evtmd_evt_id = ".$_REQUEST['evt_id'];
	
	if( !($result = $dbh->query($query)) ) return array('status'=>'fail', 'errm'=>'run_event(): set run flag error', 'sql'=>$query);
	
	return get_main_table($_REQUEST['conf_id']);

	
}
//=======================================================================================================
function get_media_list()
{
	if(!is_valid_req_id('day_num')) return array('status'=>'fail', 'errm'=>'get_media_list(): missing day_num');
	
	$dir = '/home/ftp/Archive/Wowza/TSLconferencevideo/'.$_REQUEST['day_num'].'drus';

	if ($handle = @opendir($dir))
	{
		$media_list = array(); $num = 1;
		
		while (false !== ($entry = readdir($handle)))
		{
			$ffmpeg = '';
			
			if ($entry != "." && $entry != ".." && !is_dir("$dir/$entry"))
			{
				$entry_norm = preg_replace('/\s/', '\ ', $entry);
				$ffmpeg = `ffmpeg -i $dir/$entry_norm 2>&1`;
				
				if(preg_match('/Duration: (\d+?:\d\d:\d\d)\.\d/m', $ffmpeg, $match))
				{
					
					if($match[1] == '00:00:00')
						continue;
					else
						array_push($media_list, array('file'=>$entry, 'duration'=>$match[1], 'num'=>$num++));
				}
			}
		}
		closedir($handle);
	}
	else
		return array('status'=>'fail', 'errm'=>"get_media_list(): cannot opendir $dir");
		
	$html = '<option data-duration="00:00:00" value="">&nbsp;</option>';
	if(count($media_list)>0)
	foreach($media_list as $row)
	{
		$html .= '<option data-duration="'.$row['duration'].'" value="'.$row['file'].'">'.$row['duration'].'&nbsp;&nbsp;|&nbsp;&nbsp;'.$row['file'].'</option>';
	}
	
	return array('status'=>'ok', 'media_list'=>$media_list, 'options_html'=>$html);
}
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
