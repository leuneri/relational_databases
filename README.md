
# Summary

This database models characteristics of events and players in the competitive Valorant esports scene. This database could be used by coaches, fans, and analysts to analyse and predict future outcomes. People using the database will be able to query for and view previous events and the organisations participating in them, as well as in-game stats from the matches. 

# Timeline
- March 19th
   - Completed first prototypes of application and server. Does not yet need to be fully functional
- March 26th
   - Front/back ends completed individually
   - Start connecting front/back ends
- March 31st
   - Front/back ends of application are connected and application is functional
   - Start writing SQL to create tables and populate database
- April 5th
   - Milestone 4, project deadline
   - Start working on demo presentation
- April 11th
   - Milestone 5, group demo


# Tasks
- Front end
   - Application front end in React
- Back end
   - PHP to connect our application to MySQL
   - Set up Oracle HTTP server
   - Write SQL script to populate DB with tables and data

# Task Assignment
- Backend: Anna Wang + Eric Leung
- Frontend: Chris Lee

# Usage
- Go to terminal and ssh into the UBC undergrad servers
- Create a directory called "public_html" by inputting `mkdir ~/public_html`
- Under the public_html folder, input `git clone https://github.students.cs.ubc.ca/CPSC304-2022W-T2/project_f0u2b_m5e3b_s8i3b.git`
- In the same terminal, input the following commands:
  - `chmod 711 ~/public_html/project_f0u2b_m5e3b_s8i3b/src/frontend/Search.php`
  - `chmod 711 ~/public_html/project_f0u2b_m5e3b_s8i3b/src/frontend/Organizations.php`
  - `chmod 711 ~/public_html/project_f0u2b_m5e3b_s8i3b/src/frontend/Players.php`
  - `chmod 711 ~/public_html/project_f0u2b_m5e3b_s8i3b/src/frontend/style.css`
- Go to https://www.students.cs.ubc.ca/~[CWLusername]/project_f0u2b_m5e3b_s8i3b/src/frontend/[YourDesiredPage] to test out our app!
  - e.g. To go to the Organizations page, go to https://www.students.cs.ubc.ca/~[CWLusername]/project_f0u2b_m5e3b_s8i3b/src/frontend/Organizations.php
