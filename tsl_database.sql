use tsl_events;

-- -------------------------------------------------

drop table if exists event;

CREATE TABLE event (
  evt_id int(11) NOT NULL AUTO_INCREMENT,
  evt_upper_id int(11) DEFAULT '0',
  evt_evttp_id int(11) DEFAULT NULL,
  evt_title text NOT NULL,
  evt_subtitle text,
  evt_abbr varchar(30) DEFAULT NULL,
  evt_stamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  evt_beg_dt date DEFAULT NULL,
  evt_beg_tm time DEFAULT NULL,
  evt_end_dt date DEFAULT NULL,
  evt_end_tm time DEFAULT NULL,
  evt_order int(11) DEFAULT '100',
  evt_descr text,
  evt_archive_flag tinyint(4) DEFAULT '0',
  PRIMARY KEY (evt_id)
);

-- -------------------------------------------------

drop table if exists event_media;

CREATE TABLE event_media (
  evtmd_id int(11) NOT NULL AUTO_INCREMENT,
  evtmd_evt_id int(11) NOT NULL,
  evtmd_run_flag tinyint(4) DEFAULT '0',
  evtmd_media_file varchar(1000) DEFAULT '',
  evtmd_start timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  evtmd_duration time DEFAULT '00:00:00',
  evtmd_day_num int(11) DEFAULT NULL,
  evtmd_archive_flag tinyint(4) DEFAULT '0',
  PRIMARY KEY (evtmd_id)
);

-- -------------------------------------------------

drop table if exists command;

CREATE TABLE command (
  cmd_id int(11) NOT NULL AUTO_INCREMENT,
  cmd_name varchar(63) DEFAULT NULL,
  cmd_switch1 varchar(63) DEFAULT NULL,
  cmd_switch2 varchar(63) DEFAULT NULL,
  cmd_switch3 varchar(63) DEFAULT NULL,
  cmd_text text,
  cmd_stamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  cmd_flag tinyint(4) DEFAULT NULL,
  cmd_num int(11) DEFAULT NULL,
  PRIMARY KEY (cmd_id)
);

insert into command(cmd_name, cmd_num) values('last_day_num', 1);
insert into command(cmd_switch1, cmd_name, cmd_text) values('', 'Schedule_Loader_response', '');
