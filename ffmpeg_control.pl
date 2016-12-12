#!/usr/bin/env perl

#===================================================================================================
$smil_file = "/home/ftp/Archive/Wowza/AUDIOrus/streamschedule.smil"; # файл настроек вовзы
#$smil_file = "/home/ftp/Archive/Wowza/AUDIOrus/streamschedule.smil"; # файл настроек вовзы

$clear_params_on_stop = 0;
#===================================================================================================

use POSIX qw(strftime);
use Time::HiRes;
use IPC::Open3;

$| = 1; # режим немедленного вывода буфера

local(*CHILD_IN); # описываем дескриптор ввода для скармливания функции open3()

print STDERR "\nwaiting for command fifo...\n"; # печать диагностики

$FIFO="/var/tmp/ffmpeg_fifo"; # имя канала обмена
until(-p $FIFO){ sleep 1 }; # ждем, пока пхп создаст канал
open (FIFO, "< $FIFO")  || die "can't open $FIFO: $!"; # открываем канал и ждем, пока пхп туда что-нибудь запишет

print STDERR "command fifo opened"; # печать диагностики

# $SIG{INT} = sub{ close(FIFO); exit; }; # неуклюжая попытка перехвата прерывания

#-----------------------------------------------------------------------------------------

while(1) # бесконечный цикл чтения команд из канала
{
	while($param_str = <FIFO>) # если пришла очередная команда
	{
		my $action = get_val('action', $param_str); # действие
		my $switch = get_val('switch', $param_str); # переключатель команды

		print STDERR "\n\naction = ".$action."\nswitch = ".$switch."\n"; # печать диагностики

		if($action eq 'run') # действие - запуск команды
		{
			# значения оффсетов
			my $offset_video = get_val('offset_video_mixmodule', $param_str);
			my $offset_audio = normalize_time( get_val('offset_audio_mixmodule', $param_str) );
			my $offset_1 = normalize_time( get_val('offset_1_mix', $param_str) );
			my $offset_2 = normalize_time( get_val('offset_2_mix', $param_str) );

			print STDERR "\noffset video AVmixmodule  = ".$offset_video."\noffset audio AVmixmodule  = ".$offset_audio."\noffset 1 video mix ffmpeg = ".$offset_1."\noffset 2 video mix ffmpeg = ".$offset_2."\n"; # печать диагностики

			##################################################################################################################################################################################

			$cmd{video_mixmodule} =	"ffmpeg_1 -thr1ead_queue_size 3048 -itsoffset $offset_video -i rt1mp://185.60.135.19:1935/Meditation3/myStream -vcodec libx264 -vf scale=\"iw*sar*min(1280/(iw*sar)\\,720/ih):ih*min(1280/(iw*sar)\\,720/ih),pad=1280:720:(ow-iw)/2:(oh-ih)/2\" -force_key_frames \"expr:gte(t,n_forced*2)\" -sc_threshold 0 -maxrate 1326k -bufsize 1326k -vprofile high -pix_fmt yuv420p -c:a aac -g 60 -keyint_min 60 -b:a 128k -threads 0 -f flv rtmp://185.60.135.19:1935/Meditation4/myStream 2> /home/webtslr/tslrussia.org/www/player/Logs/videodayDATE.txt";

			$cmd{audio_mixmodule} = "ffmpeg_2 -thread_queue_size 3048 -itsoffset $offset_1 -i mmsh://broadcast.TheSummitLighthouse.org/Virya -thread_queue_size 3048 -itsoffset $offset_2 -i rtmp://185.60.135.19:1935/AUDIOrus/rustranslate -map 0:1 -map 1:a -r 30 -s 768x432 -vcodec libx264 -preset veryfast -maxrate 480k -bufsize 480k -vprofile high -pix_fmt yuv420p -c:a aac -g 60 -keyint_min 60 -b:a 92k -threads 0 -f flv rtmp://185.60.135.19:1935/TSLvideorus".$offset_video."d/myStream_360p 2> /home/webtslr/tslrussia.org/www/player/Logs/videorus_360pDATE.txt";

            $cmd{mix} = "/home/ftp/IT/ffmpegcontrol/ffmpeg_3.sh $offset_1 $offset_2 $offset_video";		
			
			$cmd{english_video} =	"ffmpeg_4 -thread_queue_size 3048 -i mmsh://broadcast.TheSummitLighthouse.org/Virya -r 30 -s 1280x720 -vcodec libx264 -preset veryfast -maxrate 1326k -bufsize 1326k -vprofile high -pix_fmt yuv420p -c:a aac -g 60 -keyint_min 60 -b:a 128k -threads 0 -f flv rtmp://185.60.135.19:1935/TSLvideoeng".$offset_video."d/myStream 2> /home/webtslr/tslrussia.org/www/player/Logs/videoengDATE.txt";

			$cmd{audio_rus} =	"ffmpeg_5 -thread_queue_size 3048 -i mmsh://broadcast.TheSummitLighthouse.org/Russkiy -c:a aac -vn -f flv rtmp://185.60.135.19:1935/AUDIOrus/montana";

			$cmd{save_orig_video} =	"ffmpeg_6 -thread_queue_size 3048 -i mmsh://broadcast.TheSummitLighthouse.org/Virya -thread_queue_size 2048 -itsoffset 00:00:20 -i mmsh://broadcast.TheSummitLighthouse.org/Russkiy -map 0:1 -map 1:a -c copy /home/ftp/Archive/Wowza/TSLconferencevideo/ASFrecord/Harvest2016/videorus88DATE.asf -map 0 -c copy /home/ftp/Archive/Wowza/TSLconferencevideo/ASFrecord/Harvest2016/videoeng88DATE.asf -map 1:a -c copy /home/ftp/Archive/Wowza/TSLconferencevideo/ASFrecord/Harvest2016/audiorus88DATE.asf";

			$cmd{save_orig_audio} =	"ffmpeg_7 -thread_queue_size 2048 -i mmsh://broadcast.TheSummitLighthouse.org/Russkiy -c copy /home/ftp/Archive/Wowza/TSLconferencevideo/ASFrecord/Harvest2016/audiorusrecordDATE.asf";


			##################################################################################################################################################################################

			print STDERR "\ncmd = {".$cmd{$switch}."}\n"; # печать диагностики

			stop_pl_child($switch); # убиваем perl-потомка
			stop_ffmpeg($switch); # убиваем процесс ffmpeg

			if(length($cmd{$switch})>0 && defined($pid_pl{$switch} = fork)) # если команда выбрана и если успешно выполнен fork. Запоминаем pid потомка
			{
				if($pid_pl{$switch} == 0) # выполняется для потомка
				{
					#while(1) # бесконечный цикл перезапуска ffmpeg
					#{
						my $param = $cmd{$switch}; # копируем команду во временную переменную
						my $date = strftime "%Y%m%d_%H%M%S", localtime; # формируем текущую дату в требуемом формате
						$param =~ s/DATE/$date/g; # в строке команды заменяем подстроку DATE на строку текущей даты

						if($pid = open3(*CHILD_IN, ">&STDOUT", ">&STDERR", $param)) # запускаем команду
						{
							print STDERR "new ffmeg proc: ".$pid."\n"; # печать диагностики

							open(FID_FILE, '> /var/tmp/'.$switch.'_pid'); # файл, содержащий pid процесса ffmpeg
							print FID_FILE $pid; # записываем pid
							close FID_FILE;

							open(CMD_FILE, '> /var/tmp/'.$switch.'_cmd'); # файл, содержащий строку GET-запроса для команды
							print CMD_FILE $param_str; # записываем строку
							close CMD_FILE;

							waitpid( $pid, 0 ); # пока процесс живой - ждем
						}
					#}
				}
				else # выполняется для родительского процесса
				{
					print STDERR "\nnew pl child: ".$pid_pl{$switch}."\n"; # печать диагностики
				}
			}
		}
		elsif($action eq 'stop') # команда остановки
		{
########################################################################################################################################
			$pkill{video_mixmodule} =	"pkill -9 ffmpeg_1";

			$pkill{audio_mixmodule} =	"pkill -9 ffmpeg_2";

 			$pkill{mix} =                   "pkill -9 ffmpeg_3";

			$pkill{english_video} =	        "pkill -9 ffmpeg_4";

			$pkill{audio_rus} =	        "pkill -9 ffmpeg_5";

			$pkill{save_orig_video} =	"pkill -9 ffmpeg_6";

			$pkill{save_orig_audio} =	"pkill -9 ffmpeg_7";

			$cmd_pkill = $pkill{$switch};
print STDERR "\n **** $cmd_pkill *** \n";
			system($cmd_pkill);

			#stop_pl_child($switch); # убиваем perl-потомка
			#stop_ffmpeg($switch); # убиваем процесс ffmpeg
		}
		elsif($action eq 'select') # команда замены montana/russia в файле настроек вовзы
		{
			$translation_source = get_val('value', $param_str); # получаем требуемое значение источника перевода
			print STDERR "\nselected: ".$translation_source."\n"; # печать диагностики

			replace_trans_source($translation_source); # заменяем
		}
	}

	Time::HiRes::sleep(0.5); # таймаут цикла считывания команд
	print STDERR '.'; # индикатор работы скрипта
}

#===================================================================================================================
# замена montana/russia в строке <video src="montana" start="-2" length="-1"/>

sub replace_trans_source
{
	$new_source = $_[0]; # параметр - строка на замену

	unless(open(FILE, "< $smil_file")) { print STDERR "cant open $smil_file for reading"; return; } # открываем файл с настройками на чтение

	$str_out = '';
	while($str = <FILE>) # перебираем строки файла настроек
	{
		if($str =~ /video\s+src=/) { $str =~ s/src="[^"]*"/src="$new_source"/g; } # производим замену в нужной строке

		$str_out .= $str; # формируем текст выходного файла
	}
	close FILE;

	unless(open(FILE, "> $smil_file")) { print STDERR "cant open $smil_file for writing"; return; } # открываем файл на запись
	print FILE $str_out; # записываем резултат
	close FILE;
}

#===================================================================================================================
# убиение потомка

sub stop_pl_child
{
	$switch = $_[0]; # параметр - pid потомка

	if(defined($pid_pl{$switch}) && $pid_pl{$switch} > 0) # если процесс с таким pid уже запущен
	{
		$cnt = kill 'SIGKILL', $pid_pl{$switch}; # убиваем и смотрим количество убитых
		print STDERR "\nkill pl child ".$pid_pl{$switch}."\nchilds killed: ".$cnt."\n"; # печать диагностики
	};
}
#===================================================================================================================
# убивание процесса ffmpeg

sub stop_ffmpeg
{
	$switch = $_[0]; # параметр - селектор команды

	if( open(FID_FILE, '< /var/tmp/'.$switch.'_pid') ) # открываем файл содержащий pid процесса ffmpeg для команды из параметра
	{
		$pid_ffmpeg = <FID_FILE>; # считываем pid
		close FID_FILE;

		if(defined($pid_ffmpeg) && $pid_ffmpeg > 0) # если процесс с таким pid уже запущен
		{
			$cnt = kill 'SIGKILL', $pid_ffmpeg; # убиваем и смотрим количество убитых
			print STDERR "\nkill ffmpeg proc ".$pid_ffmpeg."\nffmpegs killed: ".$cnt."\n"; #  печать диагностики

			if($clear_params_on_stop && $cnt) # если нужно очистить строку параметров при остановке потока
			{
				open(CMD_FILE, '> /var/tmp/'.$switch.'_cmd'); # файл содержащий строку GET-запроса на запуск команды
				print CMD_FILE ''; # записываем пустую строку
				close CMD_FILE;
			}
		};
	}
}

#===================================================================================================================
# вычленяем значение параметра из строки GET-запроса

sub get_val
{
	$_[1] =~ /$_[0]=([^=&]*)(&|$)/;

	return $1;
}

#===================================================================================================================
# преобразуем число секунд в формат времени MM:SS

sub normalize_time
{
	return ($_[0] =~ /\D/ ? '00:00' : sprintf("%02u", int $_[0] / 60) . ":" . sprintf("%02u", $_[0] % 60));
}

