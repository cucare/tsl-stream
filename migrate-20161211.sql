use tsl_events;

alter table event add column evt_media_file varchar(1000);

alter table event add column evt_duration varchar(9);

alter table event add column evt_run tinyint default 0;
