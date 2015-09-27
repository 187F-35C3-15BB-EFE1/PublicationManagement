--creation of database
CREATE DATABASE pms;

--to go into database type \connect pms
--now we create table publications
CREATE TABLE publications (
pid SERIAL PRIMARY KEY,
title VARCHAR(255) NOT NULL,
authors VARCHAR(255) NOT NULL DEFAULT 'Anonymous',
research_field VARCHAR(255) NOT NULL,
publication_year INTEGER NOT NULL,
venue VARCHAR(255) NOT NULL,
papertype VARCHAR(255) NOT NULL,
arxiv_id VARCHAR(255) NOT NULL);		--????
