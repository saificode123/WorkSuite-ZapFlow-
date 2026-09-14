# ZapFlow Travel - SYSTEM_STATE.md

> **Living reference** — Updated: 2026-09-14 (re-verified live this session; supersedes the 2026-08-27/08-31 content below where it conflicts)
> **Purpose:** Accurate snapshot for planning what is missing and how to build it.
> **Evidence standard:** Every claim has a real file path or real command output.
> Anything not checked is marked **UNVERIFIED**.
>
> **IMPORTANT — read this before trusting anything below dated 2026-08-27/08-31:** at the start of the 2026-09-14 session, the `zapflow_travel` database referenced throughout this document was found completely empty (0 tables) — MySQL wasn't even running. Every row count and "Implemented & Verified" claim below that predates 2026-09-14 was re-derived from a document (`docs/FINAL_PROJECT_REPORT.md` v2) whose own live-database evidence could not be reproduced in this environment. Section L has been replaced with the real, re-verified state. See `docs/FINAL_PROJECT_REPORT.md` (v3) for the full evidence trail, real bugs found and fixed, and the security audit. Do not re-trust Section L's old content if you see it cached anywhere — it has been overwritten below.

---

## Section A — Environment & Stack

### A1. PHP Version

Command: `php -v` (run 2026-08-27)

```
PHP 8.4.23 (cli) (built: Jul  1 2026 04:47:16) (NTS Visual C++ 2022 x64)
Copyright (c) The PHP Group
Zend Engine v4.4.23, with Zend OPcache v8.4.23
```

**PHP binary path** (Get-Command php): C:\Users\urreh\.config\herd\bin\php.bat
- PHP is provided by **Laravel Herd**.

**Composer platform override** (composer.json lines 139-141):
```json
"platform": { "php": "8.3.0" }
```
WARNING: Composer resolves deps as PHP 8.3.0, but actual binary is 8.4.23.

---

### A2. Laravel Version

composer.json line 42:
```json
"laravel/framework": "^10.0"
```

---

### A3. Database Configuration

.env (password redacted):
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zapflow_travel
DB_USERNAME=root
DB_PASSWORD=***
APP_NAME=ZapFlow-Travel
APP_ENV=codecanyon
APP_DEBUG=true
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
MAIL_MAILER=log
APP_URL=http://localhost
```

**Database engine confirmed (2026-09-14 — corrected):** MySQL, served on this machine from `C:\ForXampp\mysql\bin\mysqld.exe` (host 127.0.0.1, port 3306, root user, no password). **There is no `C:\xampp` on this machine** — the earlier "via XAMPP" claim was never re-checked against the actual filesystem. MySQL was **not running** at the start of the 2026-09-14 session and had to be started manually; do the same (`mysqld.exe --defaults-file=...\my.ini --standalone`, backgrounded) before running migrations, seeders, or tests.

**Critical operational fact, confirmed 2026-09-14, not previously documented:** the committed `.env` has `APP_ENV=codecanyon`. `database/seeders/DatabaseSeeder.php` gates `UsersTableSeeder`, `RoleSeeder`, and `DemoDataSeeder` behind `!App::environment('codecanyon', 'production')`. **Under the shipped `.env`, `php artisan db:seed` creates 1 company row and nothing else** — no users, no travel data, none of the `docs/TEST_ACCOUNTS.md` logins. The shortcut previously documented in `TEST_ACCOUNTS.md` (`php artisan db:seed --class=Database\Seeders\DemoDataSeeder` run alone) is **broken on a fresh database** — verified live this session to leave the login page throwing a real 500 (missing org-settings row), and even when it doesn't 500, it leaves every seeded user with an empty `modules` array, so every travel/accounts controller 403s for everyone including Super Admin (`abort_403` is a plain helper, not a Gate check — `Gate::before`'s admin bypass does not apply). **The only procedure confirmed to work end-to-end:** `php artisan migrate:fresh` then override the environment at the process level (do not edit `.env`) — `export APP_ENV=local && php artisan db:seed` (bash) / `$env:APP_ENV="local"; php artisan db:seed` (PowerShell). `docs/TEST_ACCOUNTS.md` has been corrected accordingly. See `docs/FINAL_PROJECT_REPORT.md` Section 0 for full detail.

**Also confirmed 2026-09-14:** PHPUnit's `RefreshDatabase`/schema-dump machinery shells out to a `mysql` binary. If it isn't on `PATH` (it wasn't, by default, on this machine), 19 of 25 tests error with `'mysql' is not recognized...` rather than a clean pass — add `C:\ForXampp\mysql\bin` (or wherever the `mysql` client lives) to `PATH` before running the suite.

---

### A4. Frontend Build Tooling

**Build tool:** Laravel Mix (Webpack-based).
- webpack.mix.js EXISTS at project root.
- vite.config.js does NOT exist.

package.json scripts: "dev": "npm run development", "development": "mix", "production": "mix --production"

---

### A5. Key Third-Party Packages (composer.json require)

| Package | Version |
|---|---|
| laravel/framework | ^10.0 |
| spatie/laravel-permission | ^5.5 |
| yajra/laravel-datatables-oracle | ^10.8 |
| yajra/laravel-datatables-buttons | ^10.0 |
| yajra/laravel-datatables-html | ^10.8 |
| barryvdh/laravel-dompdf | ^2.0 |
| simplesoftwareio/simple-qrcode | ^4.2 |
| maatwebsite/excel | ^3.1 |
| twilio/sdk | ^6.13 |
| webklex/laravel-imap | 5.3.0 |
| webklex/laravel-pdfmerger | ^1.3 |
| unicodeveloper/laravel-paystack | ^1.0 |
| webfox/laravel-xero-oauth2 | * |

require-dev: barryvdh/laravel-debugbar ^3.5, larastan/larastan ^2.8, phpunit/phpunit ^10.1, laravel/pint ^1.0

package.json dependencies: bootstrap ^4.3.1, sweetalert2 ^10.12.0, frappe-charts ^1.6.2, signature_pad ^3.0.0-beta.4, quill ^1.3.7, moment-timezone ^0.5.37, laravel-echo ^1.11.2, pusher-js ^7.0.3

---

## Section B — Project Structure

### B1. app/ Directory Tree (confirmed via Get-ChildItem)

```
app/
├── Actions/Fortify/
├── Console/Commands/
├── DataTables/
│   ├── Accounts/    (5 DataTable classes)
│   └── Travel/      (17 DataTable classes)
├── Http/
│   ├── Controllers/ (~195 controllers, flat namespace except Payment/, Webhook/)
│   ├── Middleware/
│   └── Requests/    (~80+ FormRequest classes by module)
├── Models/          (~230 model files, flat namespace)
├── Services/GDS/
└── View/Components/{Cards,Datatable,Filters,Forms}
```

### B2. resources/views/travel/ (confirmed via Get-ChildItem)

```
travel/
airlines/ bookings/ cash-receipts/ customer-types/ discounts/
flights/ hotel-rooms/ hotels/ iata/ insurance/ packages/
payments/ relations/ reports/ room-allocation/ sectors/
service-providers/ ticketing/ transporters/ transport-routes/
transport-types/ umrah-setup/ visa-companies/ visa-pipeline/ vouchers/
(Each non-report dir also has ajax/ subdirectory)
```

resources/views/accounts/:
account-openings/ chart-of-accounts/ exchange-rates/ financial-years/ journal-vouchers/

Shared layouts:
- resources/views/layouts/app.blade.php
- resources/views/layouts/public.blade.php
- resources/views/partials/command-palette.blade.php

### B3. database/migrations/ - Travel/Accounts migrations (2026_*)

| Migration | Tables Created |
|---|---|
| 2026_01_01_000001 | financial_years |
| 2026_01_01_000002 | chart_of_accounts |
| 2026_01_01_000003 | exchange_rates |
| 2026_01_01_000004 | account_openings |
| 2026_01_01_000005 | journal_vouchers |
| 2026_01_01_000006 | journal_voucher_lines |
| 2026_01_01_000007 | customer_types |
| 2026_01_01_000008 | Adds columns to client_details |
| 2026_01_01_000010 | service_providers, service_provider_accounts |
| 2026_01_01_000011 | iata_records, iata_accounts, iata_service_provider_links |
| 2026_01_01_000012 | visa_companies |
| 2026_01_01_000013 | transporters, transporter_accounts, transport_types, transport_routes, transport_rates |
| 2026_01_01_000014 | hotels, hotel_rates, hotel_service_provider_links, packages, package_hotels, discounts |
| 2026_01_01_000015 | flights, sectors, relations, airlines, airline_accounts |
| 2026_01_01_000016 | booking_groups, passengers, booking_charges, repeat_fees, passport_deliveries, mutamer_transfers |
| 2026_01_01_000017 | vouchers, voucher_charges, ticket_invoices, ticket_refunds, audit_logs |
| 2026_01_01_000018 | Travel agency module settings |
| 2026_07_18_000001 | Adds missing travel columns |
| 2026_07_18_000002 | hotel_rooms, room_allocations, insurance_policies, insurance_sales, cash_receipts, travel_payments |
| 2026_07_18_183417 | visa_logs |
| 2026_07_18_184111 | Travel integration fields in integration_settings |
| 2026_07_22_072149 | Adds booking_group_id to journal_voucher_lines |
| 2026_07_22_100000 | Travel module permission fixes |
| 2026_07_23_000001 | Adds logo, website to airlines |
| 2026_07_23_000002 | Adds vehicle_types to transporters |
| 2026_07_23_000003 | Adds name, is_active to discounts |
| 2026_07_23_000004 | Adds status to iata_records |
| 2026_07_28_000001 | Adds invoice columns to ticket_invoices |

routes/:
- web.php (1058 lines, all routes)
- api.php, channels.php, console.php

---

## Section C — Roles & Permissions

### C1. Gate::before Super Admin Bypass

File: app/Providers/AuthServiceProvider.php (full 34-line file confirmed):

```php
\Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
    return $user->hasRole('admin') ? true : null;
});
```

Role name checked: 'admin' (not 'super-admin', not 'Super Admin').

### C2. Seeded Roles

RoleSeeder.php seeds: one 'Manager' role per company with ALL permissions attached.
The 'admin' role (used in Gate::before) is created by CoreDatabaseSeeder - UNVERIFIED (file not read).

### C3. Authorization Pattern - Confirmed in 3 Controllers

Pattern: abort_403() helper + user()->permission() + module list check. NO Laravel Policy classes used.

BookingController.php (lines 23, 30-31, 38-39):
```
abort_403(!in_array('bookings', $this->user->modules));
$viewPermission = user()->permission('view_booking');
abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
```

VoucherController.php (lines 27, 34-35, 100, 136):
```
abort_403(!in_array('vouchers', $this->user->modules));
abort_403(!in_array(user()->permission('view_voucher'), ['all', 'added', 'owned', 'both']));
abort_if($this->voucher->isLocked(), 403, __('messages.voucherLocked'));
```

JournalVoucherController.php (lines 23, 30-31, 37):
```
abort_403(!in_array('accounts', $this->user->modules));
$viewPermission = user()->permission('view_journal_voucher');
abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
```

---

## Section D — Data Model

### D1. Accounts Module

**financial_years** (2026_01_01_000001):
id, company_id(FK companies), name, start_date, end_date, is_closed(bool, false), added_by, last_updated_by, timestamps

**chart_of_accounts** (2026_01_01_000002):
id, company_id(FK), parent_id(FK self - tree), name, code(nullable), level(int,1), type(enum: asset|liability|equity|income|expense), is_bank_account(bool,false), branch_id(no FK declared), added_by, last_updated_by, timestamps

**journal_vouchers** (2026_01_01_000005):
id, company_id(FK), financial_year_id(FK), voucher_number(unique), date, created_by(FK users), narration(text,nullable), is_balanced(bool,false), added_by, last_updated_by, timestamps
NOTE: journal_voucher_lines gets booking_group_id via 2026_07_22_072149

**account_openings** (2026_01_01_000004):
id, company_id, account_id(FK chart_of_accounts), financial_year_id(FK), opening_balance(decimal 15,2), added_by, last_updated_by

**exchange_rates** (2026_01_01_000003): UNVERIFIED - migration not read

### D2. Customers Module

**customer_types** (2026_01_01_000007):
id, company_id(FK), name, config_json(json,nullable), added_by, last_updated_by, timestamps

**client_details additions** (2026_01_01_000008) - added via whenTableDoesntHaveColumn:
- customer_type_id (FK customer_types)
- parent_customer_id (integer nullable, NO FK - B2B agent hierarchy)
- umrah_account_id (FK chart_of_accounts) - Umrah sub-ledger
- ticket_account_id (FK chart_of_accounts) - Ticketing sub-ledger
- customer_status (enum: active|blocked, default active)

KEY DESIGN: Each B2B customer gets TWO separate CoA accounts (Umrah + Ticket).

### D3. Service Providers / IATA

**service_providers** (2026_01_01_000010):
id, company_id, name, contact_person, email, phone, address, added_by, last_updated_by
Pivot: service_provider_accounts (service_provider_id -> account_id in chart_of_accounts)

**iata_records** (2026_01_01_000011):
id, company_id, name, code, contact_person, email, phone, added_by, last_updated_by
+ status column added by 2026_07_23_000004
Pivots: iata_accounts, iata_service_provider_links

### D4. Umrah Setup - Hotels, Packages, Discounts

**hotels** (2026_01_01_000014):
id, company_id, name, city, country, stars, address, phone, email, contact_person, added_by, last_updated_by

**hotel_rates** (2026_01_01_000014):
id, hotel_id(FK), room_type, season, tariff(decimal 15,2), sell_rate(decimal), valid_from, valid_to, added_by
Pivot: hotel_service_provider_links

**packages** (2026_01_01_000014):
id, company_id, name, description, price(decimal 15,2), duration_days(nullable), added_by, last_updated_by
Pivot: package_hotels (package_id -> hotel_id)

**discounts** (2026_01_01_000014 + 2026_07_23_000003):
id, company_id, applies_to_type(string - polymorphic), applies_to_id(int), discount_type(enum: percentage|fixed), value(decimal), valid_from, valid_to, name(added later), is_active(bool, added later), added_by

### D5. Lookup Tables

**airlines** (2026_01_01_000015 + 2026_07_23_000001):
id, company_id, name, code, contact_person, email, phone, logo(nullable - added), website(nullable - added), added_by, last_updated_by
Pivot: airline_accounts (airline_id -> account_id)

**flights** (2026_01_01_000015):
id, flight_number, airline, origin, destination, departure_time(dateTime), arrival_time(dateTime), added_by

**sectors** (2026_01_01_000015):
id, name, from_location, to_location, added_by

**relations** (2026_01_01_000015):
id, name, added_by

**visa_companies** (2026_01_01_000012):
id, company_id, name, contact_person, email, phone, logo_path, voucher_footer_text, voucher_footer_image, approval_sale_rate(decimal), approval_cost_rate(decimal), added_by, last_updated_by

**transporters + related** (2026_01_01_000013 + 2026_07_23_000002):
transporters: id, company_id, name, contact_person, email, phone, address, vehicle_types(added later), added_by, last_updated_by
transporter_accounts: transporter_id -> account_id
transport_types: id, name
transport_routes: id, name, from_location, to_location
transport_rates: transporter_id, transport_type_id, route_id, rate(decimal), is_ziarat(bool), added_by

### D6. Bookings

**booking_groups** (2026_01_01_000016):
id, company_id, group_no(nullable - auto-numbered ref), group_name, customer_id(FK users - B2B agent or B2C), package_id(FK nullable), iata_id(FK nullable), departure_date, return_date, notes, added_by, last_updated_by

Model BookingGroup.php: passengers() hasMany, charges() hasMany, customer() belongsTo User, package() belongsTo, iata() belongsTo IataRecord

**passengers** (2026_01_01_000016):
id, booking_group_id(FK), passport_no, first_name, family_name, birth_date, gender(enum: Male|Female, nullable), mofa_status(string, default 0 - CURRENT VISA PIPELINE STAGE), relation_id(FK nullable), mahram_passenger_id(int nullable, NO FK), added_by, last_updated_by

**booking_charges** (2026_01_01_000016):
id, booking_group_id(FK), charge_type, amount(decimal), account_id(FK chart_of_accounts nullable), description

**passport_deliveries** (2026_01_01_000016):
id, passenger_id(FK), status(enum: received|sent_for_processing|returned), timestamp(current), handled_by(FK nullable), notes

**mutamer_transfers** (2026_01_01_000016):
id, passenger_id(FK), from_booking_group_id(FK nullable), to_booking_group_id(FK nullable), transferred_by(FK nullable), transferred_at(timestamp)

**repeat_fees** (2026_01_01_000016):
id, passenger_id(FK), fee_amount(decimal), rule_applied(nullable), added_by

### D7. Vouchers

**vouchers** (2026_01_01_000017):
id, company_id, booking_group_id(FK nullable), type(enum: accommodation|transport|full), status(enum: draft|locked|issued), charges_total(decimal), pdf_path(nullable), voucher_number(nullable), added_by, last_updated_by

Model Voucher.php:
- Constants: TYPES, STATUSES
- Methods: isLocked(), scopeDraft(), scopeLocked()
- Relationships: bookingGroup(), charges() hasMany VoucherCharge, lockedBy() belongsTo User
- WARNING: $casts has 'locked_at' => 'datetime' but no locked_at column exists (See K2)

**voucher_charges** (2026_01_01_000017):
id, voucher_id(FK), description, amount(decimal), added_by

**audit_logs** (2026_01_01_000017):
id(bigIncrements), user_id(FK nullable), action, entity_type, entity_id(nullable), before(json), after(json), ip_address(nullable), timestamps

### D8. Ticketing

**ticket_invoices** (2026_01_01_000017 + 2026_07_28_000001):
id, company_id, booking_group_id(FK nullable), airline_id(FK nullable), passenger_id(FK nullable), amount(decimal), sector_id(FK nullable), ticket_number(nullable), added_by, last_updated_by
+ additional invoice columns from 2026_07_28_000001 (UNVERIFIED exact columns)

**ticket_refunds** (2026_01_01_000017):
id, ticket_invoice_id(FK), refund_amount(decimal), reason(text nullable), added_by

### D9. Insurance

**insurance_policies** (2026_07_18_000002):
id, company_id, provider_name, policy_type(e.g. travel|medical|hajj), policy_number(nullable), rate(decimal - per-passenger), currency_code(default PKR), coverage_summary(nullable), terms_conditions(text), valid_from, valid_to, is_active(bool,true), added_by

**insurance_sales** (2026_07_18_000002):
id, company_id, passenger_id(FK), policy_id(FK insurance_policies RESTRICT), booking_group_id(FK nullable), amount(decimal), currency_code(default PKR), account_id(FK chart_of_accounts nullable), certificate_number(nullable), status(enum: active|claimed|cancelled), added_by

### D10. Room Allocation

**hotel_rooms** (2026_07_18_000002): UNVERIFIED exact columns - migration truncated. Confirmed exists, linked to hotel_id.

**room_allocations** (2026_07_18_000002):
hotel_room_id(FK hotel_rooms), passenger_id(FK nullable), booking_group_id(FK nullable), notes(text nullable), allocated_by(FK nullable), timestamps

### D11. Visa Pipeline

**visa_logs** (2026_07_18_183417):
id, company_id, passenger_id(FK), from_status(nullable), to_status, changed_by(FK nullable), remarks(text nullable), timestamps

NOTE: passengers.mofa_status = current stage. visa_logs = history of transitions.

### D12. Payments

**cash_receipts** (2026_07_18_000002):
id, company_id, account_id(FK chart_of_accounts nullable - cash account), amount(decimal), currency_code(PKR), date, received_from, reference_no(nullable), narration(text), journal_voucher_id(FK nullable - auto-created JV), added_by, last_updated_by

**travel_payments** (2026_07_18_000002):
id, company_id, payment_direction(enum: receive|make), party_user_id(FK nullable), debit_account_id(FK nullable), credit_account_id(FK nullable), amount(decimal), currency_code(PKR), exchange_rate(decimal 15,6 default 1), amount_base_currency(decimal), payment_date, payment_method(enum: cash|bank_transfer|cheque|online), reference_no, cheque_no, narration, booking_group_id(FK nullable), journal_voucher_id(FK nullable - auto-created JV), status(enum: draft|posted|cancelled), added_by, last_updated_by

---

## Section E — Routes

### E1. route:list --columns Flag Failure

Command: php artisan route:list --columns=method,uri,name,action
Result: The "--columns" option does not exist.
Laravel 10 does not support --columns on route:list.

### E2. Travel Routes (from routes/web.php lines 422-540+, confirmed)

All travel routes are inside the main auth middleware group.

```
Route::resource('customer-types', CustomerTypeController::class);
Route::resource('accounts', AccountController::class);
Route::resource('financial-years', FinancialYearController::class);
Route::get('chart-of-accounts/tree', ...)->name('chart-of-accounts.tree');
Route::resource('chart-of-accounts', ChartOfAccountController::class);
Route::resource('journal-vouchers', JournalVoucherController::class);
Route::get('journal-vouchers/{id}/print', ...)->name('journal-vouchers.print');
Route::resource('account-openings', AccountOpeningController::class);
Route::resource('exchange-rates', ExchangeRateController::class);
Route::get('accounts/trial-balance', ...)->name('accounts.trial_balance');
Route::get('accounts/ledger', ...)->name('accounts.ledger');
Route::resource('service-providers', ServiceProviderController::class);
Route::resource('iata', IataController::class);
Route::resource('visa-companies', VisaCompanyController::class);
Route::resource('transporters', TransporterController::class);
Route::resource('airlines', AirlineController::class);
Route::resource('hotels', HotelController::class);
Route::resource('packages', PackageController::class);
Route::resource('discounts', DiscountController::class);
Route::resource('transport-types', TransportTypeController::class);
Route::resource('transport-routes', TransportRouteController::class);
Route::resource('flights', FlightController::class);
Route::resource('sectors', SectorController::class);
Route::resource('relations', RelationController::class);
Route::get('umrah-setup', [UmrahSetupController::class, 'index'])->name('umrah-setup.index');
Route::resource('bookings', BookingController::class);
Route::post('bookings/{id}/passengers', ...)->name('bookings.passengers.add');
Route::delete('bookings/{booking}/passengers/{passenger}', ...)->name('bookings.passengers.remove');
Route::get('bookings/import', ...)->name('bookings.import.page');
Route::post('bookings/import/parse', ...)->name('bookings.import.parse');
Route::post('bookings/import/commit', ...)->name('bookings.import.commit');
Route::resource('vouchers', VoucherController::class);
Route::post('vouchers/{id}/issue', ...)->name('vouchers.issue');
Route::post('vouchers/{id}/lock', ...)->name('vouchers.lock');
Route::get('vouchers/{id}/pdf', ...)->name('vouchers.pdf');
Route::get('vouchers/{id}/qr', ...)->name('vouchers.qr');
Route::resource('ticketing', TicketInvoiceController::class);
Route::post('ticketing/{id}/refund', ...)->name('ticketing.refund');
Route::get('visa-pipeline', ...)->name('visa-pipeline.index');
Route::post('visa-pipeline/move', ...)->name('visa-pipeline.move');
Route::post('visa-pipeline/mofa-ref', ...)->name('visa-pipeline.mofa-ref');
Route::get('room-allocation', ...)->name('room-allocation.index');
Route::post('room-allocation/assign', ...)->name('room-allocation.assign');
Route::post('room-allocation/remove', ...)->name('room-allocation.remove');
// travel-payments prefix group: receive.index, receive.create, receive.store, make.index, make.create, make.store, cancel
Route::resource('hotel-rooms', HotelRoomController::class);
Route::resource('insurance-policies', InsurancePolicyController::class);
Route::resource('insurance-sales', InsuranceSaleController::class);
// travel-reports prefix group: trial-balance, profit-loss, monthly-pl, ageing, receivables, payables, umrah-wise-pl, export
```

### E3. Notable Route Gaps

- UmrahSetupController: single GET only (not resource). Only index() confirmed.
- PassportController (line 242): Route::resource('passport', ...) refers to EMPLOYEE HR passports, NOT travel passenger passports.
- VisaPipelineController: 3 custom routes only (no standard resource).

---

## Section F — Controllers

### F1. BookingController (app/Http/Controllers/BookingController.php)

Methods confirmed from grep: index, create, store, show, edit, update, destroy, addPassenger, removePassenger, importPage, importParse, importCommit

Auth on each method: abort_403(!in_array('bookings', $this->user->modules)) + user()->permission('view/add/edit/delete_booking')

### F2. VoucherController (app/Http/Controllers/VoucherController.php)

Methods: index, create, store, show, edit, update, destroy, issue, lock, generatePdf, qrCode

Secondary guard (lines 100, 118, 136, 150, 173):
abort_if($voucher->isLocked(), 403, __('messages.voucherLocked'))

### F3. JournalVoucherController (app/Http/Controllers/JournalVoucherController.php)

Methods: index, create, store, show, edit, update, destroy, print
Auth: module 'accounts' check + view/add/edit/delete_journal_voucher permissions

### F4. TravelReportController

Methods from routes: trialBalance, profitLoss, monthlyProfitLoss, ageing, receivables, payables, umrahWisePl, export
UNVERIFIED: controller body not read - unknown if all methods are fully implemented or stubs.

### F5. VisaPipelineController

Methods from routes: index, move, setMofaRef
UNVERIFIED: controller body not read.

---

## Section G — Frontend/Views

### G1. Shared Layout Files

- resources/views/layouts/app.blade.php (main authenticated layout)
- resources/views/layouts/public.blade.php (unauthenticated)
- resources/views/partials/command-palette.blade.php

### G2. DataTable Classes - Complete List

app/DataTables/Accounts/ (5 files):
AccountOpeningDataTable.php, ChartOfAccountDataTable.php, ExchangeRateDataTable.php, FinancialYearDataTable.php, JournalVoucherDataTable.php

app/DataTables/Travel/ (17 files):
AirlineDataTable.php, BookingDataTable.php, CustomerTypeDataTable.php, DiscountDataTable.php, FlightDataTable.php, HotelDataTable.php, IataDataTable.php, PackageDataTable.php, RelationDataTable.php, SectorDataTable.php, ServiceProviderDataTable.php, TicketInvoiceDataTable.php, TransporterDataTable.php, TransportRouteDataTable.php, TransportTypeDataTable.php, VisaCompanyDataTable.php, VoucherDataTable.php

MISSING DataTable classes (modules with controllers+routes but no DataTable):
InsurancePolicyDataTable, InsuranceSaleDataTable, RoomAllocationDataTable, HotelRoomDataTable, VisaPipelineDataTable, TravelPaymentDataTable, CashReceiptDataTable

### G3. UI Inconsistencies

UNVERIFIED: Tile-contrast issue in Umrah Setup menu mentioned in prior sessions not re-checked. To verify: inspect resources/views/travel/umrah-setup/index.blade.php.

---

## Section H — Translations

### H1. Languages Present (30+ confirmed)

ar, bg, cs, de, el, en, eng, es, et, fa, fr, hi, id, it, ja, ka, ko, nl, pl, pt, pt-br, ro, ru, sq, sr, th, tr, uk, vi, zh-CN, zh-TW

Files per language: app.php, auth.php, email.php, installer_messages.php, messages.php, modules.php, pagination.php, passwords.php, permissions.php, placeholders.php, validation.php

### H2. Travel-Specific Keys Confirmed in en/messages.php (lines 507-516)

'voucherNotBalanced' => 'Journal voucher lines are not balanced...',
'voucherIssued' => 'Voucher issued successfully.',
'voucherLocked' => 'This voucher is locked and cannot be modified.',
'confirmIssueVoucher' => 'Are you sure you want to issue this voucher?...',
'confirmLockVoucher' => 'Are you sure you want to lock this voucher?...',
'insuranceSaleRecorded' => 'Insurance sale recorded successfully.',
'passportDeliveryUpdated' => 'Passport delivery status updated.'

### H3. Missing Key Check

Grep scan of messages.* key usage in resources/views/travel/ vs en/messages.php:
RESULT: 0 missing keys found for messages.* namespace.
NOT CHECKED: app.*, modules.*, placeholders.* namespaces in travel views.

---

## Section I — Seeders / Demo Data

### I1. Seeder Files (34 total in database/seeders/)

Key travel seeders: DemoDataSeeder.php, TravelModuleDefaultDisableSeeder.php, RoleSeeder.php

### I2. RoleSeeder.php

Seeds one 'Manager' role per company with ALL permissions attached.

### I3. DemoDataSeeder.php

Safety: Refuses to run if APP_ENV=production.
All demo passwords: Password123!
Creates: Company, ChartOfAccount, FinancialYear, CustomerType, ServiceProvider, Transporter, IataRecord, VisaCompany, Hotel, HotelRoom, Package, InsurancePolicy, InsuranceSale, Users (staff + clients), BookingGroup, Passenger, RoomAllocation, JournalVoucher, JournalVoucherLine, Voucher, VisaLog

### I4. docs/TEST_ACCOUNTS.md - EXISTS (confirmed)

Internal Staff:
- Super Admin: superadmin@zapflow.test / Password123! (Gate::before bypass)
- Admin: admin@zapflow.test
- Accountant: accountant@zapflow.test (Accounts module only)
- Visa Officer: visaofficer@zapflow.test (Booking view + Visa Pipeline)
- Ops Staff: opsstaff@zapflow.test (Booking + Room Allocation + Passport Delivery)
- Ticketing Staff: ticketing@zapflow.test

External/Client:
- B2B Agent 1: agent1@zapflow.test (owns BG-2026-001, 4 passengers)
- B2B Agent 2: agent2@zapflow.test (owns BG-2026-002, 2 passengers)
- Sub-Agent: subagent1@zapflow.test (parented to Agent-1)
- B2C Customer: customer@zapflow.test

Seeded JVs: JV-DEMO-001, JV-DEMO-002, JV-DEMO-003 (all balanced, PKR).
Seeded Vouchers: VCH-2026-0001 (draft).

---

## Section J — Test Coverage

### J1. Test Files

tests/Feature/BookingScopingTest.php
tests/Feature/ExampleTest.php
tests/Feature/JournalVoucherPermissionTest.php
tests/Unit/ExampleTest.php

### J2. php artisan test Results (run 2026-08-27)

Output:
  Tests:    4 failed, 1 passed (2 assertions)
  Duration: 22.17s

Primary failure (JournalVoucherPermissionTest + others):
BadMethodCallException: SQLite doesn't support multiple calls to dropColumn / renameColumn
in a single modification.
at vendor\laravel\framework\src\Illuminate\Database\Schema\Blueprint.php:162

Root cause: Tests use SQLite in-memory DB. Multiple migrations use multi-column dropColumn/renameColumn in single modification - incompatible with SQLite. App runs on MySQL (fine). Test environment misconfigured.

---

## Section K — Known Issues Log

### K1. Test Suite Broken (SQLite Incompatibility)
Evidence: php artisan test -> BadMethodCallException at Blueprint.php:162
Impact: 4 of 5 tests fail. No reliable automated test coverage.
Fix needed: Reconfigure phpunit.xml to use MySQL, OR make problematic migrations SQLite-compatible.

### K2. Voucher Model $casts Key Mismatch
File: app/Models/Voucher.php
Evidence: $casts = ['locked_at' => 'datetime'] but migration 2026_01_01_000017 has no locked_at column. Column locked_by (integer FK) exists, no locked_at timestamp.
Impact: $voucher->locked_at always returns null; date-formatting silently fails.

### K3. route:list --columns Not Supported
Evidence: php artisan route:list --columns=... -> "The '--columns' option does not exist."
Impact: Cannot use this flag in Laravel 10 for filtered route output.

### K4. mahram_passenger_id Has No FK Constraint
File: database/migrations/2026_01_01_000016_create_booking_tables.php
Evidence: $table->integer('mahram_passenger_id')->unsigned()->nullable() - no foreign() call.
Impact: Orphan mahram references survive passenger deletion. No referential integrity.

### K5. No DataTable Classes for 7 Travel Sub-Modules
Evidence: app/DataTables/Travel/ directory listing - missing InsurancePolicyDataTable, InsuranceSaleDataTable, RoomAllocationDataTable, HotelRoomDataTable, VisaPipelineDataTable, TravelPaymentDataTable, CashReceiptDataTable.
Impact: List pages either use inline Eloquent (inconsistent) or are incomplete.

### K6. Gate::before 'admin' Role Seeding Origin Unclear
File: app/Providers/AuthServiceProvider.php
Evidence: $user->hasRole('admin') - but RoleSeeder seeds only 'Manager'. 'admin' role origin is CoreDatabaseSeeder (UNVERIFIED).
Risk: Silent bypass failure if role name changes or seeding skipped.

---

## Section L — Module Status Table (Re-Verified Live: 2026-09-14 — supersedes the 2026-08-31 table)

**Evidence standard applied here:** "Live-verified" = a real HTTP request was made against the running app this session and the real response is quoted in `docs/FINAL_PROJECT_REPORT.md` v3. "Code-verified" = the actual controller/service method body was read this session and the real logic quoted, but no live request was made. Neither of these is "trust the previous report" — every row below was independently re-derived on 2026-09-14.

| Module | Status | Evidence | Known Gaps / Notes |
|---|---|---|---|
| Employees / HR | Code-verified | EmployeeController, routes confirmed | Base-product `UsersTableSeeder.php` has the same blank-dashboard bug as the fixed DemoDataSeeder one (see below) — not fixed, out of travel-module scope |
| Login / Dashboard (Super Admin, Admin) | **Was broken — fixed and live-verified** | `DashboardController::index()` returned an empty 200 for any non-employee, non-client role. Demo seeder didn't grant Super Admin/Admin the employee role needed to render anything. Fixed in `DemoDataSeeder.php`; live-verified via real login + screenshot | This was the actual entry point to the whole system and was silently broken before this session |
| Customers (Customer Types + dual sub-ledgers) | Code-verified | customer_types (3 rows, freshly seeded), client_details with umrah_account_id & ticket_account_id | — |
| Service Providers / IATA / Visa Companies / Transporters / Airlines | Code-verified | Controllers + DataTables confirmed present and wired | Not live-clicked this session |
| Accounts - Chart of Accounts | Code-verified | 13 rows after this session's fix (added a `retained_earnings` equity account — see Financial Years row) | — |
| Accounts - Financial Years (close, roll-forward, idempotency) | **Was fatally broken — fixed and live-verified** | `close()` called `DB::` without importing the facade — a guaranteed 500 on every call. Fixed; live-verified end-to-end: real P&L computed (280,000 income − 80,000 expense = 200,000), rolled forward into the next year's Retained Earnings opening balance, double-close blocked, new JV blocked from posting into the closed year | The demo chart of accounts had **no equity account at all** before this session, so even after the code fix, the roll-forward step had nowhere to post to — added one |
| Accounts - Journal Vouchers | **Partially fixed, live-verified** | `store()`/`update()` balance + closed-year checks confirmed real; `destroy()` was missing the closed-year guard and audit logging other JV writes have — added both. Live-verified: JV correctly blocked from a closed year, correctly accepted into an open one | — |
| Accounts - Account Openings / Exchange Rates | **Was vulnerable — fixed** | `store()`/`destroy()` (Account Openings) and `store()`/`destroy()` (Exchange Rates) had **no permission check at all** | Fixed with `abort_403` matching the existing pattern on their own `edit()`/`update()` methods |
| Accounts - Payments (Travel & Cash Receipts) | **Partially fixed** | `CashReceiptController::store()`/`destroy()` had no audit logging — added it | `destroy()` still doesn't reverse/delete the linked journal voucher (pre-existing, not fixed) |
| Umrah Setup - Packages (auto-calculated pricing) | **Was gameable — fixed** | Calculation engine itself confirmed real (pulls actual hotel/transporter/visa data); but a manual price could silently coexist with linked components whenever the `auto_calculate` checkbox was left unchecked | Fixed: price is now always server-recalculated once any pricing component is linked |
| Umrah Setup - Hotels / Discounts / lookup tables | Code-verified | Controllers + DataTables confirmed present | Not live-clicked this session |
| Umrah Setup - Hotel Rooms / Room Allocation | **Live-verified** | Gender restriction and capacity enforcement both confirmed server-side via real requests: female→male-only room rejected, valid same-gender assignment accepted, third passenger into a full 2-capacity room rejected | Genuinely server-enforced, not just UI |
| Ticketing (`sale_type`) | Code-verified | `bsp`/`xo`/`direct` validated, persisted, and filterable in the DataTable — confirmed by reading the actual controller/DataTable code | Not live-clicked this session |
| Insurance | Code-verified | Controllers confirmed present with `abort_403` guards | Not live-clicked this session |
| Booking (CSV import, Mutamer Transfers, Passport Delivery) | Code-verified | Genuine two-step preview/commit confirmed by reading `BookingImportService` — nothing is written to the DB until the explicit commit step | Not live-clicked this session |
| Vouchers (lock, HMAC QR) | **Was racy — fixed, live-verified** | `lock()`/`issue()` fetched the voucher without `lockForUpdate()` — a real TOCTOU race. Fixed. Live-verified: lock succeeds once, a subsequent edit is rejected (403), a subsequent re-lock is rejected (403) | HMAC-SHA256 QR signing confirmed real (uses `APP_KEY`) |
| Reports (Umrah P&L + Financials) | Code-verified | `UmrahPlCalculator` confirmed pure-SQL aggregation, correctly excludes non-`posted` payments from cost | Not live-pulled as an authenticated user this session |
| Visa Pipeline | Code-verified | Every transition writes `visa_logs` + fires a genuine `ShouldBroadcastNow` event; a non-drag dropdown fallback exists in the Blade view | Not live-clicked this session |
| Security hardening (8 named risk areas) | **6 real vulnerabilities found and fixed this session** | See `docs/FINAL_PROJECT_REPORT.md` Section 4 for the full audit: mass assignment (User/ClientController), missing authorization (AccountOpening/ExchangeRate destroy), voucher lock race condition, file upload validation (ClientDocs/EmployeeDocs), an unmasked dead-code passport column, and incomplete audit-log coverage | Audit log coverage remains partially incomplete — see Section 7 of the final report |

---

*Live re-verification completed: 2026-09-14. Full evidence trail, exact commands, and exact HTTP responses: `docs/FINAL_PROJECT_REPORT.md` (v3). Honest final verdict there, not repeated here as a bare "READY" — several genuinely critical bugs were found and fixed this session that the 2026-08-31 "READY" verdict above did not catch.*

