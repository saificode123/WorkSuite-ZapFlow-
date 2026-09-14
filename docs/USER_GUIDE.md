# ZapFlow Travel — User Guide

> **Audience:** New employees, supervisors, examiners, and anyone who needs to understand what this system does and how to use it — without reading the source code.
> **Last verified:** 2026-09-11 (all modules confirmed working, 25/25 automated tests passing)
> **Stack:** Laravel 10 · MySQL 8 · PHP 8.4 · Laravel Herd (local)

---

## Part 1 — Login Credentials

All accounts below are created by the database seeder. Every account uses the same password.

> **Password for all accounts: `Password123!`**
> Never use this password on a production/live system.

### Internal Staff

| Role | Name | Email | Password | What this role is for |
|---|---|---|---|---|
| Super Admin | Zafar Khan | `superadmin@zapflow.test` | `Password123!` | Complete access to everything — no restrictions. Use this to configure the system, manage users, and verify anything. |
| Admin | Fatima Malik | `admin@zapflow.test` | `Password123!` | Full administrative access, same as Super Admin for practical purposes. |
| Accountant | Bilal Ahmed | `accountant@zapflow.test` | `Password123!` | Sees only the Accounts module: chart of accounts, journal vouchers, payments, financial years, and all financial reports. Cannot see bookings, visa pipeline, or room allocation. |
| Visa Officer | Ayesha Siddiqui | `visaofficer@zapflow.test` | `Password123!` | Can view bookings and passenger lists, and move passengers through the visa pipeline (the Kanban board). Cannot access financial data. |
| Ops Staff | Usman Tariq | `opsstaff@zapflow.test` | `Password123!` | Handles the physical side: views and edits bookings, assigns passengers to hotel rooms, and tracks passport delivery status. |
| Ticketing Staff | Nadia Rauf | `ticketing@zapflow.test` | `Password123!` | Access to the Ticketing module only — creates flight ticket invoices and processes refunds. |

### External / Client Accounts

| Role | Name | Email | Password | What this role is for |
|---|---|---|---|---|
| B2B Agent 1 | Al-Noor Travel Agency | `agent1@zapflow.test` | `Password123!` | Travel agency that books Umrah groups through your company. Can see only their own bookings (BG-2026-001). Has separate Umrah and Ticketing sub-ledgers in the accounts system. |
| B2B Agent 2 | Barakah Hajj Group | `agent2@zapflow.test` | `Password123!` | A second, isolated travel agency. Owns BG-2026-002. Cannot see Agent 1s data at all — used to verify booking isolation. |
| Sub-Agent | Rahmat Sub-Agency | `subagent1@zapflow.test` | `Password123!` | A sub-agency that works under Agent 1. Limited scope — sees only what has been shared with them in the hierarchy. |
| B2C Customer | Hassan Iqbal | `customer@zapflow.test` | `Password123!` | An individual pilgrim who booked directly. Can only see their own booking. No access to any staff modules. |

---

## Part 2 — Role-by-Role Guide

---

### 2.1 Super Admin

**When you log in as Super Admin**, you see the full dashboard — a summary of all bookings, recent activity, financial totals, and links to every module in the system. Nothing is hidden.

#### Employee and User Management
- **Employees** — Add or remove staff members, assign them roles (Accountant, Visa Officer, etc.), and control which modules they can access. You would use this when onboarding a new hire or restricting someones access.
- **Roles and Permissions** — Fine-tune exactly what each role can see and do. The system uses named permissions (e.g., `view_booking`, `add_journal_voucher`) so you can allow a staff member to view bookings but not edit them.

#### Setup and Configuration
- **Umrah Setup** — The master configuration hub. Use this to see all the building blocks of your Umrah operation in one place: hotels, packages, transport, visa companies, airlines, and lookup tables. Think of it as your product catalogue.
- **Financial Year** — Create and name accounting periods (e.g., "FY 2026"). Once all transactions are in, close the year to lock it. After closing, no new journal entries can be posted to that period.
- **Chart of Accounts** — The tree of all financial accounts (assets, liabilities, income, expenses). Every financial transaction eventually posts to a leaf on this tree. You would add new accounts here when you start working with a new bank, hotel, or revenue stream.

#### Day-to-Day Operations (full access)
Super Admin can do everything described in the sections below (Accountant, Visa Officer, Ops Staff, Ticketing Staff) — there is no restriction.

---

### 2.2 Accountant

**Dashboard:** Shows financial summary widgets — journal voucher counts, recent cash receipts, and links to all accounting sub-modules.

#### Accounts Setup
- **Chart of Accounts** — Browse and manage the account tree. Add a new bank account, create a revenue sub-account, or restructure the hierarchy. This is the foundation everything else posts to.
- **Account Openings** — Enter the starting balance for each account at the beginning of a financial year. Used once per year to carry forward balances from the previous period.
- **Exchange Rates** — Set daily or periodic PKR/USD/SAR rates. When a transaction is recorded in a foreign currency, the system uses this rate to convert to the base currency automatically.
- **Financial Years** — Create and manage accounting periods. Close a year once all transactions are posted — after closing, the system blocks any new entries into that year to preserve the record.

#### Day-to-Day Accounting
- **Journal Vouchers** — The core accounting record. Every debit must equal every credit (the system enforces this). You would create a journal voucher to record hotel advances, revenue recognitions, or any financial event that is not captured by a payment or receipt form. You can also print them.
- **Receive Payment** — Record money coming in from a B2B agent or B2C customer. Automatically creates the matching journal voucher in the background, so you do not need to make a manual JV for routine payments.
- **Make Payment** — Record money going out to a hotel, transport company, or visa agency. Same as above — the JV is created automatically.
- **Cash Receipts** — Record cash received at the counter (walk-in payments). Links to the appropriate cash account in the chart of accounts.
- **Other Service Invoice** — Issue an invoice for any miscellaneous service not covered by the standard booking/ticketing flows. Posts a real double-entry journal voucher automatically.

#### Financial Reports
- **Trial Balance** — Snapshot of all account balances at a point in time. Use this to check that debits and credits balance across the whole system before closing a period.
- **Receivables** — List of every customer or agent who owes you money and how much. Use this at month-end to chase outstanding balances.
- **Payables** — List of every supplier (hotel, transporter, visa company) you owe money to. Use this to plan outgoing payments.
- **Ageing Report** — Breaks receivables or payables into buckets (0-30 days, 31-60 days, 60+ days). Use this when you need to prioritize collection or understand how old your debts are getting.
- **Daily Cash Report** — Shows all cash receipts and payments for any given day, with a running balance. Use this to reconcile the cash drawer at the end of each day.
- **Umrah-Wise P&L** — Profit and loss broken down by individual booking group. See exactly how much was made or lost on each Umrah group.
- **Agent Comparison Report** — Compares revenue and booking volume across all B2B agents. Use this to identify which agents bring the most business.

---

### 2.3 Visa Officer

**Dashboard:** Shows the Visa Pipeline Kanban board prominently — all passengers, sorted by visa status column. Also shows a read-only view of active bookings.

#### What the Visa Officer Can Do
- **View Bookings** — Browse the list of all booking groups and their passengers. The Visa Officer can see passenger details (name, passport number, visa status) but cannot modify the booking itself.
- **Visa Pipeline (Kanban Board)** — The main daily tool. Each passenger appears as a card in one of five columns:
  1. **Draft** — just entered, nothing submitted yet
  2. **Sent to Embassy** — documents physically submitted
  3. **MoFA Received** — Ministry of Foreign Affairs clearance received
  4. **Visa Issued** — stamped and ready
  5. **Rejected** — application denied

  Drag a card to move a passenger to the next stage. The system records every move with a timestamp, the officers name, and optional remarks, creating a complete audit trail. When a passenger reaches "MoFA Received," the officer can also record the official MoFA reference number on the record.

**What is NOT accessible:** The Visa Officer cannot see any financial data, cannot create or edit bookings, and cannot assign rooms.

---

### 2.4 Ops Staff

**Dashboard:** Shows current bookings with passenger counts, room allocation status, and passport delivery status summaries.

#### Bookings (view and edit)
- **View Bookings** — See all booking groups and their passengers, including departure dates, packages assigned, and charges.
- **Edit Booking Details** — Update departure/return dates, notes, or the package attached to a group. Ops Staff can also add new passengers to an existing booking or remove them.
- **CSV Import** — If a large group arrives from an agent by spreadsheet, import all passengers at once rather than entering them one by one. The system previews the import first so you can check for errors before committing.
- **Mutamer Transfer** — Move a passenger from one booking group to another (for example, if someone needs to travel in a different batch than originally planned).
- **Passport Delivery Tracking** — Record the status of each physical passport: received from client, sent for visa processing, or returned after stamping. Useful for knowing at any moment where every passport physically is.
- **Repeat Fees** — Record additional charges for passengers who have been on Umrah before.

#### Room Allocation
- **Room Allocation Grid** — Assign each passenger to a specific hotel room. The system enforces two rules automatically (server-side, not just cosmetic):
  - **Capacity** — you cannot assign more passengers than the room holds
  - **Gender restriction** — male-only rooms reject female passengers and vice versa; family rooms accept either

  Use this once hotel bookings are confirmed, typically 4-6 weeks before departure.

**What is NOT accessible:** Ops Staff cannot see financial reports, journal vouchers, or the ticketing module.

---

### 2.5 Ticketing Staff

**Dashboard:** Shows recent ticket invoices and a link to create new ones.

#### Ticketing
- **Ticket Invoices** — Create a ticket invoice for any passengers flight. Record the passenger, airline, sector (route), ticket number, and amount. Specify the sale type: BSP (IATA Billing and Settlement Plan), XO (exchange order), or Direct (sold directly with the airline). This lets you filter invoices later by sale channel for reconciliation.
- **Ticket Refunds** — Process a partial or full refund on any existing ticket invoice. Records the refund amount and reason, linked back to the original invoice.

**What is NOT accessible:** Ticketing Staff cannot see bookings, visa pipeline, room allocation, or any accounts module.

---

### 2.6 B2B Agent

**Dashboard:** Shows only bookings and passengers that belong to this specific agency. An agent from Agency A cannot see anything from Agency B — this isolation is enforced server-side.

- **View Own Bookings** — See the list of booking groups they own, with passenger counts, departure dates, and visa status summaries.
- **View Own Passengers** — See the individual pilgrim records for their groups: passport numbers, visa stages, room assignments.
- **Download Vouchers** — Once a voucher has been issued by staff, the agent can download the PDF to share with pilgrims.

**What is NOT accessible:** Agents cannot see other agents data, cannot access any accounts information, and cannot modify bookings directly.

---

### 2.7 Sub-Agent

Works like a B2B Agent but sits beneath a parent agency in the hierarchy. Their scope is limited to what the parent agency shares with them — typically a subset of the parent agencys bookings.

---

### 2.8 B2C Customer (Individual Pilgrim)

**Dashboard:** Shows only their own booking information — visa status, room assignment, and voucher once issued. Cannot access any other customers data, no financial information, no staff modules.

---

## Part 3 — How a Real Booking Flows Through the System

Here is how a group Umrah booking moves through every part of the system, from enquiry to final accounts.

### Step 1: Agent Creates a Booking

A B2B agent (or your admin on their behalf) creates a Booking Group — for example, "Demo Umrah Group Alpha" departing 1 December. They attach a Package (which already has hotel, transport, visa company, and calculated price), set departure and return dates, and start adding Passengers one by one or in bulk via CSV upload. Each passenger needs: name, passport number, date of birth, gender, and family relation (so the system knows who is whose mahram).

### Step 2: Visa Officer Moves Each Passenger Through the Pipeline

Once passengers are in the system, the Visa Officer sees them on the Visa Pipeline Kanban board. Each passenger starts in the "Draft" column. As documents are submitted and processed, the officer drags each card forward:

Draft → Sent to Embassy → MoFA Received → Visa Issued

Every move is recorded with a timestamp and the officers name. When MoFA clearance comes back, the officer records the official MoFA reference number. If a visa is rejected, the card goes to the Rejected column. The pipeline gives the whole team a real-time visual answer to "where is each persons visa right now?"

### Step 3: Ops Staff Assigns Hotel Rooms

Once hotel bookings are confirmed, Ops Staff opens the Room Allocation grid. They see all hotel rooms — their type, capacity, and gender restriction — and drag each passenger card into a room. The system immediately checks capacity and gender match. If either check fails, the assignment is blocked. Female pilgrims cannot end up in a male-only room, even by accident.

### Step 4: Vouchers are Generated and Locked

Before departure, the admin creates a Voucher for the booking group — accommodation, transport, or full package. The voucher lists all charges, room assignments, and relevant details.

Once verified, the voucher is Locked. After locking:
- Nobody can edit it — even Super Admin gets a 403 error if they try.
- A QR code is generated (cryptographically signed using the system secret key) that hotels or transport providers can scan to verify authenticity.
- The lock event is recorded in the audit log.

The agent can download the voucher PDF to give to pilgrims as proof of accommodation.

### Step 5: Payments are Recorded

As the agent pays your company (or as you pay hotels and visa companies), the accountant records each transaction. All payments automatically create the underlying double-entry accounting entries — no manual journal vouchers needed for routine payments.

### Step 6: Agency Owner Checks the P&L

Once payments are posted, the accountant or admin can run the Umrah-Wise P&L report for that booking group:

| Group | Revenue | Cost | Gross Profit | Margin |
|---|---|---|---|---|
| BG-2026-001 | PKR 280,000 | PKR 80,000 | PKR 200,000 | 71.4% |

This is calculated directly from actual payments received and costs paid — not estimates or the package price. If the hotel bill came in higher than expected, the P&L reflects reality.

---

## Part 4 — Reports: What Problem Does Each One Solve?

| Report | When would you use it? |
|---|---|
| **Trial Balance** | End of month or year — quickly check that total debits equal total credits across the whole company before closing the books. |
| **Umrah-Wise P&L** | After a group completes travel — see the exact profit or loss on each booking group, calculated from real payments not estimates. The agency owner uses this to evaluate group profitability. |
| **Receivables Report** | At month-end — see every agent or customer who owes you money and exactly how much, so you know who to follow up with. |
| **Payables Report** | Before making outgoing payments — see what you owe to hotels, transport companies, and visa agencies so you can plan cash outflow. |
| **Ageing Report** | When chasing overdue balances — breaks down receivables (or payables) by how long they have been outstanding: current, 1-30 days, 31-60 days, 60+ days. Older buckets signal accounts needing urgent attention. |
| **Daily Cash Report** | End of each day — reconcile the cash drawer. Shows every receipt and payment made that day with a running balance so the accountant can verify the closing cash figure. |
| **Agent Comparison Report** | Quarterly review — compare which B2B agents are generating the most revenue and booking volume. Useful for deciding where to focus business development. |
| **Employee Efficiency Report** | Management review — see how many bookings were processed, how many vouchers issued, and how many visa transitions handled per employee over a date range. |
| **Arrival Report** | The morning a group departs from Pakistan — confirm which pilgrims are on todays departure, their passport numbers, and flight details. |
| **Departure Report** | At the end of a groups stay in Saudi Arabia — list of pilgrims scheduled to depart KSA on a given date. Useful for the ground handling team. |
| **Makkah Hotel Report (Check-In/Out)** | The morning of Makkah check-in — see exactly which pilgrims are checking into which rooms at which Makkah hotel, and on which night they check out. Hotel reception can use this directly. |
| **Madina Hotel Report (Check-In/Out)** | Same as above but for Madina — use on the morning pilgrims transfer from Makkah to Madina. |
| **KSA Intimation Report** | Before the group departs from Pakistan — generate the official notification document for Saudi authorities listing all pilgrims, passport details, and hotel assignments. Often a regulatory requirement. |

---

## Part 5 — Setup Reference (for Admins Configuring the System)

Before bookings can be created, the following master data needs to be in place. The seeder creates demo versions of all of these.

| Setup Item | Where | What it does |
|---|---|---|
| **Financial Year** | Accounts → Financial Years | Defines the accounting period. Create this first — journal vouchers will not post without an active year. |
| **Chart of Accounts** | Accounts → Chart of Accounts | The account tree. Must include bank accounts, receivable accounts for each agent, and expense accounts for hotels/transport/visa. |
| **Service Providers** | Travel → Service Providers | Hotels, transport companies, visa agencies you work with as suppliers. Link each to a payable account in the COA. |
| **IATA Records** | Travel → IATA | Airline IATA numbers your agency holds. Link to service providers and payable accounts. |
| **Visa Companies** | Travel → Visa Companies | The visa processing agents you use. Set their cost rate and sell rate — these feed into package auto-calculation. |
| **Transporters** | Travel → Transporters | Bus/van companies. Set vehicle types, routes, and per-route rates (including Ziarat rates separately). |
| **Airlines** | Travel → Airlines | Airlines you issue tickets for. Set logo, IATA code, and linked accounts. |
| **Hotels** | Travel → Hotels | Properties in Makkah/Madina. Add room types and seasonal tariff/sell rates. |
| **Hotel Rooms** | Travel → Hotel Rooms | Individual room inventory within each hotel. Set capacity and gender restriction (male-only, female-only, family). |
| **Packages** | Travel → Packages | A named bundle: hotel + transport + visa company + markup. The system calculates the sell price automatically. |
| **Discounts** | Travel → Discounts | Percentage or fixed discounts applicable to packages (e.g., Early Bird 5%). Set validity dates and active status. |
| **Customer Types** | Settings → Customer Types | Categories of clients (e.g., Direct, Corporate, Sub-Agent). Used for reporting and rate differentiation. |
| **Exchange Rates** | Accounts → Exchange Rates | Daily SAR/USD to PKR rates. Used when recording foreign-currency payments. |

---

## Part 6 — What Is Intentionally Not Built

This section states the systems boundaries honestly. A reader who knows these limits upfront will not be confused by their absence.

**NUSUK Integration — Manual Entry Stub Only**
Saudi Arabias NUSUK portal (the government system for Umrah visa applications) does not offer a public API for third-party software. This system includes an adapter architecture that is ready to plug in a real NUSUK integration if an API becomes available, but right now all data entry for NUSUK purposes is done manually — an officer reads the NUSUK screen and types the reference numbers into ZapFlow. There is no automatic sync.

**Passport OCR / MRZ Scanning**
Passenger data is entered manually by typing, or by importing a CSV spreadsheet. There is no camera-based or scanner-based passport reading. Someone has to type each passengers name, passport number, and date of birth.

**Public Website / CMS**
There is no customer-facing booking website that pilgrims can browse and self-book through. ZapFlow is a back-office management system for the travel agencys staff and their B2B partners. A public website was out of scope for this project.

**Overseas Employment Module**
The core platform includes an Overseas Employment module for job placement agencies. That module is not used by ZapFlow Travel and has been disabled.

**Salary Slips / Payroll**
HR payroll, salary slip generation, and monthly payroll processing are not part of this system. Employee records exist (for login and permissions), but payroll calculations are not implemented.

---

## Appendix A — Database Setup Commands

Run these commands in order if the database does not exist yet (the error "Unknown database zapflow_travel" means Step 1 was skipped):

```bash
# Step 1 — Create the database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS zapflow_travel;"

# Step 2 — Run all migrations and seed demo data
php artisan migrate:fresh --seed
```

**Note:** The deprecation warnings you see from `amphp` and `veewee` vendor libraries are harmless — they come from third-party packages that have not yet updated for PHP 8.4. They do not affect any functionality.

If you also need the test database:

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS zapflow_travel_testing;"
php -d memory_limit=512M vendor/bin/phpunit --testdox --no-coverage
```

---

## Appendix B — Quick Smoke Test (10 Steps)

After seeding, run through these 10 steps to confirm everything is working:

1. Log in as `superadmin@zapflow.test` → Bookings → confirm "Demo Umrah Group Alpha" shows 4 passengers.
2. Log in as `visaofficer@zapflow.test` → Visa Pipeline → move Ahmad Raza from Draft to Sent to Embassy.
3. Log in as `opsstaff@zapflow.test` → Room Allocation → assign a passenger to room MK-101.
4. Log in as `superadmin@zapflow.test` → Vouchers → open VCH-2026-0001 → click Lock.
5. Try to edit VCH-2026-0001 → confirm a 403 error appears ("voucher is locked").
6. Log in as `accountant@zapflow.test` → Journal Vouchers → open JV-DEMO-003 → confirm Dr = Cr = 280,000.
7. Navigate to Travel Reports → Umrah Wise P&L → confirm BG-2026-001 shows Revenue 280,000, Cost 80,000.
8. Log in as `agent2@zapflow.test` → Bookings → confirm ONLY "Demo Umrah Group Beta" appears (not Alpha).
9. Log in as `customer@zapflow.test` → confirm no access to any staff-level modules.
10. Log in as `accountant@zapflow.test` → confirm no Visa Pipeline link in the navigation (module not assigned).

All 10 steps should work cleanly on a freshly seeded database.

---

*ZapFlow Travel — User Guide | Verified 2026-09-11 | 25/25 automated tests passing*
