use tsl_events;

drop table if exists event_media;

create table event_media
(
	evtmd_id int not null auto_increment primary key,
	evtmd_evt_id int not null,
	evtmd_run_flag tinyint default 0,
	evtmd_media_file varchar(1000) default '',
	evtmd_duration varchar(9) default '00:00:00'
);
