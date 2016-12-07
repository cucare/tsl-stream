use tsl_events;

-- -----------------------------------------------------------

drop table if exists event;

create table event
(
	evt_id			 int not null AUTO_INCREMENT primary key,
	evt_upper_id	 int default 0,
	evt_evttp_id	 int,
	evt_title		 text not null,
	evt_subtitle	 text,
	evt_abbr		 varchar(30),
	evt_stamp		 timestamp default now(),
	evt_beg_dt		 date,
	evt_beg_tm		 time,
	evt_end_dt		 date,
	evt_end_tm		 time,
	evt_order		 int default 100,
	evt_descr		 text,
	evt_archive		 tinyint default 0
);

-- ------------------------------------------------------------

drop table if exists event_type;

create table event_type
(
	evttp_id int not null AUTO_INCREMENT primary key,
	evttp_name text not null,
	evttp_abbr varchar(30),
	evttp_descr text
);

insert into event_type(evttp_name, evttp_abbr) values('conference', 'conf');
insert into event_type(evttp_name, evttp_abbr) values('day', 'day');
insert into event_type(evttp_name, evttp_abbr) values('part', 'part');

