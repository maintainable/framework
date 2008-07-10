DROP TABLE IF EXISTS column_types;
CREATE TABLE column_types (
  id            int(11) auto_increment,

  integer_col   int(11),
  string_col    varchar(255),
  text_col      text,
  float_col     float(11,1),
  decimal_col   decimal(11,1),
  datetime_col  datetime,
  date_col      date,
  time_col      time,
  blob_col      blob,
  boolean_col   tinyint(1),
  enum_col      enum('a', 'b'),

  integer_col_nn   int(11)      NOT NULL,
  string_col_nn    varchar(255) NOT NULL,
  text_col_nn      text         NOT NULL,
  float_col_nn     float(2,1)   NOT NULL,
  decimal_col_nn   decimal(2,1) NOT NULL,
  datetime_col_nn  datetime     NOT NULL,
  date_col_nn      date         NOT NULL,
  time_col_nn      time         NOT NULL,
  blob_col_nn      blob         NOT NULL,
  boolean_col_nn   tinyint(1)   NOT NULL,
  enum_col_nn      enum('a', 'b') NOT NULL,

  PRIMARY KEY  (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS unit_tests;
CREATE TABLE unit_tests (
  id             int(11) auto_increment,
  integer_value  int(11) default '0',
  string_value   varchar(255) default '',
  text_value     text default '',
  float_value    float(2,1) default '0.0',
  decimal_value  decimal(2,1) default '0.0',
  datetime_value datetime default '0000-00-00 00:00:00',
  date_value     date default '0000-00-00',
  time_value     time default '00:00:00',
  blob_value     blob default '',
  boolean_value  tinyint(1) default '0',
  enum_value     enum('a', 'b') default 'a',
  email_value    varchar(255) default '',
  PRIMARY KEY (id), 
  KEY string_value (string_value),
  UNIQUE KEY integer_value (integer_value),
  KEY integer_string (integer_value, string_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS articles; 
CREATE TABLE articles (
  id      int(11) auto_increment, 
  title   varchar(255) default '',
  user_id int(11)      default 0,
  PRIMARY KEY (id), 
  KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS articles_categories;
CREATE TABLE articles_categories (
  id          int(11) auto_increment, 
  article_id  int(11) NOT NULL,
  category_id int(11) NOT NULL, 
  PRIMARY KEY (id), 
  KEY article_id (article_id),
  KEY category_id (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS categories; 
CREATE TABLE categories (
  id     int(11) auto_increment, 
  name   varchar(255)  NOT NULL,
  parent_id int(11)    NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS tags; 
CREATE TABLE tags (
  id    int(11) auto_increment,
  name  varchar(255) NOT NULL default '',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS taggings; 
CREATE TABLE taggings (
  id         int(11) auto_increment,
  tag_id     int(11) NOT NULL,
  article_id int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS comments; 
CREATE TABLE comments (
  id         int(11) auto_increment, 
  body       text    NOT NULL,
  article_id int(11) NOT NULL,
  user_id    int(11) NOT NULL,
  created_at datetime,
  PRIMARY KEY (id), 
  KEY article_id (article_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS companies;
CREATE TABLE companies (
  id         int(11) auto_increment, 
  name       varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id         int(11) auto_increment, 
  company_id int(11),
  name       varchar(255) default '',
  first_name varchar(40) default '',
  approved   tinyint(1) default '1',
  type       varchar(255) default '',
  created_at datetime default '0000-00-00 00:00:00',
  created_on date default '0000-00-00',
  updated_at datetime default '0000-00-00 00:00:00',
  updated_on date default '0000-00-00',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS avatars; 
CREATE TABLE avatars (
  id       int(11) auto_increment, 
  user_id  int(11) NOT NULL,
  filepath varchar(255) NOT NULL,
  PRIMARY KEY (id),
  KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- namespaced
DROP TABLE IF EXISTS fax_jobs;
CREATE TABLE fax_jobs (
  id        int(11) auto_increment, 
  page_size varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS fax_recipients;
CREATE TABLE fax_recipients (
  id         int(11) auto_increment, 
  name       varchar(255) NOT NULL,
  fax_job_id int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS fax_attachments;
CREATE TABLE fax_attachments (
  id         int(11) NOT NULL auto_increment,
  fax_job_id int(11) NOT NULL default '0',
  article_id int(11) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY index_fax_attachments_on_fax_job_id_and_article_id (fax_job_id, article_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
