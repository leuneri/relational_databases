DROP TABLE OrganizationID;
DROP TABLE Organization;
DROP TABLE HistoricalMatchup;
DROP TABLE ParticipatingIn;
DROP TABLE TeamMemberContract;
DROP TABLE Contract;
DROP TABLE Coach;
DROP TABLE Player;
DROP TABLE Weapon;
DROP TABLE UsesWeapon;
DROP TABLE Event;
DROP TABLE StagedEvent;
DROP TABLE OnlineEvent;
DROP TABLE SeriesInEvent;
DROP TABLE MatchInSeries;
DROP TABLE Map;
DROP TABLE MapPicks;
DROP TABLE MapPlayed;
DROP TABLE Matchup;
DROP TABLE Agent;
DROP TABLE AgentPlayed;
DROP TABLE AvgCombatScore;

CREATE TABLE OrganizationID (
	o_id INTEGER,
	name CHAR(255) NOT NULL UNIQUE,
	PRIMARY KEY (o_id)
);
grant select on OrganizationID to public;

CREATE TABLE Organization (
	name CHAR(255) NOT NULL PRIMARY KEY,
	ranking INTEGER UNIQUE,
	region CHAR(255) NOT NULL,
	win_rate REAL
);
grant select on Organization to public;

CREATE TABLE HistoricalMatchup (
	o_id1 INTEGER,
	o_id2 INTEGER,
	win_loss_ratio CHAR(255),
	PRIMARY KEY (o_id1, o_id2)
);
grant select on HistoricalMatchup to public;

CREATE TABLE ParticipatingIn (
	o_id INTEGER,
	e_id INTEGER,
	final_placement INTEGER UNIQUE,
	PRIMARY KEY (o_id, e_id),
	FOREIGN KEY (o_id) REFERENCES Organization (name) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (e_id) REFERENCES Event (e_id) ON DELETE SET NULL ON UPDATE CASCADE
);
grant select on ParticipatingIn to public;

CREATE TABLE TeamMemberContract (
	tm_id INTEGER,
	in_game_name CHAR(255) NOT NULL UNIQUE,
	real_name CHAR(255) UNIQUE,
	start_date DATE,
	end_date DATE,
	salary INTEGER,
	o_id INTEGER,
	PRIMARY KEY (tm_id),
	FOREIGN KEY (o_id) REFERENCES OrganizationID (o_id) ON DELETE SET NULL ON UPDATE CASCADE
);
grant select on TeamMemberContract to public;

CREATE TABLE Contract (
	start_date DATE,
	end_date DATE,
	currently_active BOOL,
	PRIMARY KEY (start_date, end_date)
);
grant select on Contract to public;

CREATE TABLE Coach (
	tm_id INTEGER PRIMARY KEY,
	FOREIGN KEY (tm_id) REFERENCES TeamMemberContract (tm_id) ON DELETE CASCADE ON UPDATE CASCADE
);
grant select on Coach to public;

CREATE TABLE Player (
	tm_id INTEGER PRIMARY KEY,
	rank CHAR(255),
	role CHAR(255),
	FOREIGN KEY (tm_id) REFERENCES TeamMemberContract (tm_id) ON DELETE CASCADE ON UPDATE CASCADE
);
grant select on Player to public;

CREATE TABLE Weapon (
	weapon_name CHAR(255) PRIMARY KEY,
	damage INTEGER
);
grant select on Weapon to public;

CREATE TABLE UsesWeapon (
	tm_id INTEGER,
	weapon_name CHAR(255),
	average_damage_per_round REAL,
	headshot_percentage REAL,
	PRIMARY KEY (tm_id, weapon_name),
	FOREIGN KEY (tm_id) REFERENCES TeamMemberContract (tm_id) ON DELETE CASCADE ON UPDATE NO ACTION,
	FOREIGN KEY (weapon_name) REFERENCES Weapon (weapon_name) ON DELETE SET NULL ON UPDATE SET NULL
);
grant select on UsesWeapon to public;

CREATE TABLE Event (
	e_id INTEGER PRIMARY KEY,
	name CHAR(255) NOT NULL,
	start_date DATE NOT NULL,
	end_date DATE NOT NULL,
	winning_organization INTEGER,
	prize_pool REAL NOT NULL,
	FOREIGN KEY (winning_organization) REFERENCES OrganizationID (o_id)
);
grant select on Event to public;

CREATE TABLE StagedEvent (
	e_id INTEGER PRIMARY KEY,
	num_attendees INTEGER,
	venue_location CHAR(255) NOT NULL
);
grant select on StagedEvent to public;

CREATE TABLE OnlineEvent (
	e_id INTEGER PRIMARY KEY,
	num_viewers INTEGER,
	broadcast_platform CHAR(255) NOT NULL
);
grant select on OnlineEvent to public;

CREATE TABLE SeriesInEvent (
	s_id INTEGER PRIMARY KEY,
	date DATE NOT NULL,
	winning_organization INTEGER,
	e_id INTEGER,
	FOREIGN KEY (e_id) REFERENCES Event (e_id) ON DELETE SET NULL ON UPDATE SET NULL,
	FOREIGN KEY (winning_organization) REFERENCES OrganizationID (o_id)
);
grant select on SeriesInEvent to public;

CREATE TABLE MatchInSeries (
	m_id INTEGER PRIMARY KEY,
	num_rounds INTEGER,
	winning_organization INTEGER,
	scoreline CHAR(255),
	s_id INTEGER,
	FOREIGN KEY (s_id) REFERENCES SeriesInEvent (s_id) ON DELETE SET NULL ON UPDATE CASCADE,
	FOREIGN KEY (winning_organization) REFERENCES OrganizationID (o_id)
);
grant select on MatchInSeries to public;

CREATE TABLE Map (
	map_name CHAR(255) PRIMARY KEY
);
grant select on Map to public;
 
 CREATE TABLE MapPicks (
    map_name CHAR(255),
    o_id INTEGER,
    num_times_played INTEGER,
    PRIMARY KEY (map_name, o_id),
    FOREIGN KEY (map_name) REFERENCES Map ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (o_id) REFERENCES OrganizationID ON DELETE CASCADE ON UPDATE CASCADE
);
grant select on MapPicks to public;

CREATE TABLE MapPlayed (
    m_id INTEGER,
    map_name CHAR(255) NOT NULL,
    starting_side CHAR(255) NOT NULL,
    PRIMARY KEY (m_id, map_name),
    FOREIGN KEY (m_id) REFERENCES MatchInSeries ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (map_name) REFERENCES Map ON DELETE SET NULL ON UPDATE CASCADE
);
grant select on MapPlayed to public;

CREATE TABLE Matchup (
    s_id INTEGER,
    o_id INTEGER,
    PRIMARY KEY (s_id, o_id),
    FOREIGN KEY (s_id) REFERENCES SeriesInEvent ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (o_id) REFERENCES OrganizationID ON DELETE CASCADE ON UPDATE CASCADE
);
grant select on Matchup to public;

CREATE TABLE Agent (
    agent_number INTEGER, 
    name CHAR(255) UNIQUE NOT NULL, 
    pick_rate REAL NOT NULL,
    PRIMARY KEY (agent_number)
);
grant select on Agent to public;

CREATE TABLE AgentPlayed (
    tm_id INTEGER, 
    m_id INTEGER, 
    agent_number INTEGER,
    PRIMARY KEY (tm_id, m_id, agent_number)
);
grant select on AgentPlayed to public;

CREATE TABLE AvgCombatScore (
    kills INTEGER, 
    deaths INTEGER, 
    assists INTEGER, 
    num_plants INTEGER, 
    num_defuses INTEGER, 
    num_rounds INTEGER, 
    average_combat_score INTEGER,
    PRIMARY KEY (kills, deaths, assists, num_plants, num_defuses, num_rounds)
);
grant select on AvgCombatScore to public;
 

INSERT INTO OrganizationID VALUES(1, ‘Sentinels’);
INSERT INTO OrganizationID VALUES(2, ‘LOUD’);
INSERT INTO OrganizationID VALUES(3, ‘Fnatic’); 
INSERT INTO OrganizationID VALUES(4, ‘Paper Rex’);
INSERT INTO OrganizationID VALUES(5, ‘NRG’);

INSERT INTO Organization VALUES(‘Sentinels’, 37, ‘Americas’, 0.48)
INSERT INTO Organization VALUES(‘LOUD’, 2, ‘Americas’, 0.87);
INSERT INTO Organization VALUES(‘Fnatic’, 6, ‘EMEA’, 0.74);
INSERT INTO Organization VALUES(‘Paper Rex’, 4, ‘Pacific’, 0.78);
INSERT INTO Organization VALUES(‘NRG’, 31, ‘Americas’, 0.56);

INSERT INTO HistoricalMatchup VALUES(5, 2, ‘0:1’);
INSERT INTO HistoricalMatchup VALUES(1, 5, ‘2:1’);
INSERT INTO HistoricalMatchup VALUES(3, 4, ‘0:1’);
INSERT INTO HistoricalMatchup VALUES(3, 1, ‘1:2’);
INSERT INTO HistoricalMatchup VALUES(4, 5, ‘0:0’);

INSERT INTO ParticipatingIn VALUES(1, 31, 1);
INSERT INTO ParticipatingIn VALUES(1, 1, 2);
INSERT INTO ParticipatingIn VALUES(2, NULL, 1);
INSERT INTO ParticipatingIn VALUES(3, NULL, 1);
INSERT INTO ParticipatingIn VALUES(4, 23, 1);

INSERT INTO TeamMemberContract VALUES(1, ‘TenZ’, ‘Tyson Ngo’, 2020-06-01, NULL, NULL, 1);
INSERT INTO TeamMemberContract VALUES(2, ‘aspas’, ‘Erick Santos’, 2022-01-01, NULL, NULL, 2);
INSERT INTO TeamMemberContract VALUES(3, ‘Enzo’, ‘Enzo Mestari’, 2022-05-09, 2022-11-30, NULL, 3);
INSERT INTO TeamMemberContract VALUES(4, ‘SyykoNT’, ‘Don Muir’, 2022-10-03, NULL, NULL, 1’);
INSERT INTO TeamMemberContract VALUES(5, ‘s0m’, ‘Sam Oh’, 2020-10-07, NULL, NULL, 5);
INSERT INTO TeamMemberContract VALUES(6, ‘f0rsakeN’, ‘Jason Susanto’, 2021-02-08, NULL, NULL, 4);

INSERT INTO Contract VALUES(2020-06-01, NULL, TRUE);
INSERT INTO Contract VALUES(2022-02-03, NULL, TRUE);
INSERT INTO Contract VALUES(2022-05-09, 2022-11-30, FALSE);
INSERT INTO Contract VALUES(2022-10-03, NULL, TRUE);
INSERT INTO Contract VALUES(2020-10-07, NULL, TRUE);

INSERT INTO Coach VALUES(4);
INSERT INTO Coach VALUES(7);
INSERT INTO Coach VALUES(8);
INSERT INTO Coach VALUES(9);
INSERT INTO Coach VALUES(10);

INSERT INTO Player VALUES(1, ‘Radiant #1’, ‘Duelist’);
INSERT INTO Player VALUES(2, ‘Radiant #1’, ‘Duelist’);
INSERT INTO Player VALUES(3, ‘Radiant #220’, ‘Initiator’);
INSERT INTO Player VALUES(5, ‘Radiant #24’, ‘Controller’);
INSERT INTO Player VALUES(6, ‘Radiant #1’, ‘Duelist’);

INSERT INTO Weapon VALUES(‘Sheriff’, 159)
INSERT INTO Weapon VALUES(‘Marshal’, 202)
INSERT INTO Weapon VALUES(‘Vandal’, 160)
INSERT INTO Weapon VALUES(‘Classic’, 78)
INSERT INTO Weapon VALUES(‘Guardian’, 195)

INSERT INTO UsesWeapon VALUES(1, ‘Vandal’, 177.1, 0.361);
INSERT INTO UsesWeapon VALUES(2, ‘Marshal’, 120.9, 0.247);
INSERT INTO UsesWeapon VALUES(3, ‘Classic’, 28.3, 0.338);
INSERT INTO UsesWeapon VALUES(5, ‘Sheriff’, 88.4, 0.561);
INSERT INTO UsesWeapon VALUES(6, ‘Vandal’, 175.9, 0.458);

INSERT INTO Event VALUES(1, ‘VCT 2023: LOCK//IN São Paulo’, 2023-02-13, 2023-03-04, NULL, 500000);
INSERT INTO Event VALUES(2, ‘VCT 2021: Stage 2 Masters - Reykjavík’, 2021-05-24, 2021-05-30, 1, 600000);
INSERT INTO Event VALUES(3, ‘2022: Stage 2 Masters - Copenhagen’, 2022-07-10, 2022-07-24, 77, 650000);
INSERT INTO Event VALUES(4, ‘VCT 2021: Brazil Stage 1 Masters’, 2021-03-13, 2021-03-21, 86, 45538);
INSERT INTO Event VALUES(5, ‘VCT 2022: Game Changers Korea’, 2022-09-22, 2022-09-23, 46, 7038);

INSERT INTO StagedEvent VALUES(1, 10332, ‘Ginásio do Ibirapuera’);
INSERT INTO StagedEvent VALUES(2, 4215, ‘Laugardalshöll’);
INSERT INTO StagedEvent VALUES(3, 6922, ‘Forum Copenhagen’);
INSERT INTO StagedEvent VALUES(6, 2352, ‘Marlene-Dietrich-Halle’);
INSERT INTO StagedEvent VALUES(8, 1702, ‘Volkswagen Arena’);

INSERT INTO OnlineEvent VALUES(1, 372184, ‘Twitch’);
INSERT INTO OnlineEvent VALUES(2, 488364, ‘Twitch’);
INSERT INTO OnlineEvent VALUES(3, 317604, ‘Twitch’);
INSERT INTO OnlineEvent VALUES(4, 372184, ‘Twitch’);
INSERT INTO OnlineEvent VALUES(5, 372184, ‘Twitch’);

INSERT INTO SeriesInEvent VALUES(1, 2023-02-19, 2, 1);
INSERT INTO SeriesInEvent VALUES(2, 2023-02-24, 3, 1);
INSERT INTO SeriesInEvent VALUES(3, 2021-05-30, 1, 2);
INSERT INTO SeriesInEvent VALUES(4, 2022-07-14, 8, 3);
INSERT INTO SeriesInEvent VALUES(5, 2022-07-17, 4, 3);

INSERT INTO MatchInSeries(1, 19, 3, ‘13:6’, 2);
INSERT INTO MatchInSeries(2, 20, 3, ‘13:7’, 2);
INSERT INTO MatchInSeries(3, 16, 2, ‘13:3’, 1);
INSERT INTO MatchInSeries(4, 28, 5, ‘15:13’, 1);
INSERT INTO MatchInSeries(5, 34, 2, ‘18:16’, 1);

INSERT INTO Map VALUES(‘Ascent’);
INSERT INTO Map VALUES(‘Fracture’);
INSERT INTO Map VALUES(‘Haven’);
INSERT INTO Map VALUES(‘Lotus’);
INSERT INTO Map VALUES(‘Pearl’);

INSERT INTO MapPicks VALUES(“Ascent”, 36, 25);
INSERT INTO MapPicks VALUES(“Haven”, 36, 11);
INSERT INTO MapPicks VALUES(“Ascent”, 13, 18);
INSERT INTO MapPicks VALUES(“Lotus”, 81, 2);
INSERT INTO MapPicks VALUES(“Breeze”, 48, 0);

INSERT INTO MapPlayed VALUES(1, ‘Haven’, ‘Defend’);
INSERT INTO MapPlayed VALUES(2, ‘Split’, ‘Defend’);
INSERT INTO MapPlayed VALUES(3, ‘Split’, ‘Attack’);
INSERT INTO MapPlayed VALUES(4, ‘Pearl’, ‘Defend’);
INSERT INTO MapPlayed VALUES(5, ‘Fracture’, ‘Attack’);

INSERT INTO Matchup VALUES(1, 2);
INSERT INTO Matchup VALUES(1, 1);
INSERT INTO Matchup VALUES(2, 3);
INSERT INTO Matchup VALUES(2, 4);
INSERT INTO Matchup VALUES(2, 2);

INSERT INTO Agent VALUES(4, ‘Killjoy’, 0.66);
INSERT INTO Agent VALUES(10, ‘Jett’, 0.55);
INSERT INTO Agent VALUES(6, ’Sova’, 0.47);
INSERT INTO Agent VALUES(13, ‘Breach’, 0.41);
INSERT INTO Agent VALUES(3, ‘Omen’, 0.41);

INSERT INTO Agent VALUES(4, ‘Killjoy’, 0.66);
INSERT INTO Agent VALUES(10, ‘Jett’, 0.55);
INSERT INTO Agent VALUES(6, ’Sova’, 0.47);
INSERT INTO Agent VALUES(13, ‘Breach’, 0.41);
INSERT INTO Agent VALUES(3, ‘Omen’, 0.41);

INSERT INTO AgentPlayed VALUES(4841, 8521, 10);
INSERT INTO AgentPlayed VALUES(574, 8522, 6);
INSERT INTO AgentPlayed VALUES(51, 7724, 1);
INSERT INTO AgentPlayed VALUES(89, 10553, 8);
INSERT INTO AgentPlayed VALUES(4841, 9921, 3);

INSERT INTO AvgCombatScore VALUES(20, 8, 8, 1, 5, 16, 346);
INSERT INTO AvgCombatScore VALUES(15, 11, 10, 2, 3, 19, 217);
INSERT INTO AvgCombatScore VALUES(32, 19, 6, 0, 2, 23, 395);
INSERT INTO AvgCombatScore VALUES(28, 14, 4, 0, 1, 21, 348);
INSERT INTO AvgCombatScore VALUES(25, 12, 7, 4, 1, 20, 359);

