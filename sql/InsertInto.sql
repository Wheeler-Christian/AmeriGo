/* ===== Insert data into those tables ======================================================================================================================== */
/* 1. The STATES table references NOBODY */
INSERT INTO states		
(state_id, state_name)
VALUES
('CA', 'California'),
('CO', 'Colorado'),
('ID', 'Idaho'),
('NV', 'Nevada'),
('OR', 'Oregon'),
('UT', 'Utah');

/* 2. The STATIONS table references STATES */
INSERT INTO stations										
(station_id, city, state_id)
VALUES
('BMC', 'Brigham City',     'UT'),
('BOI', 'Boise',            'ID'),
('BVR', 'Beaver',           'UT'),
('CDC', 'Cedar City',       'UT'),
('DEN', 'Denver',           'CO'),
('EKO', 'Elko',             'NV'),
('GJT', 'Grand Junction',   'CO'),
('GRB', 'Granby',           'CO'),
('GWS', 'Glenwood Springs', 'CO'),
('HLP', 'Helper',           'UT'),
('LAS', 'Las Vegas',        'NV'),
('LAX', 'Los Angeles',      'CA'),
('MUO', 'Mountain Home',    'ID'),
('NEP', 'Nephi',            'UT'),
('OGD', 'Ogden',            'UT'),
('PDT', 'Pendleton',        'OR'),
('PDX', 'Portland',         'OR'),
('PIH', 'Pocatello',        'ID'),
('PVU', 'Provo',            'UT'),
('RNO', 'Reno',             'NV'),
('RVR', 'Green River',      'UT'),
('SAC', 'Sacramento',       'CA'),
('SAN', 'San Diego',        'CA'),
('SFO', 'San Francisco',    'CA'),
('SGU', 'St. George',       'UT'),
('SHO', 'Shoshone',         'ID'),
('SLC', 'Salt Lake City',   'UT'),
('WEN', 'Wendover',         'UT'),
('WMC', 'Winnemucca',       'NV');

/* 3. The CORRIDORS table references NOBODY */
INSERT INTO corridors		
(corridor_id, corridor)
VALUES
(1, 'Green'),
(2, 'Blue'),
(3, 'Red'),
(4, 'Orange'),
(255, 'Other');

/* 4. The "corridor_directions" table references "corridors" and "stations" */
INSERT INTO directions
(direction_id, corridor_id, eotl)
VALUES
(1.1, 1, 'BOI'),
(1.2, 1, 'SLC'),
(2.1, 2, 'DEN'),
(2.2, 2, 'SLC'),
(3.1, 3, 'SLC'),
(3.2, 3, 'LAS'),
(4.1, 4, 'SLC'),
(4.2, 4, 'RNO');

/* 5. The SEGMENTS table references STATIONS and "corridor_directions" */
INSERT INTO segments					
(segment_id, direction_id, station1, station2, available_seats, distance)
VALUES
(1,  1.1, 'SLC', 'OGD', 100, 38.3),
(2,  1.1, 'OGD', 'BMC', 100, 21.3),
(3,  1.1, 'BMC', 'PIH', 100, 109),
(4,  1.1, 'PIH', 'SHO', 100, 116),
(5,  1.1, 'SHO', 'MUO', 100, 73.7),
(6,  1.1, 'MUO', 'BOI', 100, 43.8),
(7,  2.1, 'SLC', 'PVU', 100, 45.3),
(8,  2.1, 'PVU', 'HLP', 100, 67.9),
(9,  2.1, 'HLP', 'RVR', 100, 71.4),
(10, 2.1, 'RVR', 'GJT', 100, 102),
(11, 2.1, 'GJT', 'GWS', 100, 86.9),
(12, 2.1, 'GWS', 'GRB', 100, 109),
(13, 2.1, 'GRB', 'DEN', 100, 86.1),
(14, 3.1, 'LAS', 'SGU', 100, 120),
(15, 3.1, 'SGU', 'CDC', 100, 52.2),
(16, 3.1, 'CDC', 'PVU', 100, 208),
(17, 3.1, 'PVU', 'SLC', 100, 45.3),
(18, 4.1, 'RNO', 'WMC', 100, 166),
(19, 4.1, 'WMC', 'EKO', 100, 124),
(20, 4.1, 'EKO', 'SLC', 100, 230),
(21, 1.2, 'BOI', 'MUO', 100, 43.8),
(22, 1.2, 'MUO', 'SHO', 100, 73.7),
(23, 1.2, 'SHO', 'PIH', 100, 116),
(24, 1.2, 'PIH', 'BMC', 100, 109),
(25, 1.2, 'BMC', 'OGD', 100, 21.3),
(26, 1.2, 'OGD', 'SLC', 100, 38.3),
(27, 2.2, 'DEN', 'GRB', 100, 86.1),
(28, 2.2, 'GRB', 'GWS', 100, 109),
(29, 2.2, 'GWS', 'GJT', 100, 86.9),
(30, 2.2, 'GJT', 'RVR', 100, 102),
(31, 2.2, 'RVR', 'HLP', 100, 71.4),
(32, 2.2, 'HLP', 'PVU', 100, 67.9),
(33, 2.2, 'PVU', 'SLC', 100, 45.3),
(34, 3.2, 'SLC', 'PVU', 100, 45.3),
(35, 3.2, 'PVU', 'CDC', 100, 208),
(36, 3.2, 'CDC', 'SGU', 100, 52.2),
(37, 3.2, 'SGU', 'LAS', 100, 120),
(38, 4.2, 'SLC', 'EKO', 100, 230),
(39, 4.2, 'EKO', 'WMC', 100, 124),
(40, 4.2, 'WMC', 'RNO', 100, 166);


/* 6. The CUSTOMERS table references NOBODY */
INSERT INTO CUSTOMERS
(first_name, last_name, email, phone, dob, user_level, active, pass, registration_date)
VALUES
('Wolfgang',    'Mozart',           'mozart@gmail.com',     '660-501-8669', '1756-1-27',    1,	NULL,	SHA2('wolfgang', 512),  NOW()),
('Ludwig',      'van Beethoven',    'beethoven@gmail.com',  '228-548-6059', '1770-12-16',   1,	NULL, 	SHA2('ludwig', 512),	NOW()),
('Clara',       'Schumann',         'schumann@gmail.com',   '304-594-4156', '1819-9-13',    1,	NULL, 	SHA2('clara', 512),	    NOW()),
('Johann',      'Bach',             'bach@gmail.com',       '308-458-4844', '1685-3-31',	1,	NULL,	SHA2('johann', 512),	NOW()),
('George',		'Washington',       'washington@gmail.com', '703-555-0195', '1732-2-22',    2,	NULL,	SHA2('george', 512),	NOW());

/* 7. The TICKETS table references CUSTOMERS */
/* Starts empty because no purchases yet */

/* 8. The TICKET_SEGMENTS table is an "intersection table" that references TICKETS and SEGMENTS */
/* Starts empty because no purchases yet */