CREATE TABLE h2o_redirect (
	ID int NOT NULL auto_increment,
	ACTIVE char(1) not null DEFAULT 'Y',
	REDIRECT_FROM text  NULL,
	REDIRECT_TO text  NULL,
	primary key (ID)
);
