drop table if exists wh_user;
create table wh_user(
	user_id int unsigned not null auto_increment comment '主键',
	user_reg_time int not null default '0' comment '注册时间',
	user_device_code int not null default '000' comment '用户设备号', 
	user_last_logintime int default '' comment '上次登录时间',
	user_pwd char(32) not null default '000' comment '登录密码',
	user_tel char(11) not null default '' comment '用户手机号',
	user_sex enum('男','女') comment '用户性别',
	user_weight tinyint comment '用户体重',
	user_height decimal(5,2) comment '用户身高',
	user_age tinyint comment '用户年龄',
	user_see_card smallint unsigned not null default 0 comment '用户查看私密空间券数',
	///user_integral int not null default 0 comment '用户积分',
	user_nickname char(32) not null default '' comment '用户昵称',
	user_logo varchar(128) not null default '' comment '用户头像',
	user_big_logo varchar(128) not null default '' comment '头像大图',
	///is_member enum('0','1','2') not null default '0' comment '是否是会员',
	user_city char(16) not null default '' comment '用户所在城市',
	user_zan int unsigned not null default 0 comment '用户点赞数',
	user_tag char(8) comment '用户发展倾向',
	///user_flower int not null default '5' comment '用户花朵数',
	user_coin int unsigned not null default 0 comment '用户红豆数',
	user_freeze_coin int unsigned not null default 0 comment '用户冻结红豆数',
	user_weixin varchar(32) not null default '' comment '用户微信',
	////user_qq tinyint not null default '' comment '用户QQ',
	user_weixin_id varchar(32) not null default '' comment '用户微信ID，用于标识',
	////user_qq_id varchar(32) not null default '' comment '用户qqID，用于标识',
	user_level tinyint unsigned not null default 1 comment '用户等级',
	user_constell char(16) DEFAULT NULL COMMENT '用户星座'
	user_sign varchar(128) comment '用户标签',
	user_collect_task text comment '用户任务收藏',
	user_collect_task_num int unsigned comment '用户任务收藏数',
	user_error_num int default 0 comment '用户任务违约次数',
	is_forbidden enum('0','1') default '0' comment '是否被禁',
	user_care text comment '用户关注',
	user_fans text comment '用户粉丝',
	user_care_num int unsigned default 0 comment '用户关注数',
	user_fans_num int unsigned default 0 comment '用户粉丝数',
	is_success enum('0','1') default '0' comment '用于判断是否完成注册',
	key(user_weixin_id),
	unique key(user_tel),
	primary key(user_id)
)engine=Innodb charset=utf8 comment '用户信息表';

drop table if exists wh_tel_code;
create table wh_tel_code(
	id int not null auto_increment comment '主键ID',
	user_tel char(11) not null comment '用户注册手机',
	code mediumint not null comment '手机验证码',
	expire_time int unique not null comment '过期时间',
	primary key(id),
	key(user_tel)
)engine=Myisam charset=utf8 comment '注册验证码表';

drop table if exists wh_user_credit;
create table wh_user_credit(
	id int unsigned not null auto_increment comment '主键ID',
	user_id int unsigned not null comment '用户ID',
	avg_conformity_score float(4,2) comment '符合度评分',
	avg_action_capacity_score float(4,2) comment '活动能力评分',
	avg_attitude_score float(4,2) comment '工作态度评分',
	pub_time int unsigned comment '数据创建时间'
	upd_time int unsigned comment '数据最新更新时间';
	primary key(id),
	key(user_id)
)engine=Innodb charset=utf8 comment '用户信用表';

drop table if exists wh_user_message;
create table wh_user_message(
	mess_id int unsigned not null auto_increment comment '主键ID',
	user_id int unsigned not null comment '用户ID',
	pub_time int unsigned not null default 1 comment '信息发布时间',
	mess_title varchar(20) not null default '' comment '信息标题',
	mess_content varchar(256) not null default '' comment '信息内容',
	is_read enum('0','1') default '0' comment '是否已读',
	primary key(id),
	key(user_id)
)engine=Myisam charset=utf8 comment '用户接收消息表';

drop table if exists wh_recharge_record;
create table wh_recharge_record(
	id int unsigned not null auto_increment comment '主键ID',
	user_id int unsigned not null comment '用户ID',
	recharge_fee int unsigned not null comment '充值数额',
	add_time int unsigned not null default 0 comment '添加时间',
	recharge_code varchar(64) not null default '0' comment '充值码',
	primary key(id),
	key(user_id)
)engine=Innodb charset=utf8 comment '用户充值记录表';

drop table if exists wh_taskemployer_comment;
create table wh_taskemployer_comment(
	id int unsigned not null auto_increment comment 'id',
	user_id int unsigned not null comment '用户ID',
	task_id int unsigned not null comment '任务id',
	comment_user_id int unsigned not null comment '被评论用户ID',
	conformity_score float(4,2) comment '符合度评分',
	action_capacity_score float(4,2) comment '活动能力评分',
	attitude_score float(4,2) comment '工作态度评分',
	user_comment varchar(256) comment '文字评价',
	comment_pub_time int unsigned not null comment '评论发布时间',
	primary key(id),
	key(user_id),
	key(task_id),
	key(comment_user_id),
	key(conformity_score),
	key(action_capacity_score),
	key(attitude_score),
	key(user_comment),
	key(comment_pub_time)
)engine=Innodb charset=utf8 comment '雇主评价表';

drop table if exists wh_taskemployee_comment;
create table wh_taskemployee_comment(
	id int unsigned not null auto_increment comment 'id',
	user_id int unsigned not null comment '用户ID',
	task_id int unsigned not null comment '任务id',
	task_user_id int unsigned not null comment '被评论用户ID',
	conformity_score float(4,2) comment '符合度评分',
	communion_score float(4,2) comment '交流态度评分',
	credibility_score float(4,2) comment '诚信度评分',
	user_comment varchar(256) comment '文字评价',
	comment_pub_time int unsigned not null comment '评论发布时间',
	primary key(id),
	key(user_id),
	key(task_id),
	key(task_user_id),
	key(conformity_score),
	key(credibility_score),
	key(communion_score),
	key(user_comment),
	key(comment_pub_time)
)engine=Innodb charset=utf8 comment '雇员评价表';

drop table if exists wh_user_task;
create table wh_user_task(
	task_id int unsigned not null auto_increment comment 'ID',
	task_user_id int unsigned not null comment '用户ID',
	task_fee int not null default '0' comment '任务单人费用',
	task_fee_total int unsigned not null default '0' comment '任务总费用',
	task_title char(32) not null default '' comment '任务标题',
	task_content text not null comment '任务内容',
	task_tag char(64) not null default '' comment '任务类型',
	task_province char(16) not null default '浙江' comment '任务所在省份',
	task_city char(16) not null default '杭州' comment '任务所在城市',
	task_sex  enum('男','女','不限') default '男' comment '用户任务所需性别',
	task_join_peo varchar(512) comment '任务报名者',
	task_employee varchar(512) not null default '' comment '任务录用者',
	task_confirm_peo varchar(512) comment '任务接单者',
	task_refuse_peo varchar(512) comment '任务拒绝接单者',
	task_comment_peo varchar(512) comment '任务已评论的人',
	task_unfinish_peo varchar(512) comment '确认订单但未完成任务的',
	fee_type enum('0','1') default '0' comment '任務付費方式 0:全額担保,1:面议',
	is_have_unfinish enum('0','1') default '0' comment '是否有未完成任务的人',
	is_tui enum('0','1') default '0' comment '是否推送(1:推送)',
	task_banner_img varchar(256) comment '推荐任务的banner图',
	is_join enum('0','1','2') default '0' comment '任务是否有报名,1:有报名者，2：报名结束',
	is_confirm enum('0','1') default '0' comment '任务是否确认订单',
	is_finish enum('0','1','2') default '0' comment '任务是否完成(1:完成,2:用户取消)',
	is_comment enum('0','1') default '0' comment '任务是否评价(1:评价)',
	is_complain enum('0','1','2') default '0' comment '任务是否被投诉(1:被投诉,2:已解决投诉)',
	is_guarantee enum('0','1') default '0' comment '任务是否担保(0:未担保,1:全额担保)',
	task_peo_num smallint unsigned not null default 0 comment '任务所需人数量',
	task_man_num smallint unsigned not null default 0 comment '任务所需男生数量', 
	task_pub_time int not null default '0' comment '任务发布时间',
	task_end_time int not null default '10000' comment '任务结束时间',
	primary key(task_id),
	key(task_user_id)
)engine=Innodb charset=utf8 comment '用户任务发布表，用于任务雇主';

drop table if exists wh_user_task_trace;
create table wh_user_task_trace(
	id int unsigned not null auto_increment comment '主键id',
	user_id int unsigned not null comment '用户ID',
	task_id int unsigned not null comment '任务ID',
	task_user_id int unsigned not null comment '任务发布用户ID',
	is_join enum('0','1') default '1' comment '是否报名,1:已报名',
	is_employe enum('0','1') default '0' comment '是否录用',
	employe_time int unsigned comment '被录用时间',
	is_refuse enum('0','1') default '0' comment '是否拒绝',
	is_confirm enum('0','1','2') default '0' comment '是否确认订单,1:确认,2:拒绝',
	is_finish enum('0','1','2','3') default '0' comment '是否完成,0:未完成,1:已完成,2:用户自己确认交易失败,3:雇主判定交易失败',
	is_comment enum('0','1') default '0' comment '是否评价',
	is_expire enum('0','1') DEFAULT '0' COMMENT '任务是否结束,0:未结束，1：已结束',
	is_appeal enum('0','1','2') default '0' comment '是否申诉,1:已申诉,2:已解决',
	add_time int unsigned comment '添加时间',
	task_end_time int unsigned comment '任务结束时间',
	task_salary int unsigned not null default 0 comment '任务薪水',
	primary key(id),
	key(user_id),
	key(task_id),
	key(task_user_id)
)engine=Innodb charset=utf8 comment '用户任务跟踪表,用于任务投递者';

drop table if exists wh_unconfirm_employee_record;
create table wh_unconfirm_employee_record(
	id int unsigned not null auto_increment comment '主键',
	user_id int unsigned not null comment '用户ID',
	task_id int unsigned not null comment '任务ID',
	task_user_id int unsigned not null comment '任务发布者ID',
	primary key(id),
	key(user_id),
	key(task_id)
)engine=Innodb charset=utf8 comment '任务中间表，用于记录未按时接单的用户';

drop table if exists wh_task_uncomment_record;
create table wh_task_uncomment_record(
	id int unsigned not null auto_increment comment '主键',
	task_id int unsigned not null comment '任务ID',
	primary key(id),
	key(task_id)
)engine=Innodb charset=utf8 comment '任务中间表，用于记录雇主未按时评价的任务';

drop table if exists wh_task_unfinish_record;
create table wh_task_unfinish_record(
	id int unsigned not null auto_increment comment '主键',
	task_id int unsigned not null comment '任务ID',
	primary key(id),
	key(task_id)
)engine=Innodb charset=utf8 comment '任务中间表，用于记录雇主未按时结束交易的任务';

drop table if exists wh_user_recive_coin;
create table wh_user_recive_coin(
	id int unsigned not null auto_increment comment '主键id',
	user_id int unsigned not null comment '用户ID',
	from_user_id int unsigned not null comment '赠送红豆的用户ID',
	coin_num int unsigned not null default '0' comment '接收红豆总数',
	add_time int unsigned not null default 0 comment '添加时间',
	primary key(id),
	key(user_id),
	key(from_user_id)
)engine=Innodb charset=utf8 comment '用户接收红豆表';

drop table if exists wh_user_advice;
create table wh_user_advice(
	advice_id int unsigned not null auto_increment comment '意见ID',
	advice_user_id int unsigned not null comment '用户ID',
	advice_type varchar(32) not null comment '用户意见类型',
	advice_content text not null comment '意见内容',
	user_connection varchar(32) not null comment '用户联系方式',
	advice_time int comment '用户建议提交时间',
	primary key(advice_id)
)engine=Myisam charset=utf8 comment '用户意见表';

drop table if exists wh_dongtai;
create table wh_dongtai(
	dt_id int unsigned not null auto_increment comment 'ID',
	dt_user_id int unsigned not null comment '动态的用户ID',
	dt_pub_time int not null comment '发布时间',
	dt_total_zan int not null default '0' comment '总赞数',
	dt_content text comment '动态文字内容',
	dt_zan_people text comment '点赞用户(user_id)',
	coin_num int unsigned not null default '0' comment '动态红豆数',
	primary key(dt_id),
	key(dt_user_id)
)engine=Innodb charset=utf8 comment '用户动态';

drop table if exists wh_private_space;
create table wh_private_space(
	pri_id int unsigned not null auto_increment comment 'ID',
	pri_user_id int unsigned not null comment '动态的用户ID',
	pri_pub_time int not null comment '发布时间',
	pri_total_zan int not null default '0' comment '总赞数',
	pri_content text comment '动态文字内容',
	pri_zan_people text comment '点赞用户(user_id)',
	coin_num int unsigned not null default '0' comment '动态红豆数',
	primary key(dt_id),
	key(dt_user_id)
)engine=Innodb charset=utf8 comment '用户私密空间(暂为动态形式)';  

drop table if exists wh_pri_pics;
create table wh_pri_pics(
    id int unsigned not null auto_increment comment '主键id',
	pri_id int unsigned not null comment '动态id',
	pri_img char(100) comment '动态图片',
	pri_mid_img char(100) comment '动态中图',
	pri_small_img char(100) comment '动态缩略图',
	primary key(id),
	key(pri_id)
)engine=Myisam charset=utf8 comment '私密空间图册';

drop table if exists wh_dt_pics;
create table wh_dt_pics(
    id int unsigned not null auto_increment comment '主键id',
	dt_id int unsigned not null comment '动态id',
	dt_img char(100) comment '动态图片',
	dt_mid_img char(100) comment '动态中图',
	dt_small_img char(100) comment '动态缩略图',
	primary key(id),
	key(dt_id)
)engine=Myisam charset=utf8 comment '动态图册';

drop table if exists wh_user_member;
create table wh_user_member(
	id int unsigned not null auto_increment comment '主键ID',
	user_id int unsigned not null comment '用户ID',
	member_type enum('0','1','2','3','4','5') not null default '0' comment '会员类型,0:非会员,1:一次充,打电话,2:一次充,看私密动态,3:月充会员,4:季充会员,5:年充会员',
	member_reg_time int unsigned comment '会员注册时间',
	last_recharge_time int comment '最新充值时间',
	user_call_card smallint unsigned comment default 0 '会员电话券',
	recharge_total int unsigned comment '充值总额',
	expire_time int unsigned comment '会员过期时间',
	primary key(id),
	unique key(user_id)
)engine=Myisam charset=utf8 comment '用户会员表';

drop table if exists wh_recharge_once_space;
create table wh_recharge_once_space(
	id int unsigned not null auto_increment comment '主键id',
	user_id int unsigned comment '用户ID',
	user_id_find int unsigned comment '被查看的用户ID)',
	add_time int unsigned comment '添加时间',
	expire_time int unsigned comment '过期时间',
	primary key(id),
	key(user_id),
	key(user_id_find)
)engine=Innodb charset=utf8 comment '用户一次充红豆看空间记录表';

drop table if exists wh_recharge_once_call;
create table wh_recharge_once_call(
	id int unsigned not null auto_increment comment '主键id',
	user_id int unsigned comment '用户ID',
	add_time int unsigned comment '添加时间',
	expire_time int unsigned comment '过期时间',
	primary key(id),
	key(user_id)
)engine=Innodb charset=utf8 comment '用户一次充红豆打电话';

drop table if exists wh_call_card_record;
create table wh_call_card_record(
	id int unsigned not null auto_increment comment "主键id",
	user_id int unsigned not null comment '用户id',
	call_card_num smallint unsiged not null comment '交流券总数',
	rem_num smallint unsigned not null comment '未使用的交流券数',
	used_num smallint unsigned not null default 0 comment '已使用的交流券数',
	add_time int unsigned comment '添加时间',
	expire_time int unsigned comment '过期时间',
	primary key(id),
	key(user_id),
	key(rem_num),
	key(used_num),
	key(expire_time)
)engine=Innodb charset=utf8 comment '用户充值交流券记录表';

drop table if exists wh_user_member_record;
create table wh_user_member_record(
	id int unsigned not null auto_increment comment '主键ID',
	user_id int unsigned not null comment '用户ID',
	add_time int unsigned comment '添加时间',
	member_type tinyint comment '会员类型',
	expire_time int unsigned comment '过期时间',
	begin_time int unsigned comment '会员生效时间',
	recharge_fee mediumint unsigned comment '充值费用',
	primary key(id),
	key(user_id),
	key(add_time),
	key(expire_time),
	key(begin_time),
	key(member_type)
)engine=Innodb charset=utf8 comment '用户会员记录表';

drop table if exists wh_user_pics;
create table wh_user_pics(
    id int unsigned not null auto_increment comment '主键id',
	news_id unsigned smallint not null comment '新闻id',
	photo char(100) not null comment '图片'
)engine=Myisam charset=utf8 comment '用户图册';

drop table if exists wh_admin_user;
create table wh_admin_user(
	admin_user_id tinyint not null auto_increment comment '主键',
	admin_name char(16) not null comment '用戶名',
	admin_pwd char(16)  not null comment '用户密码',
	primary key(admin_user_id)
)engine=Myisam charset=utf8 comment '用户详细信息表';

drop table if exists wh_news;
create table wh_news(
	news_id int unsigned not null auto_increment comment '主键',
	is_tui enum('0','1') default '0' comment '是否推荐该新闻', 
	news_img varchar(128) not null default '' comment '新闻图片',
	news_title char(32) not null default '' comment '新闻标题',
	news_content longtext not null comment '新闻内容',
	news_time int unsigned not null comment '发布时间',
	news_source char(16) not null default '' comment '新闻来源',
	primary key(news_id)
)engine=Myisam charset=utf8 comment '新闻表';

drop table if exists wh_news_pics;
create table wh_news_pics(
    id int unsigned not null auto_increment comment '主键',
    news_id mediumint unsigned not null  comment '新闻id',
    news_pics char(100) not null comment '新闻原图',
    pics_small_pics char(100) not null comment '新闻缩略图',
    primary key (id)
)engine=Myisam charset=utf8 comment '新闻相册表';

drop table if exists wh_news_recommend;
create table wh_news_recommend(
	id int unsigned not null auto_increment comment '主键',
	banner_img varchar(128) not null comment 'banner图片',
	rec_info varchar(128) not null comment '推荐信息',
	add_time int not null default 0 comment '添加时间',
	type tinyint not null default 0 comment '推荐类型(0:推荐新闻)',
	primary key(id),
	key(banner_img),
	key(rec_info)
)engine=Innodb charset=utf8 comment '新闻推荐表';

drop table if exists wh_index_recommend;
create table wh_index_recommend(
	id int unsigned not null auto_increment comment '主键',
	banner_img varchar(128) not null comment 'banner图片',
	rec_info varchar(128) not null comment '推荐信息',
	add_time int not null default 0 comment '添加时间',
	type tinyint not null default 0 comment '推荐类型(0:推荐主播,1:广告)',
	primary key(id),
	key(banner_img),
	key(rec_info)
)engine=Innodb charset=utf8 comment '主页推荐表';

drop table if exists wh_task_recommend;
create table wh_task_recommend(
	id int unsigned not null auto_increment comment '主键',
	banner_img varchar(128) not null comment 'banner图片',
	rec_info varchar(128) not null comment '推荐信息',
	add_time int not null default 0 comment '添加时间',
	type tinyint not null default 0 comment '推荐类型(0:推荐任务)',
	primary key(id),
	key(banner_img),
	key(rec_info)
)engine=Innodb charset=utf8 comment '任务推荐表';

drop table if exists wh_employee_complain;
create table wh_employee_complain(
	id int unsigned not null auto_increment comment '主键',
	user_id int unsigned not null comment '投诉用户',
	task_id int unsigned not null comment '投诉任务ID',
	add_time int unsigned comment '添加时间',
	is_dispose enum('0','1') not null default '0' comment '是否处理',
	primary key(id),
	key(user_id),
	key(task_id),
	key(add_time)
)engine=Innodb charset=utf8 comment '用户维权申诉表(任务)';

drop table if exists wh_jobhunter;
create table wh_jobhunter(
    id int unsigned not null auto_increment comment '主键',
	user_name char(5) not null default '' comment '求职者姓名',
	user_tel char(11) not null default '' comment '求职者电话号码',
	user_email char(24) not null default '' comment '求职者邮箱',
	user_company varchar(24) not null default '' comment '求职者前公司',
	user_info varchar(64) not null default '' comment '任职岗位及相关年限',
	submit_time int unsigned not null default '0' comment '提交时间',
	primary key(id)
)engine=Myisam charset=utf8 comment '求职者信息表';

drop table if exists wh_job;
create table wh_job(
    job_id int unsigned not null auto_increment comment '主键',
    job_name char(16) not null default '' comment '职位名称',
    job_require text comment '职位要求',
    job_pub_time int unsigned not null default '' comment '职位发布时间',
    job_end_time int unsigned comment '职位截止时间',
    primary key(job_id)
)engine=Myisam charset=utf8 comment '求职者信息表';

drop table if exists wh_virtual_tel;
create table wh_virtual_tel(
    id int unsigned not null auto_increment comment '主键',
    bind_id char(32) not null default '' comment "号码绑定ID",
    callee char(16) not null default '' comment '被叫',
    caller_id char(16) not null default '' comment '主叫',
    virtual_num char(16) not null default '' comment '中间号码',
    primary key(id),
    key(virtual_num),
    key(caller_id)
)engine=Myisam charset=utf8 comment '虚拟电话表';

drop table if exists wh_user_trad;
create table wh_user_trad(
    id int unsigned not null auto_increment comment '主键',
    user_id int unsigned not null comment "用户ID",
    out_trad_on char(32) not null comment '订单号',
    add_time int unsigned not null comment '添加时间',
    primary key(id),
    key(user_id),
    key(out_trad_on)
)engine=Innodb charset=utf8 comment '用户订单表';

insert into wh_user (reg_time,device_num,last_login_time,user_pwd,user_tel) 
	values('2','2','2','11','11');
alter table wh_user_task add task_candidate text comment '任务应聘者';
alter table wh_dt_pics change column dt_small_img char(100) comment '动态缩略图';
alter table wh_job drop column job_end_time;
alter table wh_user add user_fans text comment '用户粉丝';
alter table wh_dt_pics add dt_small_img char(100) not null default '' comment '动态缩略图';
alter table wh_user add user_post_task text comment '用户投递的任务';
alter table wh_user add user_big_logo varchar(128) not null default '' comment '头像大图';
alter table wh_user_advice add advice_time int comment '用户建议提交时间';
alter table wh_dt_pics add dt_mid_img char(100) not null default '' comment '动态中图';
alter table wh_user_task add task_man_num smallint unsigned default '0' comment '任务所需男生数量';
