--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'SQL_ASCII';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

--
-- Name: hex_to_int(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION hex_to_int(hexval character varying) RETURNS integer
    LANGUAGE plpgsql IMMUTABLE STRICT
    AS $$
DECLARE
   result  int;
BEGIN
 EXECUTE 'SELECT x''' || hexval || '''::int' INTO result;  RETURN result;
END; $$;


ALTER FUNCTION public.hex_to_int(hexval character varying) OWNER TO postgres;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: data; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE data (
    id_dev integer NOT NULL,
    id_prop integer NOT NULL,
    data timestamp(0) without time zone NOT NULL,
    valor real NOT NULL
);


ALTER TABLE public.data OWNER TO postgres;

--
-- Name: dev_prop; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE dev_prop (
    id_dev integer NOT NULL,
    id_prop integer NOT NULL
);


ALTER TABLE public.dev_prop OWNER TO postgres;

--
-- Name: devs; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE devs (
    id integer NOT NULL,
    name character varying(50) NOT NULL
);


ALTER TABLE public.devs OWNER TO postgres;

--
-- Name: props; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE props (
    id integer NOT NULL,
    name character varying(50) NOT NULL
);


ALTER TABLE public.props OWNER TO postgres;

--
-- Name: dev_prop_view; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dev_prop_view AS
    SELECT dev_prop.id_prop, dev_prop.id_dev, devs.name AS dev, props.name AS prop FROM ((props JOIN dev_prop ON ((props.id = dev_prop.id_prop))) JOIN devs ON ((devs.id = dev_prop.id_dev)));


ALTER TABLE public.dev_prop_view OWNER TO postgres;

--
-- Name: devs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE devs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.devs_id_seq OWNER TO postgres;

--
-- Name: devs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE devs_id_seq OWNED BY devs.id;


--
-- Name: props_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE props_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.props_id_seq OWNER TO postgres;

--
-- Name: props_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE props_id_seq OWNED BY props.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY devs ALTER COLUMN id SET DEFAULT nextval('devs_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY props ALTER COLUMN id SET DEFAULT nextval('props_id_seq'::regclass);


--
-- Name: data_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY data
    ADD CONSTRAINT data_pkey PRIMARY KEY (id_dev, id_prop, data);


--
-- Name: dev_prop_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY dev_prop
    ADD CONSTRAINT dev_prop_pkey PRIMARY KEY (id_dev, id_prop);


--
-- Name: devs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY devs
    ADD CONSTRAINT devs_pkey PRIMARY KEY (id);


--
-- Name: props_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY props
    ADD CONSTRAINT props_pkey PRIMARY KEY (id);


--
-- Name: datad; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX datad ON data USING btree (data);


--
-- Name: dev_prop_id_dev_id_prop_key; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX dev_prop_id_dev_id_prop_key ON dev_prop USING btree (id_dev, id_prop);


--
-- Name: devs_id_key; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX devs_id_key ON devs USING btree (id);


--
-- Name: props_id_key; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX props_id_key ON props USING btree (id);


--
-- Name: data_id_dev_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY data
    ADD CONSTRAINT data_id_dev_fkey FOREIGN KEY (id_dev, id_prop) REFERENCES dev_prop(id_dev, id_prop) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: dev_prop_id_dev_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dev_prop
    ADD CONSTRAINT dev_prop_id_dev_fkey FOREIGN KEY (id_dev) REFERENCES devs(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: dev_prop_id_prop_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dev_prop
    ADD CONSTRAINT dev_prop_id_prop_fkey FOREIGN KEY (id_prop) REFERENCES props(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

