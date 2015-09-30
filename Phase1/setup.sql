CREATE DATABASE pms;

CREATE TABLE publications (
	pid SERIAL PRIMARY KEY,
	title VARCHAR NOT NULL,
	authors VARCHAR NOT NULL,
	research_field VARCHAR NOT NULL,
	publication_year INTEGER NOT NULL,
	venue VARCHAR NOT NULL,
	papertype VARCHAR NOT NULL,
	link VARCHAR NOT NULL,
	keywords VARCHAR
);

CREATE TABLE users (
	uid SERIAL PRIMARY KEY,
	email VARCHAR NOT NULL UNIQUE,
	password VARCHAR NOT NULL,
	role INTEGER CHECK (0 <= role and role <= 3)
);

CREATE TABLE suggestions (
	sid SERIAL PRIMARY KEY,
	from_uid SERIAL REFERENCES users (uid),
	to_pid SERIAL REFERENCES publications (pid),
	changes VARCHAR NOT NULL
);