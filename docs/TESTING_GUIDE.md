# ZapFlow Travel — Complete Role Testing Guide
# All Logins + Step-by-Step Testing Instructions

> PASSWORD FOR ALL ACCOUNTS: Password123!
> App URL: http://localhost:8000
> Run: php artisan serve  (then open the URL above)

===============================================================
PART 1 — LOGIN CREDENTIALS (All 10 Accounts)
===============================================================

INTERNAL STAFF
--------------
| Role           | Name               | Email                        | Password      |
|----------------|--------------------|------------------------------|---------------|
| Super Admin    | Zafar Khan         | superadmin@zapflow.test      | Password123!  |
| Admin          | Fatima Malik       | admin@zapflow.test           | Password123!  |
| Accountant     | Bilal Ahmed        | accountant@zapflow.test      | Password123!  |
| Visa Officer   | Ayesha Siddiqui    | visaofficer@zapflow.test     | Password123!  |
| Ops Staff      | Usman Tariq        | opsstaff@zapflow.test        | Password123!  |
| Ticketing Staff| Nadia Rauf         | ticketing@zapflow.test       | Password123!  |

EXTERNAL / CLIENT ACCOUNTS
---------------------------
| Role           | Name                    | Email                    | Password      |
|----------------|-------------------------|--------------------------|---------------|
| B2B Agent 1    | Al-Noor Travel Agency   | agent1@zapflow.test      | Password123!  |
| B2B Agent 2    | Barakah Hajj Group      | agent2@zapflow.test      | Password123!  |
| Sub-Agent      | Rahmat Sub-Agency       | subagent1@zapflow.test   | Password123!  |
| B2C Customer   | Hassan Iqbal            | customer@zapflow.test    | Password123!  |

===============================================================
PART 2 — DEMO DATA REFERENCE (what is in the database)
===============================================================

BOOKING GROUPS
--------------
| Group No.    | Name                      | Owner   | Passengers | Departure  |
|--------------|---------------------------|---------|------------|------------|
| BG-2026-001  | Demo Umrah Group Alpha    | Agent 1 | 4          | ~2 months  |
| BG-2026-002  | Demo Umrah Group Beta     | Agent 2 | 2          | ~3 months  |

PASSENGERS (BG-2026-001 — the main test group)
-----------------------------------------------
| Name           | Passport   | Visa Status      | Notes                        |
|----------------|------------|------------------|------------------------------|
| Ahmad Raza     | AA1234567  | draft            | Move through pipeline        |
| Kiran Bano     | BB9876543  | sent_to_embassy   | Already submitted            |
| Tariq Mehmood  | CC5544332  | mofa_received    | MoFA Ref: MOFA-2026-TM-004481|
| Sana Fatima    | DD1122334  | issued           | Fully processed              |

VOUCHERS
--------
| Voucher No.    | Status | Notes                      |
|----------------|--------|----------------------------|
| VCH-2026-0001  | draft  | Use this to test locking   |

JOURNAL VOUCHERS (all balanced, PKR)
-------------------------------------
| JV No.       | Debit Side         | Credit Side         | Amount  | Purpose               |
|--------------|--------------------|---------------------|---------|-----------------------|
| JV-DEMO-001  | Bank               | Agent1 Umrah AR     | 150,000 | Partial payment in    |
| JV-DEMO-002  | Hotel Expense      | Bank                | 80,000  | Hotel advance         |
| JV-DEMO-003  | Agent1 Umrah AR    | Umrah Revenue       | 280,000 | Revenue recognition   |

EXPECTED P&L for BG-2026-001:
  Revenue:      PKR 280,000
  Hotel Cost:   PKR  80,000
  Gross Profit: PKR 200,000

===============================================================
PART 3 — ROLE-BY-ROLE TESTING WALKTHROUGH
===============================================================

-----------------------------------------------------------
ROLE 1: SUPER ADMIN
Email: superadmin@zapflow.test / Password123!
-----------------------------------------------------------
What to test:

1. LOGIN
   Go to http://localhost:8000
   Enter email + password → you should land on the full dashboard

2. VIEW ALL BOOKINGS
   Left menu → Bookings
   Confirm both "Demo Umrah Group Alpha" (4 passengers) and
   "Demo Umrah Group Beta" (2 passengers) are visible

3. VIEW VOUCHERS
   Left menu → Vouchers
   Open VCH-2026-0001 (status = draft)
   Click "Lock" button
   Confirm success message appears

4. VERIFY VOUCHER IS LOCKED
   Try to edit VCH-2026-0001 again
   You should get a 403 error: "This voucher is locked"

5. CLOSE A FINANCIAL YEAR
   Left menu → Accounts → Financial Years
   Click "Close" on the active year
   Confirm it becomes locked

6. VERIFY CLOSED YEAR BLOCKS NEW JV
   Left menu → Accounts → Journal Vouchers → Create New
   Try selecting the closed year from dropdown → it should not appear
   (dropdown only shows open years)

7. UMRAH SETUP
   Left menu → Umrah Setup
   Verify hotels, packages, transporters, visa companies all load

8. VIEW REPORTS
   Left menu → Reports → Umrah Wise P&L
   Confirm BG-2026-001 shows Revenue 280,000 and Cost 80,000

-----------------------------------------------------------
ROLE 2: ACCOUNTANT
Email: accountant@zapflow.test / Password123!
-----------------------------------------------------------
What to test:

1. LOGIN & CHECK MENU RESTRICTION
   Log in → confirm you do NOT see: Bookings, Visa Pipeline,
   Room Allocation, Ticketing in the navigation
   You SHOULD see: Accounts menu with all sub-items

2. JOURNAL VOUCHERS
   Left menu → Accounts → Journal Vouchers
   Open JV-DEMO-003
   Confirm: Debit total = Credit total = PKR 280,000
   Click Print → confirm printable version loads

3. OPEN JV-DEMO-001
   Confirm Debit = PKR 150,000, Credit = PKR 150,000

4. OPEN JV-DEMO-002
   Confirm Debit = PKR 80,000, Credit = PKR 80,000

5. CHART OF ACCOUNTS
   Left menu → Accounts → Chart of Accounts
   Confirm tree structure loads with assets, liabilities, income, expenses

6. FINANCIAL YEARS
   Confirm active year is visible (and closed year if you ran Step 5 above)

7. TRIAL BALANCE REPORT
   Left menu → Reports → Trial Balance
   Confirm it loads and shows balanced totals

8. UMRAH P&L REPORT
   Left menu → Reports → Umrah Wise P&L
   Confirm BG-2026-001: Revenue 280,000 | Cost 80,000 | Profit 200,000

9. RECEIVABLES REPORT
   Confirm agent receivable balances appear

10. DAILY CASH REPORT
    Confirm the report loads and shows cash entries

11. TRY TO ACCESS BOOKINGS (should be blocked)
    Type http://localhost:8000/bookings in browser
    You should get a 403 Access Denied

-----------------------------------------------------------
ROLE 3: VISA OFFICER
Email: visaofficer@zapflow.test / Password123!
-----------------------------------------------------------
What to test:

1. LOGIN & CHECK MENU
   Confirm you see: Bookings (view only), Visa Pipeline
   Confirm you do NOT see: Accounts, Ticketing, Room Allocation

2. VIEW BOOKINGS
   Left menu → Bookings
   Confirm both groups are visible (Visa Officer can see all bookings)
   Click into "Demo Umrah Group Alpha"
   Confirm 4 passengers are listed with their passport numbers

3. VISA PIPELINE (Main test)
   Left menu → Visa Pipeline
   You should see a Kanban board with 5 columns:
     Draft | Sent to Embassy | MoFA Received | Visa Issued | Rejected

   Confirm Ahmad Raza is in the "Draft" column
   Drag Ahmad Raza → "Sent to Embassy"
   Refresh the page
   Confirm Ahmad Raza stays in "Sent to Embassy" (persisted to DB)

4. SET MoFA REFERENCE NUMBER
   Find Tariq Mehmood (already in "MoFA Received" column)
   Click on his card → set MoFA reference number
   Confirm it saves

5. VERIFY AUDIT TRAIL
   Ask Super Admin to check: the move you made should be logged

6. TRY TO ACCESS ACCOUNTS (should be blocked)
   Type http://localhost:8000/journal-vouchers in browser
   You should get a 403 Access Denied

-----------------------------------------------------------
ROLE 4: OPS STAFF
Email: opsstaff@zapflow.test / Password123!
-----------------------------------------------------------
What to test:

1. LOGIN & CHECK MENU
   Confirm you see: Bookings, Room Allocation, Passport Delivery
   Confirm you do NOT see: Accounts, Visa Pipeline, Ticketing

2. VIEW & EDIT BOOKINGS
   Left menu → Bookings → open Demo Umrah Group Alpha
   Try editing departure date → save → confirm it updates

3. ADD A PASSENGER
   Open Demo Umrah Group Alpha
   Click "Add Passenger"
   Fill in: Name, Passport No, DOB, Gender
   Save → confirm passenger appears in the list

4. CSV IMPORT TEST
   Left menu → Bookings → Import
   Download the sample CSV template
   Fill in 2 rows of passenger data
   Upload → preview shows → click Commit
   Confirm passengers appear in the booking

5. PASSPORT DELIVERY
   Open a booking → find any passenger
   Click "Passport Status"
   Change status from "Received" → "Sent for Processing"
   Save → confirm the status updates

6. ROOM ALLOCATION (Main test)
   Left menu → Room Allocation
   Find the grid of hotel rooms

   TEST 1 — Valid assignment:
   Drag a MALE passenger into room MK-101 (male room)
   Confirm it saves successfully

   TEST 2 — Gender restriction:
   Try to drag a FEMALE passenger into room MK-101 (male-only)
   Confirm you get an error: "Gender restriction — male only"

   TEST 3 — Capacity restriction:
   Try to assign a 3rd passenger into a room with capacity 2
   Confirm you get an error: "Room is at full capacity"

   Refresh the page → confirm valid assignments still show

-----------------------------------------------------------
ROLE 5: TICKETING STAFF
Email: ticketing@zapflow.test / Password123!
-----------------------------------------------------------
What to test:

1. LOGIN & CHECK MENU
   Confirm you see: Ticketing module
   Confirm you do NOT see: Bookings, Accounts, Visa Pipeline,
   Room Allocation

2. CREATE A TICKET INVOICE
   Left menu → Ticketing → New Invoice
   Fill in:
     Passenger: pick any from the list
     Airline: pick any
     Sector: pick any
     Ticket Number: TK-TEST-001
     Amount: 45,000
     Sale Type: BSP
   Save → confirm invoice created

3. CREATE ANOTHER WITH DIFFERENT SALE TYPE
   Create another invoice with Sale Type = Direct
   Confirm both BSP and Direct invoices appear in the list

4. FILTER BY SALE TYPE
   In the invoice list, filter by "BSP"
   Confirm only BSP invoices appear
   Filter by "Direct" → confirm only Direct invoices appear

5. PROCESS A REFUND
   Open TK-TEST-001 ticket invoice
   Click "Refund"
   Enter refund amount: 5,000
   Enter reason: "Ticket date changed"
   Save → confirm refund is recorded and linked to the invoice

6. TRY TO ACCESS BOOKINGS (should be blocked)
   Type http://localhost:8000/bookings in browser
   You should get a 403 Access Denied

-----------------------------------------------------------
ROLE 6: B2B AGENT 1 (Al-Noor Travel Agency)
Email: agent1@zapflow.test / Password123!
-----------------------------------------------------------
What to test:

1. LOGIN
   Confirm you see only your own bookings
   You should see: Demo Umrah Group Alpha (BG-2026-001)
   You should NOT see: Demo Umrah Group Beta (belongs to Agent 2)

2. VIEW YOUR BOOKING
   Open Demo Umrah Group Alpha
   Confirm you can see all 4 passengers and their visa statuses

3. DOWNLOAD VOUCHER
   Check if VCH-2026-0001 is visible (if locked by Super Admin)
   Download the PDF → confirm it opens correctly with passenger details

4. VERIFY ISOLATION (Important)
   Try to access BG-2026-002 directly:
   Type http://localhost:8000/bookings/[id-of-BG-2026-002] in browser
   You should get a 403 — agents cannot see other agents bookings

-----------------------------------------------------------
ROLE 7: B2B AGENT 2 (Barakah Hajj Group)
Email: agent2@zapflow.test / Password123!
-----------------------------------------------------------
What to test:

1. LOGIN
   Confirm you ONLY see: Demo Umrah Group Beta (BG-2026-002)
   Confirm you do NOT see: Demo Umrah Group Alpha (belongs to Agent 1)

2. VERIFY ISOLATION
   You can only see your 2 passengers in BG-2026-002
   Agent 1 data is completely invisible

-----------------------------------------------------------
ROLE 8: SUB-AGENT (Rahmat Sub-Agency)
Email: subagent1@zapflow.test / Password123!
-----------------------------------------------------------
What to test:

1. LOGIN
   Confirm limited scope — sees only what parent agency (Agent 1) shares
   Verify no access to financial modules

-----------------------------------------------------------
ROLE 9: B2C CUSTOMER (Individual Pilgrim)
Email: customer@zapflow.test / Password123!
-----------------------------------------------------------
What to test:

1. LOGIN
   Confirm you see ONLY your own booking/pilgrim status
   Confirm there is NO access to:
     - Any other customer data
     - Bookings management menu
     - Accounts
     - Visa Pipeline
     - Room Allocation

2. VERIFY RESTRICTION
   Type http://localhost:8000/bookings in browser
   You should get a 403 Access Denied

===============================================================
PART 4 — GOLDEN PATH (Full End-to-End Workflow Test)
===============================================================

Run these 10 steps in order to verify the complete system workflow:

STEP 1 — Login as superadmin@zapflow.test
  → Bookings → confirm "Demo Umrah Group Alpha" shows 4 passengers
  EXPECTED: 4 rows visible ✓

STEP 2 — Login as visaofficer@zapflow.test
  → Visa Pipeline → drag Ahmad Raza from "Draft" to "Sent to Embassy"
  Refresh page → confirm he stays in new column
  EXPECTED: Status persists after refresh ✓

STEP 3 — Login as opsstaff@zapflow.test
  → Room Allocation → assign Ahmad Raza to room MK-101
  EXPECTED: Assignment saves, no capacity/gender error ✓

STEP 4 — Login as superadmin@zapflow.test
  → Vouchers → open VCH-2026-0001 → click LOCK
  EXPECTED: Status changes from "draft" to "locked" ✓

STEP 5 — Try to edit the locked voucher (while still as Super Admin)
  → Click Edit on VCH-2026-0001
  EXPECTED: 403 error "This voucher is locked" ✓

STEP 6 — Login as accountant@zapflow.test
  → Journal Vouchers → open JV-DEMO-003
  EXPECTED: Dr = 280,000 | Cr = 280,000 (balanced) ✓

STEP 7 — Still as accountant → Reports → Umrah Wise P&L
  → Find BG-2026-001
  EXPECTED: Revenue 280,000 | Cost 80,000 | Profit 200,000 ✓

STEP 8 — Login as agent2@zapflow.test
  → Bookings
  EXPECTED: Only "Demo Umrah Group Beta" visible, NOT Alpha ✓

STEP 9 — Login as customer@zapflow.test
  → Try to access /bookings URL
  EXPECTED: 403 Denied — no staff modules accessible ✓

STEP 10 — Login as accountant@zapflow.test
  → Check navigation menu
  EXPECTED: No Visa Pipeline link, no Room Allocation link ✓

ALL 10 STEPS PASS = System fully verified ✓

===============================================================
PART 5 — QUICK SETUP COMMANDS (run these before testing)
===============================================================

# Step 1: Make sure MySQL is running in XAMPP Control Panel

# Step 2: Create databases (if not already done)
mysql -u root -e "CREATE DATABASE IF NOT EXISTS zapflow_travel;"
mysql -u root -e "CREATE DATABASE IF NOT EXISTS zapflow_travel_testing;"

# Step 3: Fresh migrate + seed (loads all demo data)
php artisan migrate:fresh --seed

# Step 4: Start the app
php artisan serve

# Step 5: Open browser
http://localhost:8000

# Step 6 (optional): Run automated tests (25/25 should pass)
php -d memory_limit=512M vendor/bin/phpunit --testdox --no-coverage

===============================================================
NOTES
===============================================================

- Vendor deprecation warnings (amphp, veewee) are HARMLESS —
  they come from third-party libraries and do not affect the app.

- The PHPUnit "Deprecations: 1" in the test output is also
  from the amphp vendor library, NOT project code.

- If you need to reset demo data at any point:
  php artisan migrate:fresh --seed
  (This wipes and re-creates everything from scratch)

- All passwords are: Password123!
  Never use this password on a live/production server.

===============================================================
