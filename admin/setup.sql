CREATE TYPE public.userlevel AS ENUM ('Admin', 'User', 'Devel');
CREATE TYPE public.ds_type AS ENUM ('jdbc', 'jndi');
CREATE TYPE public.cron_period AS ENUM('custom', 'hourly', 'daily', 'weekly', 'monthly', 'never');
CREATE TYPE public.rep_format AS ENUM('pdf', 'html', 'html2', 'rtf', 'xls', 'jxl', 'csv', 'xlsx', 'pptx', 'docx');
CREATE TYPE public.rmd_format AS ENUM('html', 'pdf', 'docx');

CREATE TABLE public.access_group (	id SERIAL PRIMARY KEY,
	name character varying(255) NOT NULL
);

CREATE TABLE public.groups ( id SERIAL PRIMARY KEY,
    name character varying(255),
    reportids character varying(255),
    description character varying(255)
);

CREATE TABLE public.group_access ( id SERIAL PRIMARY KEY,
		report_group_id integer NOT NULL REFERENCES public.groups(id),
    access_group_id integer NOT NULL REFERENCES public.access_group(id),
		UNIQUE(report_group_id, access_group_id)
);

CREATE TABLE public.datasource (	id SERIAL PRIMARY KEY,
		name character varying(50) UNIQUE,
		type public.ds_type NOT NULL,
    url character varying(250),
		username character varying(255) NOT NULL,
    password character varying(255) NOT NULL
);

CREATE TABLE public.jasper (	id SERIAL PRIMARY KEY,
    repname character varying(200),
    datasource_id integer NOT NULL REFERENCES public.datasource(id),
    download_only character varying(200),
    outname character varying(200),
    name character varying(200),
    is_grouped numeric(10,0) DEFAULT 0,
    description character varying(255)
);

CREATE TABLE public.inputs (	id SERIAL PRIMARY KEY,
    input character varying(2550),
    name character varying(255),
    report_id integer NOT NULL REFERENCES public.jasper(id)
);

CREATE TABLE public.parameters (	id SERIAL PRIMARY KEY,
    reportid numeric,
    ptype character varying(250),
    pvalues character varying(250),
    pname character varying(250)
);


CREATE TABLE public.report_access (	id SERIAL PRIMARY KEY,
    access_group_id integer NOT NULL	REFERENCES public.access_group(id),
    report_id integer NOT NULL				REFERENCES public.jasper(id),
		UNIQUE(report_id, access_group_id)
);

CREATE TABLE public.user (	id SERIAL PRIMARY KEY,
    name character varying(250),
    email character varying(250),
    password character varying(250),
		ftp_user character varying(250),
		pg_password character varying(250),
    accesslevel public.userlevel DEFAULT 'User',
		UNIQUE(email)
);

CREATE TABLE public.user_access (	id SERIAL PRIMARY KEY,
    user_id integer NOT NULL					REFERENCES public.user(id),
    access_group_id integer NOT NULL	REFERENCES public.access_group(id),
		UNIQUE(user_id, access_group_id)
);

CREATE TABLE public.links (	id SERIAL PRIMARY KEY,
		name character varying(200),
    description character varying(255),
    url character varying(250)
);

CREATE TABLE public.link_access (	id SERIAL PRIMARY KEY,
    link_id integer NOT NULL					REFERENCES public.links(id),
    access_group_id integer NOT NULL	REFERENCES public.access_group(id),
		UNIQUE(link_id, access_group_id)
);

CREATE TABLE public.schedule (	id SERIAL PRIMARY KEY,
	cron_period public.cron_period NOT NULL,
	name character varying(50) NOT NULL,
	format public.rep_format NOT NULL,
	datasource_id integer NOT NULL REFERENCES public.datasource(id),
	filename character varying(100) NOT NULL,
	email character varying(50),
	email_subj character varying(50),
	email_body character varying(250),
	email_tmpl character varying(100),
	noemail BOOLEAN DEFAULT FALSE,
	url_opt_params character varying(100)
);

CREATE TABLE public.pglink (	id SERIAL PRIMARY KEY,
		name character varying(250) NOT NULL,
		host character varying(250) NOT NULL,
		port integer NOT NULL default 5432,
		username character varying(250) NOT NULL,
    password character varying(250) NOT NULL,
		dbname character varying(80) NOT NULL,
		svc_name character varying(50) NOT NULL,
		owner_id 	integer NOT NULL	REFERENCES public.user(id)
);

CREATE TABLE public.gslink (	id SERIAL PRIMARY KEY,
		name character varying(250) NOT NULL,
		url character varying(250) NOT NULL,
		username character varying(250),
    password character varying(250),
		owner_id 	integer NOT NULL	REFERENCES public.user(id)
);


CREATE TABLE public.map ( id SERIAL PRIMARY KEY,
    name character varying(50) NOT NULL,
		description character varying(50) NOT NULL
);

CREATE TABLE public.map_pglink ( id SERIAL PRIMARY KEY,
    map_id 		integer NOT NULL REFERENCES public.map(id),
    pglink_id integer NOT NULL REFERENCES public.pglink(id),
		UNIQUE(map_id, pglink_id)
);

CREATE TABLE public.map_gslink ( id SERIAL PRIMARY KEY,
    map_id 		integer NOT NULL REFERENCES public.map(id),
    gslink_id integer NOT NULL REFERENCES public.gslink(id),
		UNIQUE(map_id, gslink_id)
);


CREATE TABLE public.map_access ( id SERIAL PRIMARY KEY,
    map_id 						integer NOT NULL REFERENCES public.map(id),
    access_group_id 	integer NOT NULL REFERENCES public.access_group(id),
		UNIQUE(map_id, access_group_id)
);

CREATE TABLE public.permalink (	id SERIAL PRIMARY KEY,
		description character varying(255),
		page character varying(255),
		query character varying(255),
    map_id integer NOT NULL		REFERENCES public.map(id),
		created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		expires TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP + interval '1 hour',
		visits			 integer NOT NULL DEFAULT 0,
		visits_limit integer NOT NULL DEFAULT 1,
		hash character varying(36) NOT NULL
);

CREATE TABLE public.schedule_r (	id SERIAL PRIMARY KEY,
	rmap_id integer NOT NULL REFERENCES public.map(id),
	rmd_source character varying(250) NOT NULL,
	format public.rmd_format NOT NULL,
	email character varying(50),
	email_subj character varying(50),
	email_body character varying(250),
	email_tmpl character varying(100),
	mail_on_change_only BOOLEAN DEFAULT FALSE,
	url_opt_params character varying(100)
);
