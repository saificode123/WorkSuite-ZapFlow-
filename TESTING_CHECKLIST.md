# ZapFlow Umrah/Hajj Travel CRM — Testing Checklist

## Environment
| Item | Value |
|------|-------|
| **App URL** | `http://localhost:8000` (or your configured APP_URL) |
| **Login URL** | `http://localhost:8000/login` |
| **Dashboard** | `http://localhost:8000/account/dashboard` |

## Login Credentials (seeded)
| Role | Email | Password |
|------|-------|----------|
| **Admin** | `admin@example.com` | `123456` |
| Employee | `employee@example.com` | `123456` |
| Client | `client@example.com` | `123456` |

> **Note:** The system uses an authenticated prefix `account/` for all travel modules. After logging in, navigate to any route listed below.

---

## Quick-Start Pre-Testing

Before full testing, run these once:

```bash
# 1. Fresh seed (this will reset all data)
php artisan migrate:fresh --seed

# 2. Disable non-travel modules (Projects, Tasks, Leads, etc.)
php artisan db:seed --class=Database\\Seeders\\TravelModuleDefaultDisableSeeder

# 3. Clear caches
php artisan optimize:clear
php artisan route:cache
```

Now login at `/login` with `admin@example.com` / `123456`.

---

## Module Test Cases

### 1. Service Providers (Setups)
**URL:** `/account/service-providers`  
**Purpose:** Manage travel suppliers (hotels, transport, visa companies).

- [ ] Click **"Add Service Provider"** — modal opens
- [ ] Fill all fields (name, contact, type dropdown) — submit
- [ ] Verify record appears in the DataTable
- [ ] Click **Edit** — modal pre-fills data
- [ ] Modify a field — save — verify update in row
- [ ] Click **Delete** — confirm modal — record disappears
- [ ] Test search/filter boxes in the table header

### 2. Hotels
**URL:** `/account/hotels`  
**Purpose:** Hotel registry used in packages.

- [ ] Create a hotel with all fields (name, city, star rating, contact)
- [ ] Verify row in DataTable
- [ ] Edit the hotel (change rating, add phone)
- [ ] Delete the hotel
- [ ] Verify that the hotel name appears in package dropdowns later

### 3. Hotel Rooms
**URL:** `/account/hotel-rooms`  
**Purpose:** Room types + rates per hotel.

- [ ] Click **"Add Room"** — modal opens
- [ ] Select a hotel from the dropdown
- [ ] Enter room type (Single/Double/Triple/Suite), board basis (BB/HB/FB/AI), season, rate
- [ ] Submit — verify row in DataTable shows hotel name and rate
- [ ] Edit room (change rate or board basis)
- [ ] Delete room

### 4. Packages
**URL:** `/account/packages`  
**Purpose:** Umrah/Hajj packages combining hotels, transport, flights.

- [ ] **Create Package** — fill name, duration, select hotels, add markup/discount
- [ ] Verify package appears with calculated price (uses `PackageCalculationService`)
- [ ] **Edit** — modify markup percentage — see recalculated price
- [ ] Delete package
- [ ] Verify package shows in Booking dropdown

### 5. Bookings
**URL:** `/account/bookings`  
**Purpose:** Core — create/import/manage pilgrim bookings.

- [ ] Click **"Add Booking"**
- [ ] Select customer (or create new from the customer picker)
- [ ] Select package, travel dates, add passengers
- [ ] Save — verify booking created, passengers listed in show page
- [ ] **Add Passenger** via inline form — save
- [ ] **Remove Passenger** — verify removal
- [ ] **Edit Booking** — change dates or package
- [ ] **Delete Booking** (should be blocked if vouchers exist)
- [ ] **CSV Import** — download sample, upload CSV, verify parsed data
- [ ] **Show page** — verify all booking details visible

### 6. Vouchers
**URL:** `/account/vouchers`  
**Purpose:** Issue/lock/print vouchers per booking passenger.

- [ ] **Create Voucher** — select booking + passengers
- [ ] Verify voucher appears in list with `Draft` status
- [ ] **Issue Voucher** — click Issue button — status changes to `Issued`
- [ ] **Lock Voucher** — click Lock — confirms — status changes to `Locked`
- [ ] Verify locked voucher cannot be edited/deleted (blocked by `VoucherObserver`)
- [ ] **PDF download** — click PDF icon — downloads PDF with QR code
- [ ] **QR code endpoint** — `GET /account/vouchers/{id}/qr` — returns PNG
- [ ] **Show page** — verify all voucher details + QR display
- [ ] **Edit while Draft** — fields are editable
- [ ] **Edit while Locked** — should fail/redirect

### 7. Voucher Auto-Numbering
- [ ] After seeding, create 3 vouchers — numbers should auto-increment (e.g., `VCH-00001`, `VCH-00002`)
- [ ] VoucherObserver handles this on `creating` event

### 8. Visa Pipeline
**URL:** `/account/visa-pipeline`  
**Purpose:** Track visa application stages per passenger.

- [ ] Verify Kanban board loads with status columns (Draft, Submitted, MOFA, Processing, etc.)
- [ ] **Drag passenger** from one column to another — status updates
- [ ] **Set MOFA Ref** — enter MOFA/UUID reference number
- [ ] **Visa Log** — after moving status, check VisaLog is created (visible in passenger history)
- [ ] Verify PassengerObserver creates log entries on status change

### 9. Room Allocation
**URL:** `/account/room-allocation`  
**Purpose:** Allocate hotel rooms to passengers.

- [ ] Select hotel + room type from dropdowns
- [ ] Select passengers (from bookings)
- [ ] **Assign** — passengers appear in the allocated list
- [ ] **Remove allocation** — passenger returns to unallocated
- [ ] Verify room capacity logic (don't over-allocate)

### 10. Insurance (Policies + Sales)
**Policies URL:** `/account/insurance-policies`  
**Sales URL:** `/account/insurance-sales`  

- [ ] **Create Policy** — name, provider, coverage type, premium amount
- [ ] Edit/delete policy
- [ ] **Create Sale** — select policy, select passenger from booking, enter sale date
- [ ] Verify sale row in DataTable with passenger + policy info
- [ ] Edit/delete sale

### 11. Payments (Travel Payments)
**URL:** `/account/travel-payments`  
**Purpose:** Record payments received from / made to suppliers.

- [ ] **Receive Payment** — navigate to Receive tab
- [ ] Select booking/passenger, enter amount, mode (cash/bank/cheque)
- [ ] Submit — verify payment recorded
- [ ] **Make Payment** — navigate to Make tab
- [ ] Select supplier (hotel/transport/etc.), enter amount
- [ ] Submit — verify outflow recorded
- [ ] **Cancel Payment** — cancel a payment — verify status changes
- [ ] Verify double-entry postings in Chart of Accounts

### 12. Cash Receipts
**URL:** `/account/cash-receipts`  
**Purpose:** Manual cash receipt entries with double-entry posting.

- [ ] Click **"Add Receipt"**
- [ ] Select debit account (e.g., Cash), credit account (e.g., Customer), enter amount + description
- [ ] Submit — verify receipt in the DataTable
- [ ] **Show** — view receipt details + journal entries
- [ ] **Edit** — modify amount or description
- [ ] **Delete** — verify deletion also removes journal entries
- [ ] Verify balance updates in Chart of Accounts

### 13. Reports
**URL:** `/account/travel-reports`  
**Purpose:** Financial and operational reports.

- [ ] **Trial Balance** — loads chart of accounts summary with debit/credit columns
- [ ] **Profit & Loss** — income vs expense summary
- [ ] **Monthly P&L** — grouped by month
- [ ] **Umrah-wise P&L** — filter by package/booking
- [ ] **Receivables** — amounts due from customers
- [ ] **Payables** — amounts due to suppliers
- [ ] **Ageing Report** — overdue buckets
- [ ] **Export to Excel** — click any export button — downloads `.xlsx`

### 14. Command Palette (Cmd+K)
**URL:** Press `Ctrl+K` or `Cmd+K` from any account page  
**Purpose:** Quick search across the system.

- [ ] Focus the palette (keyboard shortcut)
- [ ] Type "admin" — should show admin user
- [ ] Type booking reference — show matching booking
- [ ] Type voucher number — show matching voucher
- [ ] Type passenger name — show matching passenger
- [ ] Click a result — navigates to the record

### 15. Integration Settings
**URL:** `/account/integration-settings`  
**Purpose:** Configure travel API adapters (Airlines, Visa, Hotels, Insurance).

- [ ] Navigate to Settings → Integration Settings (or direct URL)
- [ ] Verify table shows configured adapters
- [ ] Click **"Update"** on an adapter — modal/metro form opens
- [ ] Enter API key, endpoint URL, toggle active
- [ ] Save — verify changes persisted
- [ ] Click **"Test Connection"** — shows success/failure message
- [ ] For new adapter — click **"Add Integration"** — fill form — saves

---

## Accounting Base (Pre-Existing Modules)

These modules existed before the travel pivot and should still work:

| Module | URL | Test Actions |
|--------|-----|-------------|
| Customer Types | `/account/customer-types` | Create, Edit, Delete |
| Financial Years | `/account/financial-years` | Create, Set Active |
| Chart of Accounts | `/account/chart-of-accounts` | Tree view, Create account |
| Journal Vouchers | `/account/journal-vouchers` | Create with debit/credit entries, Print |
| Exchange Rates | `/account/exchange-rates` | Add rate for currency pair |
| Account Openings | `/account/account-openings` | Opening balances per account |

---

## Sidebar & Navigation Checks

- [ ] Verify sidebar shows Travel section with all 12+ travel module links
- [ ] Click each sidebar link — route loads without 404
- [ ] Verify non-travel modules (Projects, Tasks, Leads) are hidden (if seeder was run)
- [ ] Verify mobile sidebar works (hamburger menu)
- [ ] Verify active link highlighting

---

## Translation / i18n Check

- [ ] Change language in settings — verify sidebar labels change
- [ ] Verify travel-specific translations load (new keys added)

---

## Edge Cases to Test

| Scenario | Expected Behavior |
|----------|------------------|
| Create voucher without passengers | Validation error |
| Lock draft voucher | Status → Locked |
| Delete locked voucher | Observer blocks — error message |
| Create booking with no package selected | Validation error |
| Allocate more passengers than room capacity | Validation error |
| Duplicate email in customer | Validation error |
| CSV import with malformed rows | Parse error with row numbers |
| Create cash receipt with zero amount | Validation error |
| Test connection with invalid API URL | Error message shown |

---

## Quick Verification Commands

Run these from the terminal to verify system health:

```bash
# Check all routes load without error
php artisan route:list

# Check no missing classes
php artisan clear-compiled

# Verify migrations are fresh
php artisan migrate:status

# Check key observers are registered
php artisan event:list | findstr Voucher
php artisan event:list | findstr Passenger

# List all registered routes with method
php artisan route:list | findstr account/
```

---

## Troubleshooting

| Symptom | Fix |
|---------|-----|
| 404 on travel pages | Run `php artisan route:cache`, verify routes in listing |
| Class not found for controller | Run `composer dump-autoload` |
| Missing migrations | Run `php artisan migrate --force` |
| Blank page on Dashboard | Run `php artisan optimize:clear` |
| Login redirect loop | Clear cookies, check `APP_URL` in `.env` |
| Seed fails | Run `php artisan migrate:fresh --seed` |
