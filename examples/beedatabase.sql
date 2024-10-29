--
-- PostgreSQL database dump
--

-- Dumped from database version 16.3 (Ubuntu 16.3-1.pgdg22.04+1)
-- Dumped by pg_dump version 16.3 (Ubuntu 16.3-1.pgdg22.04+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: hstore; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS hstore WITH SCHEMA public;


--
-- Name: EXTENSION hstore; Type: COMMENT; Schema: -; Owner: 
--

--COMMENT ON EXTENSION hstore IS 'data type for storing sets of (key, value) pairs';


--
-- Name: postgis; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;


--
-- Name: EXTENSION postgis; Type: COMMENT; Schema: -; Owner: 
--

--COMMENT ON EXTENSION postgis IS 'PostGIS geometry and geography spatial types and functions';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: apiary; Type: TABLE; Schema: public; Owner: admin1
--

CREATE TABLE public.apiary (
    fid integer NOT NULL,
    nbr_of_boxes integer,
    bee_species character varying,
    bee_amount character varying,
    beekeeper character varying,
    picture character varying,
    disease boolean,
    kind_of_disease character varying,
    average_harvest integer,
    area_id integer,
    uuid character varying,
    field_uuid character varying,
    geom public.geometry(Point,3857)
);


ALTER TABLE public.apiary OWNER TO admin1;

--
-- Name: apiary_fid_seq; Type: SEQUENCE; Schema: public; Owner: admin1
--

CREATE SEQUENCE public.apiary_fid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.apiary_fid_seq OWNER TO admin1;

--
-- Name: apiary_fid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: admin1
--

ALTER SEQUENCE public.apiary_fid_seq OWNED BY public.apiary.fid;


--
-- Name: fields; Type: TABLE; Schema: public; Owner: admin1
--

CREATE TABLE public.fields (
    fid integer NOT NULL,
    proprietor character varying,
    plant_species character varying,
    picture character varying,
    review_date date,
    reviewer character varying,
    uuid character varying,
    geom public.geometry(Polygon,3857)
);


ALTER TABLE public.fields OWNER TO admin1;

--
-- Name: fields_fid_seq; Type: SEQUENCE; Schema: public; Owner: admin1
--

CREATE SEQUENCE public.fields_fid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fields_fid_seq OWNER TO admin1;

--
-- Name: fields_fid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: admin1
--

ALTER SEQUENCE public.fields_fid_seq OWNED BY public.fields.fid;


--
-- Name: apiary fid; Type: DEFAULT; Schema: public; Owner: admin1
--

ALTER TABLE ONLY public.apiary ALTER COLUMN fid SET DEFAULT nextval('public.apiary_fid_seq'::regclass);


--
-- Name: fields fid; Type: DEFAULT; Schema: public; Owner: admin1
--

ALTER TABLE ONLY public.fields ALTER COLUMN fid SET DEFAULT nextval('public.fields_fid_seq'::regclass);


--
-- Data for Name: apiary; Type: TABLE DATA; Schema: public; Owner: admin1
--

INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (2, 10, 'Apis Mellifera', '100000', 'Rita Levi Montalcini', 'DCIM/4.jpg', true, 'EFB', 20, 31, '{d89d4955-0d11-43b9-a6e5-32add1729628}', '{d89d4955-0d11-43b9-a6e5-32add1729628}', '0101000020110F0000C932090EAC742F41D33413A2C08C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (4, 4, 'Apis Mellifera', '10000', 'Stephen Hawking', 'DCIM/4.jpg', false, NULL, 10, 30, '{d6a44bf1-9a33-4b4a-9b2d-9f88928aaf24}', '{d6a44bf1-9a33-4b4a-9b2d-9f88928aaf24}', '0101000020110F0000204DCCF198732F41EC8CE546828C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (6, 3, 'Apis Mellifera Mellifera', '1000', 'Sheldon Cooper', 'DCIM/2.jpg', false, NULL, NULL, 37, '{82417071-c18a-4d6a-b2cd-44e46e3e65b6}', '{82417071-c18a-4d6a-b2cd-44e46e3e65b6}', '0101000020110F000078DDB61051732F4138A8E9AB298C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (9, 6, 'Apis Mellifera Carnica', '10000', 'Samantha Cristoforetti', 'DCIM/4.jpg', false, NULL, 200, 32, '{07453566-026b-4516-a449-a864fe768f8b}', '{07453566-026b-4516-a449-a864fe768f8b}', '0101000020110F0000C4D84660446F2F413917BBD6518C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (10, 10, 'Apis Mellifera Carnica', '1000', 'Rita Levi Montalcini', 'DCIM/2.jpg', false, NULL, 20, 37, '{b7494aa9-8847-4633-ae88-02fb33ecabc4}', '{b7494aa9-8847-4633-ae88-02fb33ecabc4}', '0101000020110F000059C090BD37782F41D0F33DAC998C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (11, 8, 'Apis Mellifera Carnica', '100000', 'Ptolemy', 'DCIM/2.jpg', false, NULL, 99, 33, '{882c0ee8-b8ca-4b17-9524-669f3fff2714}', '{882c0ee8-b8ca-4b17-9524-669f3fff2714}', '0101000020110F0000175315D64A752F417C70D31D448C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (12, 10, 'Apis Mellifera', '100000', 'Mae Jemison', 'DCIM/3.jpg', true, 'AFB', 1000, 35, '{1e90e07e-422f-476a-a6fd-cc20b74a807f}', '{1e90e07e-422f-476a-a6fd-cc20b74a807f}', '0101000020110F0000C885742B9D732F4190BF4B567D8C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (13, 3, 'Apis Mellifera Carnica', '1000', 'Nicolau Copernicus', 'DCIM/3.jpg', false, NULL, NULL, 29, '{b4d343df-98cb-4255-a54b-337fe17e397e}', '{b4d343df-98cb-4255-a54b-337fe17e397e}', '0101000020110F0000ECB5B75FF56E2F41AF42D382048C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (20, 1, 'Apis Mellifera Mellifera', '1000', 'Erasmus of Rotterdam', 'DCIM/2.jpg', false, NULL, NULL, 32, '{ad3118ae-825e-4916-b0f6-9a97c4db2e01}', '{ad3118ae-825e-4916-b0f6-9a97c4db2e01}', '0101000020110F0000524C7AAA23722F4183434D75C78C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (22, 2, 'Apis Mellifera Mellifera', '1000', 'Sheldon Cooper', 'DCIM/4.jpg', false, NULL, 2, 28, '{4ce89f9f-de89-4ba3-812d-77ddb87f4175}', '{4ce89f9f-de89-4ba3-812d-77ddb87f4175}', '0101000020110F00002A109A5AC9702F416C6E5C43288C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (24, 5, 'Apis Mellifera Carnica', '100000', 'Galileo Galilei', 'DCIM/1.jpg', false, NULL, 50, 33, '{009f64b2-d6e4-46b0-aba8-39946379e053}', '{009f64b2-d6e4-46b0-aba8-39946379e053}', '0101000020110F0000029EBEB91E752F41637E95A53F8C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (26, 3, 'Apis Mellifera Carnica', '1000', 'Marie Curie', 'DCIM/3.jpg', false, NULL, 666, 34, '{014be504-b170-44ef-96d6-333e874bd30e}', '{014be504-b170-44ef-96d6-333e874bd30e}', '0101000020110F00000098ABC042742F41920CC375A78C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (27, 3, 'Apis Mellifera', '10000', 'Samantha Cristoforetti', 'DCIM/3.jpg', false, NULL, 369, 40, '{92ea9423-a042-4e19-8a55-8c9650438de1}', '{92ea9423-a042-4e19-8a55-8c9650438de1}', '0101000020110F0000EE86BB8C8C732F41285F6328428C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (29, 2, 'Apis Mellifera Mellifera', '1000', 'Marco Bernasocchi', 'DCIM/2.jpg', false, NULL, NULL, 32, '{52f18a59-6f33-424a-979b-260b752a56d2}', '{52f18a59-6f33-424a-979b-260b752a56d2}', '0101000020110F0000921110C487742F412910FD066F8C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (30, 3, 'Apis Mellifera', '10000', 'Galileo Galilei', 'DCIM/1.jpg', false, NULL, 369, 41, '{fb42e6db-91ba-40a5-8dcb-8bfd598fced8}', '{fb42e6db-91ba-40a5-8dcb-8bfd598fced8}', '0101000020110F0000B9D1745582702F41DA5997074B8C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (31, 10, 'Apis Mellifera Carnica', '10000', 'Al Idrisi', 'DCIM/4.jpg', false, NULL, 568, 30, '{72ea5739-89f2-4559-a800-f544024b14b8}', '{72ea5739-89f2-4559-a800-f544024b14b8}', '0101000020110F0000C39F6F2E06732F4133BB6B037E8C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (34, 3, 'Apis Mellifera Carnica', '1000', 'Isaac Newton', 'DCIM/1.jpg', false, NULL, 100, 29, '{24b50311-53a8-4db3-a122-9fa06565a9a1}', '{24b50311-53a8-4db3-a122-9fa06565a9a1}', '0101000020110F000085B8628B7E6D2F417F0A0A3A158C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (35, 3, 'Apis Mellifera', '1000', 'Nicolau Copernicus', 'DCIM/3.jpg', true, 'AFB', NULL, 35, '{1cf4d15b-8d26-4d57-b167-c410513001be}', '{1cf4d15b-8d26-4d57-b167-c410513001be}', '0101000020110F00007C48628A8C722F416E6FC3D5A48C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (37, 6, 'Apis Mellifera Carnica', '100000', 'Gerardus Mercator', 'DCIM/2.jpg', false, NULL, NULL, 28, '{4bbe6420-a9e9-4829-9a9f-5f019ceb51a5}', '{4bbe6420-a9e9-4829-9a9f-5f019ceb51a5}', '0101000020110F0000B9BE3829546C2F41FDAE245D458D5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (38, 6, 'Apis Mellifera Carnica', '100000', 'Gerardus Mercator', 'DCIM/3.jpg', true, 'AFB', NULL, 39, '{e8dbe6a8-a1eb-42f4-947d-3346bae8ea8d}', '{e8dbe6a8-a1eb-42f4-947d-3346bae8ea8d}', '0101000020110F0000F6C13B7F536C2F4120709A195A8D5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (39, 4, 'Apis Mellifera', '100000', 'Elizabeth Blackburn', 'DCIM/2.jpg', false, NULL, 100, 36, '{45b7d175-db7a-4aa4-9694-88dc3bad5fa6}', '{45b7d175-db7a-4aa4-9694-88dc3bad5fa6}', '0101000020110F0000CCF1949DB8742F41BF78C1770A8C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (40, 10, 'Apis Mellifera', '100000', 'Elizabeth Blackburn', 'DCIM/4.jpg', true, 'EFB', 1000, 35, '{77bc4dda-2455-498c-8790-c867e049f985}', '{77bc4dda-2455-498c-8790-c867e049f985}', '0101000020110F00009D81F6398B742F413DA63F2E6B8C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (43, 3, 'Apis Mellifera Carnica', '1000', 'Albert Einstein', 'DCIM/1.jpg', false, NULL, NULL, 32, '{bfead805-8a6b-462b-bfce-4c045b4a8a69}', '{bfead805-8a6b-462b-bfce-4c045b4a8a69}', '0101000020110F0000BABC3B2EE7732F415709E900208C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (47, 5, 'Apis Mellifera Mellifera', '100000', 'Albert Einstein', 'DCIM/2.jpg', false, NULL, NULL, 31, '{1511c328-29db-43d0-be29-e49be6ddb1dd}', '{1511c328-29db-43d0-be29-e49be6ddb1dd}', '0101000020110F0000FBE7E60EC76F2F41719597C65B8C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (49, 5, 'Apis Mellifera Mellifera', '10000', 'Matthias Kuhn', 'DCIM/2.jpg', true, 'EFB', NULL, 33, '{91588d3f-807b-4eaa-9852-625ccede3157}', '{91588d3f-807b-4eaa-9852-625ccede3157}', '0101000020110F0000812FC72E406E2F41AAEA14A7228C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (51, 5, 'Apis Mellifera', '1000', 'Mae Jemison', 'DCIM/4.jpg', false, NULL, 12, 36, '{a71d03b1-2bc3-4726-8f53-6c6cc9264f9d}', '{a71d03b1-2bc3-4726-8f53-6c6cc9264f9d}', '0101000020110F0000A33DA7950C782F4112E08480838C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (54, 5, 'Apis Mellifera Carnica', '1000', 'Isaac Newton', 'DCIM/1.jpg', false, NULL, 100, 37, '{8f23aced-d901-4522-89ca-4bc034ad3ef7}', '{8f23aced-d901-4522-89ca-4bc034ad3ef7}', '0101000020110F0000F7F17B9B33752F416240B4DA418C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (55, 10, 'Apis Mellifera Carnica', '1000', 'Erasmus of Rotterdam', 'DCIM/3.jpg', false, NULL, NULL, 38, '{37742624-857e-4dc6-b5aa-774cc82720c1}', '{37742624-857e-4dc6-b5aa-774cc82720c1}', '0101000020110F0000D60B4DD37A702F416FB489BA238C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (56, 3, 'Apis Mellifera', '1000', 'Marie Curie', 'DCIM/3.jpg', false, NULL, 9, 40, '{bab30a82-dd39-429a-be95-00561dd91dd2}', '{bab30a82-dd39-429a-be95-00561dd91dd2}', '0101000020110F0000A220784DFD6C2F417D2AB5EF6C8D5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (57, 3, 'Apis Mellifera', '1000', 'Al Idrisi', 'DCIM/3.jpg', true, 'EFB', 6, 34, '{0235be9c-2026-42ac-a99c-f9e8183fc602}', '{0235be9c-2026-42ac-a99c-f9e8183fc602}', '0101000020110F0000BDEF091803702F41177E950B068C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (60, 5, 'Apis Mellifera', '10000', 'David Signer', 'DCIM/4.jpg', true, 'EFB', NULL, 37, '{0e703bc6-0a80-4ef3-82de-f4c3a1d69075}', '{0e703bc6-0a80-4ef3-82de-f4c3a1d69075}', '0101000020110F0000B2035FFAD4722F418E6E715B9D8C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (61, 6, 'Apis Mellifera Carnica', '100000', 'Mario Baranzini', 'DCIM/3.jpg', false, NULL, NULL, 38, '{99b841c3-b65a-4f44-96c9-e23cd09d23f3}', '{99b841c3-b65a-4f44-96c9-e23cd09d23f3}', '0101000020110F00009B7DDB78426C2F410879D3034C8D5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (62, 6, 'Apis Mellifera Carnica', '100000', 'Denis Rouzaud', 'DCIM/4.jpg', true, 'EFB', NULL, 34, '{7c0cd3cb-f202-418b-93ac-b82dd11c07af}', '{7c0cd3cb-f202-418b-93ac-b82dd11c07af}', '0101000020110F0000883D4995356C2F4192C96D33538D5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (63, 3, 'Apis Mellifera', '1000', 'Ptolemy', 'DCIM/3.jpg', false, NULL, 9, 34, '{1dc77378-9c35-466c-813a-004642bd81a1}', '{1dc77378-9c35-466c-813a-004642bd81a1}', '0101000020110F0000A5C161305F6D2F416F191FC75A8D5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (64, 7, 'Apis Mellifera Carnica', '1000', 'Stephen Hawking', 'DCIM/3.jpg', false, NULL, 10, 39, '{3180afb7-080d-4382-91fc-4a30c1ddd001}', '{3180afb7-080d-4382-91fc-4a30c1ddd001}', '0101000020110F0000194517B8A0782F41B111A37DBD8C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (66, 5, 'Apis Mellifera Carnica', '1000', 'Mac Moneysack', 'DCIM/3.jpg', true, 'EFB', 20, 45, '{555a66d9-4a35-4bbd-bf70-e790b35a2a57}', '{555a66d9-4a35-4bbd-bf70-e790b35a2a57}', '0101000020110F00007E770BD699732F41B766F2DD858C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (67, 4, 'Apis Mellifera Carnica', '100000', 'David Signer', NULL, false, NULL, NULL, NULL, '{54bb48fc-e197-4243-b1c3-564e79c37809}', NULL, '0101000020110F0000E14876C65E732F4111DC0C905D8C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (68, 6, 'Apis Mellifera Carnica', '100000', 'Sid Hochwind', NULL, false, NULL, NULL, NULL, '{d24d0742-0918-4146-bf95-853c5e956483}', NULL, '0101000020110F00009463420445742F414AD80A38638C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (69, 6, 'Apis Mellifera Carnica', '100000', 'Pete Penika', NULL, true, NULL, NULL, NULL, '{d24d0742-0918-4146-bf95-853c5e956483}', NULL, '0101000020110F00009463420445742F414AD80A38638C5641');
INSERT INTO public.apiary (fid, nbr_of_boxes, bee_species, bee_amount, beekeeper, picture, disease, kind_of_disease, average_harvest, area_id, uuid, field_uuid, geom) VALUES (70, 6, 'Apis Mellifera Mellifera', '1000', 'Pete Penika', NULL, true, NULL, NULL, NULL, '{d24d0742-0918-4146-bf95-853c5e956483}', NULL, '0101000020110F00009463420445742F414AD80A38638C5641');


--
-- Data for Name: fields; Type: TABLE DATA; Schema: public; Owner: admin1
--

INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (28, 'national', 'lavender', 'DCIM/lavender.jpg', '2019-05-23', 'Marco Bernasocchi', '{524bd130-62e6-4644-bf6c-a63d52d6e1dc}', '0103000020110F0000010000000C000000C728F7BE0C722F41D5DC1A43178C5641D723A388F6712F41DD0DA2AB178C564109B1300310722F41CFBB34320A8C564192E2899C23722F4180AEAE21018C564184FE0AFDE4722F419E9509BF078C5641160A2A4FD5722F413C16D4850A8C5641462FB160B1722F4116CB122D0B8C56410D2D1C9075722F41284D39120C8C5641162780D948722F4168B55A420F8C56411F01469225722F41BF26470E118C5641E3656FAA10722F41A4B6EFCF148C5641C728F7BE0C722F41D5DC1A43178C5641');
INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (29, 'private', 'taraxacum', 'DCIM/taraxacum.jpg', '2019-05-23', 'Marco Bernasocchi', '{bf29c1ea-9d21-4b03-af69-a32ad6dd316f}', '0103000020110F0000010000000C00000035900A5656732F41F5E587E6268C5641362A4CB065732F4128E9745E248C56415EA990BD83732F4115DD83DB208C5641C18F28FE97732F4193993100228C5641D124162291732F41F9D2EA42158C5641A9A5D11473732F4122C88E4D178C5641ABC46F6233732F41C0AE17BA0A8C5641268079250E732F413D6BC5DE0B8C56418111A8A0F6722F418F550DF40F8C5641B49E351B10732F41E2D99663238C56412CA15F361B732F41D5216B44258C564135900A5656732F41F5E587E6268C5641');
INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (30, 'cantonal', 'grass', 'DCIM/grass.jpg', '2019-05-23', 'Denis Rouzard', '{1ea9bdf9-8646-4d7d-adc8-5cde5d28478e}', '0103000020110F0000010000000B00000092E2899C23722F4180AEAE21018C564184FE0AFDE4722F419E9509BF078C5641199AD8C9FF722F4188B1557C048C56411F4A87BD0D732F4136D28ADE028C564190F3D4563B732F41852FF440FF8B5641B2754D3B3E732F4159FC2BC9FE8B564128AAE4A429732F41D58C5DA3FB8B5641F81B235C07732F4156AEE68FFF8B5641C0025B5A43722F41F0CC2EE2F98B5641A50DAD7D29722F4191EF39ABFE8B564192E2899C23722F4180AEAE21018C5641');
INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (31, 'national', 'grass', 'DCIM/grass.jpg', '2019-05-23', 'Marco Bernasocchi', '{b9ab211a-9ac5-4e1d-acec-340fac9dea68}', '0103000020110F0000010000000B000000C523CA6BAD6E2F419D132B0FEC8B5641A124935AEB6E2F4140A79569018C56418C6655A40E702F412E124574038C564181873649A0702F419184DFCFD78B5641E44BE6FFAD6F2F41139CD6FBD38B5641D83D5B5CAF6F2F4109E62252DC8B5641991E0C95996F2F41B3D7695CE28B5641DF99B6D4746F2F41BA0A1B2BE58B564146666A692F6F2F4138F323FFE88B5641A4D2C016CE6E2F411A504866EC8B5641C523CA6BAD6E2F419D132B0FEC8B5641');
INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (32, 'private', 'lavender', 'DCIM/lavender.jpg', '2019-05-23', 'Marco Bernasocchi', '{c25a0d8a-802d-45d5-a8a4-8853def4d0e4}', '0103000020110F00000100000017000000E44BE6FFAD6F2F41139CD6FBD38B564117DCE987AF6F2F41088A88E1CA8B564168114AE0D86F2F41011888B0CA8B5641F0059C18FC6F2F4132262AFDC68B564151CC89EF18702F41E502E252BA8B5641C023054529702F41F3936FD1AD8B56411861EB8A67702F418F7368C1AA8B5641E0F54C26B6702F41EC60BE02AB8B56412B232791CA702F4170FA166AAC8B56416794489CFD702F4190F5C9FCAF8B5641044DD92117712F416CCB2812B48B5641E1788EF9F6702F4112E29547BE8B56418EEDD7DEF1702F4112E29547BE8B5641FF9AA9F6DD702F41652EFDFFC58B5641AC0FF3DBD8702F41A3E1504CC58B5641537CB6D3BE702F41C31B5341C68B5641E0F54C26B6702F414EE85C77CA8B56416F487B0ECA702F411C190A8DCB8B5641EE5BAFC3BB702F41A7E513C3CF8B5641709ED1D0A5702F4187AB11CECE8B5641BC2102FE95702F4183BB72D8D38B564193BC5BA596702F41342FE1A8D78B5641E44BE6FFAD6F2F41139CD6FBD38B5641');
INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (33, 'cantonal', 'colza', 'DCIM/colza.jpg', '2019-05-23', 'Marco Bernasocchi', '{5e5c21c9-68ca-4560-8498-484084c266da}', '0103000020110F00000100000005000000E02035F087712F41932598ECE38B56416B178A6F3E712F41E3AC8B90DE8B564143BEFA2C4A712F41827C2376D68B56419ED47808A5712F4158E0815ADA8B5641E02035F087712F41932598ECE38B5641');
INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (34, 'national', 'taraxacum', 'DCIM/taraxacum.jpg', '2019-05-23', 'Matthias Kuhn', '{c7407437-2fec-4871-a45f-a940c7de458e}', '0103000020110F000001000000080000009ED47808A5712F4158E0815ADA8B56419C5D984D4C712F41C0E6778DD68B5641797DEC0C44712F418C5C6161CC8B56416AC133AD62712F41BF1790CABB8B564107246E70A0712F41273C5A51C98B5641D0647C90A6712F41D7F3B50FCC8B564184E14B63B6712F410312B966CD8B56419ED47808A5712F4158E0815ADA8B5641');
INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (35, 'private', 'grass', 'DCIM/grass.jpg', '2019-05-23', 'Marco Bernasocchi', '{285817c0-5c64-4451-acfb-80897818aa39}', '0103000020110F0000010000000900000091E47D0894712F41F06B14F2DF8B5641B51BF928DC712F410B9CA08FE78B5641241D1EBC10722F41B93B1556D08B5641BFFC16AC0D722F412DAE5A82C98B564166BF3066CF712F412DAE5A82C98B564126C9CA40C8712F41E3D7B671CC8B564184E14B63B6712F410312B966CD8B56419ED47808A5712F4158E0815ADA8B564191E47D0894712F41F06B14F2DF8B5641');
INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (36, 'national', 'grass', 'DCIM/grass.jpg', '2019-05-23', 'Marco Bernasocchi', '{7dd3d29c-8dec-4e1f-aad7-a218b937b5df}', '0103000020110F0000010000000C00000014DE2389EE712F413379513D588C5641F141F7E703722F41634BB94C4F8C5641CE8F645A78722F41660955C5598C5641B59C37B589722F41605805325C8C5641C2AC4390B3722F41B9940BE05E8C5641FB71B8162F732F41FD8E347C708C5641D295E700FA722F414A699B03788C5641C75E73BBEC722F413223FB7A7C8C5641677320C17F722F415D02AF6F808C56413235B0844F722F41E58BA6CC7C8C5641070389AC3E722F419933A1807A8C564114DE2389EE712F413379513D588C5641');
INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (37, 'cantonal', 'taraxacum', 'DCIM/taraxacum.jpg', '2019-05-23', 'Marco Bernasocchi', '{679cdd75-45b1-425b-8242-d86d874d7eba}', '0103000020110F00000100000010000000AD426193BF712F419DF734FD4B8C56418E9D04C397712F41E94F3A494E8C56414AA3DB2686712F41C06AB70A4D8C56414C245DCA4F712F41B3904869548C5641DB768BB263712F41867CD7D25A8C56417EB4605251712F417F146962628C56415503BB1D0A712F41EDDFF0BB658C56416B9CCE8912712F41907E8E76728C5641633EF2D921712F41DCD693C2748C564112367D925D712F416BD28B50718C564196FBE07288712F4173879E5A798C564108571F3FA3712F41B534235D7B8C5641C9B8CF0BEF712F41DBE025837C8C5641AA405E4C2C722F41D56E25527C8C5641F69863982E722F4157861C7E788C5641AD426193BF712F419DF734FD4B8C5641');
INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (38, 'private', 'weed', 'DCIM/weed.jpg', '2019-05-23', 'Marco Bernasocchi', '{3ecb3e04-b1a8-4b82-9f96-88579f221364}', '0103000020110F0000010000000E000000C4A6BBB97E7A2F415B3729FA598D56410D515451527A2F4126641399508D56418EE7C9D9847A2F419C9709634C8D5641A4D473D8F1792F418E5CD2A6348D5641BBEE08E8C3792F411F9BDC0D398D5641797561EF7B792F4179C3BE3A2C8D564101979E38047A2F416CDFBDD82B8D56417EFE25693E7A2F41589DE0C63A8D56416C9A40E4937B2F4167C4F301438D5641939D7964AC7B2F41242101C0488D5641683E677B367B2F41D4990D1C4E8D5641FFEE71133B7B2F4120F21268508D5641BC48DF098E7A2F41CD3B316C5D8D5641C4A6BBB97E7A2F415B3729FA598D5641');
INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (39, 'national', 'taraxacum', 'DCIM/taraxacum.jpg', '2019-05-23', 'David Signer', '{cbcf9a6e-74fa-41eb-90dc-1dc0590a8143}', '0103000020110F0000010000000B000000EA439EF901772F41DEB6DA39CA8C5641E614B0A109772F41B4DBE9BBD08C5641A19905622E772F4122B103D6DB8C5641822194A26B772F419C6A1E52E78C564102B8092B9E772F41E4933546F18C56418EAE5EAA54772F413E134ECDFB8C56410D18E92122772F41D0A88E8E178D5641A214477692752F4120308232128D5641AEA1117E7B752F41D0E6FD58D98C5641F5A37DF085762F41B61EFC94D88C5641EA439EF901772F41DEB6DA39CA8C5641');
INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (40, 'cantonal', 'lavender', 'DCIM/lavender.jpg', '2019-05-23', 'Mario Baranzini', '{064a2978-83fe-4e93-ab3c-1ed5b2a76258}', '0103000020110F0000010000000F00000067B1A1E302772F412207C400CB8C56411DD4A18103772F41D1D2D9D7C98C5641EA439EF901772F415719BF5BBE8C564140A8ECA923772F413081E0B6CC8C5641298E579A51772F4128230407DC8C5641A2F3DB83F8772F41E6D6471FF98C5641293767815E772F4103858164FA8C5641095A2AAA9A772F415AE7AAC6F18C564102B8092B9E772F41E4933546F18C5641822194A26B772F419C6A1E52E78C564120A7F65841772F41AAE35064DF8C5641A19905622E772F4122B103D6DB8C5641FD7E6BB717772F41C02325FDD48C5641E614B0A109772F41B4DBE9BBD08C564167B1A1E302772F412207C400CB8C5641');
INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (41, 'national', 'grass', 'DCIM/grass.jpg', '2019-05-23', 'Marco Bernasocchi', '{6d0b747b-7b1d-4c58-91ea-b1272d15805f}', '0103000020110F0000010000000B0000002735F1E185772F413B71DF17908C564113B2043E20772F4101796D1F968C5641C6403862C8762F41F7C32DFE6C8C5641EBBF4F7764762F41A49DA8464F8C56416188EB6539762F415E13F64E478C564120FEBFB394762F417A14782B488C56417133200CBE762F4123251617558C5641AD0DDCC2A0762F41A3A73A07578C5641159EE0BC10772F41A0D914CA7D8C5641BAF47CEC53772F4175552D8F7A8C56412735F1E185772F413B71DF17908C5641');
INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (42, 'private', 'grass', 'DCIM/grass.jpg', '2020-05-25', 'Dave Signer', '{84ac5af7-3e70-4fe8-8eeb-b10674ffeb96}', '0103000020110F0000010000000A000000817D251D24702F413CDE4174188C564115A88E0317702F41F8B43FB0228C5641254D9713EE6F2F414CFE7247228C5641366DC8E6F76F2F4165AE3C04318C5641B26290D30E702F41C063A24A348C5641B2DDB89641702F416E3ED5ED358C5641AAC848706F702F4118E36EB9358C5641E43D4C105F702F414BEC3FAA218C5641572853503E702F413BCC0ED7178C5641817D251D24702F413CDE4174188C5641');
INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (43, 'private', 'taraxacum', 'DCIM/taraxacum.jpg', '2020-05-25', 'Dave Signer', '{ea7515cc-ec4a-46f1-84d5-9c05de1f5bf5}', '0103000020110F00000100000007000000AAC848706F702F4118E36EB9358C564107AB5172C5702F4146730A3C2E8C56417919810AD6702F412F377AC42A8C5641B5AFFE9BF4702F415D680D69248C56416D22B02C0A712F41BD16B4CC1D8C5641E43D4C105F702F414BEC3FAA218C5641AAC848706F702F4118E36EB9358C5641');
INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (44, 'private', 'weed', 'DCIM/weed.jpg', '2020-05-25', 'Dave Signer', '{1686c3c2-d574-4406-afc2-fbd9f2b1499e}', '0103000020110F00000100000008000000572853503E702F413BCC0ED7178C5641E43D4C105F702F414BEC3FAA218C5641C826DA2E0A712F41BE16B4CC1D8C5641683ED5A9AD702F410F6328E4178C56415AEB478873702F415CA55CE0128C5641413B7ECB64702F41B5BFF53A158C5641C445B6DE4D702F41B97E2861178C5641572853503E702F413BCC0ED7178C5641');
INSERT INTO public.fields (fid, proprietor, plant_species, picture, review_date, reviewer, uuid, geom) VALUES (45, 'national', 'lavender', 'DCIM/lavender.jpg', '2020-05-25', 'Maya Mielina', '{cb5e0f73-eff6-46e7-953a-f72d468e766c}', '0103000020110F00000100000009000000FE0C57FC3F732F418835FE846F8C5641E93FD9533E732F41B30776256E8C56417A2667CA27732F41B30776256E8C56410E4B56820C732F411DFF60ED688C564150B4EDFA1A732F41BD2E4629658C5641505C75CEE1722F41FC09B8FD618C5641DF5DD96FCD722F41B50D5190628C5641FB71B8162F732F41FD8E347C708C5641FE0C57FC3F732F418835FE846F8C5641');


--
-- Data for Name: spatial_ref_sys; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Name: apiary_fid_seq; Type: SEQUENCE SET; Schema: public; Owner: admin1
--

SELECT pg_catalog.setval('public.apiary_fid_seq', 70, true);


--
-- Name: fields_fid_seq; Type: SEQUENCE SET; Schema: public; Owner: admin1
--

SELECT pg_catalog.setval('public.fields_fid_seq', 45, true);


--
-- Name: apiary apiary_pkey; Type: CONSTRAINT; Schema: public; Owner: admin1
--

ALTER TABLE ONLY public.apiary
    ADD CONSTRAINT apiary_pkey PRIMARY KEY (fid);


--
-- Name: fields fields_pkey; Type: CONSTRAINT; Schema: public; Owner: admin1
--

ALTER TABLE ONLY public.fields
    ADD CONSTRAINT fields_pkey PRIMARY KEY (fid);


--
-- Name: apiary_geom_geom_idx; Type: INDEX; Schema: public; Owner: admin1
--

CREATE INDEX apiary_geom_geom_idx ON public.apiary USING gist (geom);


--
-- Name: fields_geom_geom_idx; Type: INDEX; Schema: public; Owner: admin1
--

CREATE INDEX fields_geom_geom_idx ON public.fields USING gist (geom);


--
-- PostgreSQL database dump complete
--

