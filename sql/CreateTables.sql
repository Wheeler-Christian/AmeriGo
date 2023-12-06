/* ===== Create tables ======================================================================================================================== */
/* 1. The "states" table references NOBODY */
CREATE TABLE states (
	state_id CHAR(2) NOT NULL,
	state_name VARCHAR(20) NOT NULL,
PRIMARY KEY(state_id)
);

/* 2. The "stations" table references "states" */
CREATE TABLE stations (
	station_id CHAR(3) NOT NULL,
	city VARCHAR(30) NOT NULL,
	state_id CHAR(2) NOT NULL,
PRIMARY KEY(station_id),
FOREIGN KEY(state_id)
	REFERENCES states(state_id)
);

/* 3. The "corridors" table references NOBODY */
CREATE TABLE corridors (
	corridor_id TINYINT UNSIGNED NOT NULL,
	corridor VARCHAR(10) NOT NULL,
PRIMARY KEY(corridor_id)
);

/* The "directions" table references "corridors" and "stations" */
CREATE TABLE directions (
	direction_id DECIMAL(3,1) UNSIGNED NOT NULL,
	corridor_id TINYINT UNSIGNED NOT NULL,
	eotl CHAR(3) NOT NULL,
PRIMARY KEY(direction_id),
FOREIGN KEY(corridor_id)
	REFERENCES corridors(corridor_id),
FOREIGN KEY(eotl)
	REFERENCES stations(station_id)
);

/* 5. The "segments" table references "stations" and "directions" */
CREATE TABLE segments (
	segment_id INTEGER UNSIGNED NOT NULL,
	direction_id DECIMAL(3,1) UNSIGNED NOT NULL,
	station1 CHAR(3) NOT NULL,
	station2 CHAR(3) NOT NULL,
	available_seats SMALLINT UNSIGNED NOT NULL,
	distance DECIMAL(4,1) UNSIGNED NOT NULL,
PRIMARY KEY(segment_id),
FOREIGN KEY(direction_id)
	REFERENCES directions(direction_id),
FOREIGN KEY(station1)
	REFERENCES stations(station_id),
FOREIGN KEY(station2)
	REFERENCES stations(station_id)	
);

/* 6. The "customers" table references NOBODY */
/* The customers table is already in the database,
 * so we don't need to create it here */

/* 7. The "tickets" table references "customers" */
CREATE TABLE tickets (
	ticket_id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	customer_id MEDIUMINT(8) UNSIGNED NOT NULL,
	quantity SMALLINT UNSIGNED NOT NULL,
	date_purchased DATETIME NOT NULL,
PRIMARY KEY(ticket_id),
FOREIGN KEY(customer_id)
	REFERENCES customers(customer_id)
);

/* 8. The "ticket_segments" table is an INTERSECTION TABLE that references "tickets" and "segments" */
CREATE TABLE ticket_segments (
	ticket_id INTEGER UNSIGNED NOT NULL,
	segment_id INTEGER UNSIGNED NOT NULL,
PRIMARY KEY(ticket_id, segment_id),
FOREIGN KEY(ticket_id)
	REFERENCES tickets(ticket_id),
FOREIGN KEY(segment_id)
	REFERENCES segments(segment_id)
);


