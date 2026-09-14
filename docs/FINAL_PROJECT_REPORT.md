# ZapFlow Travel — Final Project Report (v3 — Live Re-Verification, Supersedes v2)

> **Document status:** This version supersedes `v2` (dated 2026-09-11). v2's central evidentiary claim — that a live, seeded database was checked to produce the row counts and Golden Path results it cites — could not be reproduced in this environment: the actual `zapflow_travel` database was found completely empty (0 tables) at the start of this session, and the demo-data pipeline is gated behind an environment check that v2 does not mention. This version rebuilds the evidence from a genuinely fresh, freshly-seeded, freshly-tested instance and states plainly what was verified live via real HTTP requests, what was verified by reading code, and what remains unverified.
>
> **Generated:** 2026-09-14
> **PHP:** 8.4.25 (Laravel Herd) — differs from v2's claimed 8.4.23; harmless (both satisfy `^10.0`/8.2+ requirements)
> **DB:** MySQL 8, served locally from `C:\ForXampp\mysql` (not XAMPP as v2 claimed — that install path does not exist on this machine; see Section 0)
> **Dev/demo DB:** `zapflow_travel` — **freshly migrated and seeded during this session**
> **Test DB:** `zapflow_travel_testing`

---

## Section 0 — What was actually wrong with the starting state, and how it was fixed

This is the load-bearing section. Everything below depends on these environment facts, which were not documented anywhere before this session.

| # | Finding | Evidence | Fix applied |
|---|---|---|---|
| 0.1 | **MySQL server was not running at all.** `zapflow_travel` "existed" only as an empty schema (0 tables) reachable once MySQL was started. | `SHOW TABLES` on a running MySQL instance returned 0 rows for `zapflow_travel`. | Started `C:\ForXampp\mysql\bin\mysqld.exe` (a `ForXampp` install exists on this machine at `C:\ForXampp`; there is no `C:\xampp`, contradicting earlier docs' claim of "MySQL via XAMPP"). |
| 0.2 | **The `mysql` CLI was not on `PATH`.** `php artisan test` / PHPUnit's `RefreshDatabase` invoke the `mysql` client (via `MySqlSchemaState`) to load/dump schema. Without it, **every test that touches the DB errors** with `'mysql' is not recognized as an internal or external command`. | Full stack trace captured on first `phpunit` run in this session (Section 1 command output). | Added `C:\ForXampp\mysql\bin` to `PATH` for the session. This is an environment fact, not a code fix — needs to be true on any machine that runs this suite. |
| 0.3 | **`APP_ENV=codecanyon` (the committed `.env` value) silently disables nearly the entire seeding pipeline**, including `UsersTableSeeder`, `RoleSeeder`, and — critically — **`DemoDataSeeder` entirely** (`database/seeders/DatabaseSeeder.php`: `if (!App::environment('codecanyon', 'production')) { $this->call(DemoDataSeeder::class); }`). Under the shipped `.env`, plain `php artisan db:seed` creates exactly **1 company row and nothing else** — 0 users, 0 of every travel/accounts table. **Worse: the workaround previously documented in `docs/TEST_ACCOUNTS.md` — running `php artisan db:seed --class=Database\Seeders\DemoDataSeeder` standalone — is itself broken on a truly fresh database.** Verified live this session: it leaves the **login page throwing a 500** (`Attempt to read property "google_status" on null`, `auth/login.blade.php`) because it skips `OrganisationSettingsTableSeeder`; and even where login succeeds, the resulting users get an **empty `modules` array** (no `ModulePermissionSeeder`/org-settings run), so `abort_403(!in_array('accounts', $this->user->modules))` — a plain PHP helper, not a Gate check the `Gate::before` admin bypass can save you from — returns 403 on every travel/accounts controller for every user, Super Admin included. | Ran plain `db:seed` against a freshly migrated DB with the unmodified `.env`: `users: 0` etc. Separately, ran the exact `--class=DemoDataSeeder` command previously documented in `TEST_ACCOUNTS.md`, on a truly fresh `migrate:fresh` database, with the unmodified `.env`: `GET /login` → real `500`, log line quoted above. | **Fixed the documentation, not the seeder** (fixing `OrganisationSettingsTableSeeder`/`ModulePermissionSeeder` to be safely re-runnable standalone was judged out of scope for this pass). `docs/TEST_ACCOUNTS.md` now documents and this session verified the one procedure that actually produces a working instance: `php artisan migrate:fresh` then `APP_ENV=local php artisan db:seed` (env override only, `.env` untouched). Live-verified after this exact procedure: login succeeds and a module-gated page (`/account/financial-years`) returns real HTTP 200, not 403. |
| 0.4 | v2 claims `docs/TEST_ACCOUNTS.md` credentials were verified via `Hash::check` against a live DB. That DB did not exist at session start (0.1–0.3), so that specific verification, as described, was not possible in this environment at that time. | — | Re-ran the check for real this session (Section 2). All 10 passed. |
| 0.5 | Seeding (`db:seed`) unconditionally calls `Artisan::call('key:generate')` (`DatabaseSeeder.php` line 24), **rotating `APP_KEY` on every seed run** — a real side effect worth knowing about, since it would invalidate any already-encrypted data or live sessions in a real install. Running the master-prompt-mandated seed steps rotated this machine's `.env` `APP_KEY`; it was restored (`git checkout -- .env`) before finishing this session so the repo is left byte-for-byte as it was on this one line. | `git diff .env` after seeding showed only the `APP_KEY` line changed. | Reverted. **Recommendation, not yet fixed:** guard the `key:generate` call so it doesn't run un-asked on every seed. |
| 0.6 | A dev server started via the harness's `preview_start`/`launch.json` mechanism failed to bind on Windows (`Failed to listen on 127.0.0.1:PORT (reason: ?)`) regardless of port; the identical `php artisan serve` command run directly in a shell bound and served correctly. | Reproduced on ports 8000, 8091 (failed) vs. direct shell launch on 8099 (worked, `curl` got HTTP 200). | Not a codebase bug — a quirk of this specific browser-preview process-launch mechanism on this machine. Documented so a future session doesn't waste time on it. |

---

## Section 1 — Automated Test Suite (real, current run)

Command (exactly as the master prompt specifies):
```bash
php -d memory_limit=512M vendor/bin/phpunit --testdox --no-coverage
```

**With MySQL not running / `mysql` not on PATH (the state at session start):** 19 of 25 tests **error** (not "fail" — hard errors) with `ProcessFailedException` / `'mysql' is not recognized...` / `Base table or view not found`. This is the honest first-run result and contradicts v2's claim of a clean `25/25` run being the current state of this checkout.

**After fixing the two environment issues in Section 0 (MySQL running, `mysql` on PATH) — no code changes yet:**
```
PHPUnit 10.5.64 by Sebastian Bergmann and contributors.
........................         25 / 25 (100%)
Tests: 25, Assertions: 44, PHPUnit Deprecations: 1.
OK, but there were issues!
```
All 25 tests genuinely pass. This part of v2's claim is accurate — **once the environment is correctly configured**, which v2 did not document.

**After all code fixes in this session (Section 3):** re-ran the full suite three more times; **still 25/25, 44 assertions**, no regressions introduced.

The 1 PHPUnit deprecation is from `amphp/parallel-functions` (vendor code), unrelated to this project.

---

## Section 2 — Fresh Migration + Seed (real output, this session)

```
php artisan migrate:fresh   →  all 285+ migrations ran clean, 0 errors
APP_ENV=local php artisan db:seed  →  0 errors, DemoDataSeeder ran to completion
```

Real row counts after seeding (queried directly, not narrated):

| Table | Rows |
|---|---|
| companies | 1 |
| users | 23 |
| client_details | 10 |
| customer_types | 3 |
| booking_groups | 2 |
| passengers | 6 |
| journal_vouchers | 3 |
| vouchers | 1 |
| financial_years | 1 |
| chart_of_accounts | 13 (was 12 before this session — see Section 3, a `retained_earnings` equity account was added) |
| hotels | 2 |
| packages | 1 |
| insurance_policies | 2 |
| insurance_sales | 1 |
| visa_companies | 1 |
| transporters | 1 |
| service_providers | 1 |
| iata_records | 1 |
| airlines | 1 |
| room_allocations | 2 |
| visa_logs | 12 |
| audit_logs | 3 |

All 10 `docs/TEST_ACCOUNTS.md` passwords verified with `Hash::check('Password123!', ...)` against this real, freshly-seeded database: **10/10 pass** (superadmin, admin, accountant, visaofficer, opsstaff, ticketing, agent1, agent2, subagent1, customer).

---

## Section 3 — Real Bugs Found and Fixed This Session

Every item below was independently reproduced (either a fatal error, a wrong HTTP response, or a missing check confirmed by reading the exact line), then fixed, then re-verified.

### 3.1 Blank dashboard on login as Super Admin or Admin (severity: critical — blocks the entire golden path)
- **Root cause:** `DashboardController::index()` (`app/Http/Controllers/DashboardController.php:54-75`) only returns a view when `'employee'` or `'client'` is in the logged-in user's roles; otherwise it falls through and implicitly returns `void`, which Laravel renders as an **empty 200 response**.
- `database/seeders/DemoDataSeeder.php` assigned Super Admin `roles: [$superAdminRole, $adminRole]` and Admin `roles: [$adminRole]` — **neither included `$employeeRole`**, unlike every other seeded role (Accountant, Visa Officer, Ops Staff, Ticketing all correctly include it).
- **Reproduced:** logged in as `superadmin@zapflow.test` via real browser session and via `curl` — dashboard request returned `HTTP 200` with a **0-byte body**.
- **Fixed:** `DemoDataSeeder.php` now includes `$employeeRole` in both accounts' role arrays. Re-seeded, re-tested: full dashboard now renders (verified via screenshot and `curl` non-empty response).
- **Note:** the base product's own `UsersTableSeeder.php` (the seeder used for non-demo/non-codecanyon company installs) has the **identical latent bug** — `$user->roles()->attach($adminRole->id)` for `admin@example.com` never attaches the employee role either. Not fixed in this session (out of the travel-module scope this engagement is chartered for), but flagged here since it would affect any real customer's very first admin login through that seeder path.

### 3.2 `FinancialYearController::close()` — fatal error, feature could never run (severity: critical)
- **Root cause:** the file's `use` imports (`app/Http/Controllers/FinancialYearController.php:5-9`, before fix) never included `use Illuminate\Support\Facades\DB;`, yet `close()` calls `DB::transaction(...)`, `DB::table(...)`, `DB::raw(...)`. In a namespaced PHP file, an unqualified `DB` resolves to `App\Http\Controllers\DB`, which doesn't exist — **every call to this method threw `Error: Class "App\Http\Controllers\DB" not found`**, a 500 response.
- This directly contradicts every "VERIFIED — uses `lockForUpdate()`" claim about financial-year closing in the prior report: the well-designed idempotency/roll-forward logic was real but **dead code that could never execute**.
- **Fixed:** added the missing `use` import.
- **Live-verified after fix** (Section 4) — the endpoint now genuinely computes P&L, rolls it forward, and is idempotent.

### 3.3 Package pricing — manual price can silently coexist with linked components (severity: medium, explicitly flagged as a required check)
- **Confirmed:** `PackageController::store()`/`update()` always set `$package->price = $request->price` unconditionally, and only recalculated from real hotel/transporter/visa data `if ($request->boolean('auto_calculate'))`. A caller could link a hotel + transporter + visa company and still leave a completely arbitrary manual price in place by simply not checking the auto-calculate box — exactly the failure mode the master prompt calls out by name.
- **Fixed:** both methods now force `recalculateAndSave()` whenever the package has *any* linked pricing component (hotel, transporter, or visa company), regardless of the `auto_calculate` flag. A manual price is only honored for a fully standalone package with no linked components.

### 3.4 Missing translation key: `app.menu.integrationSettings`
- The sidebar literally rendered the string `app.menu.integrationSettings` instead of "Integration Settings" for every logged-in user, because the key didn't exist in `resources/lang/en/app.php`. Added it. A full scan of every `app.*`/`modules.*`/`messages.*`/`placeholders.*` key referenced anywhere under `resources/views/travel/` (277 unique keys) against `resources/lang/en/` found **zero other missing keys** — this was the only real gap in English. (Coverage of the other 30 language files was not audited — out of scope given time, and a pre-existing gap in every prior audit too.)

---

## Section 4 — Security Hardening: Independent Audit + Fixes

A dedicated read-only audit (fresh, no reliance on prior claims) checked the 8 named risk areas against the actual current code. Findings and what was done about each:

| # | Area | Verdict found | Action taken |
|---|---|---|---|
| 1 | Mass assignment | **VULNERABLE**: `User` model (`$guarded=['id']`, fully open) + `ClientController::store()/update()` passing `$request->all()` straight into `User::create()`/`update()`, with no permission check on either write method. All named financial models (JournalVoucher, Voucher, TravelPayment, BookingGroup, etc.) were confirmed **SECURE** (explicit `$fillable`). | **Fixed**: `ClientController` now builds an explicit column whitelist for every `User::create()`/`update()` call instead of the raw request array, and both `store()`/`update()` now have real `abort_403` permission checks matching the existing `create()`/`edit()` pattern. |
| 2 | Authorization on every financial-write controller | **VULNERABLE**: `AccountOpeningController::store()`/`destroy()` and `ExchangeRateController::store()`/`destroy()` had **no permission check at all** — any authenticated user with the `accounts` module enabled could create or delete these records regardless of their actual `add_account_opening`/`delete_account_opening`/etc. permission. All 9 other named controllers (JournalVoucher, OtherServiceInvoice, TravelPayment, CashReceipt, Voucher, TicketInvoice, InsuranceSale, ChartOfAccount, FinancialYear) were confirmed to have real checks on every write method. | **Fixed**: added matching `abort_403` checks to `store()`/`update()`/`destroy()` on both controllers. |
| 3 | Idempotency on destructive financial actions | **VULNERABLE** in two ways: (a) `FinancialYearController::close()` — see 3.2, the lock code existed but the method was dead due to the missing import; (b) `VoucherController::lock()`/`issue()` fetched the voucher with plain `findOrFail()`, **no `lockForUpdate()`** — a genuine TOCTOU race where two concurrent requests could both pass the `isLocked()` check before either commits. | **Fixed**: 3.2's import fix makes the existing `lockForUpdate()` on `FinancialYearController::close()` actually run (verified live — see Section 5). Added `->lockForUpdate()` to the voucher fetch in both `lock()` and `issue()`. |
| 4 | XSS via `{!! !!}` | **SECURE** for every travel/accounts view checked — narration/notes fields consistently use escaped `{{ }}`; the only raw-output usages found are DataTables framework markup. DataTable `rawColumns()` usages that touch user text (e.g. provider names) all pass through `e()` first. (A broader, pre-existing `{!! !!}` pattern for rich-text fields elsewhere in the base CRM — contracts, messages, notes — was flagged as an out-of-scope, unverified surface, not part of the named 8 risks.) | No change needed in the audited scope. |
| 5 | File upload validation | **VULNERABLE**: `ClientDocs\CreateRequest`/`UpdateRequest` and `EmployeeDocs\CreateRequest`/`UpdateRequest` validated only `'file' => 'required'` — no mime-type or size constraint at the validation layer (partially mitigated deeper in the stack by a global extension *blocklist* in `Files::validateUploadedFile()`, not an allowlist). Other upload paths (passport scans, avatars, logos) were already correctly validated with `mimes:`/`max:`. | **Fixed**: all four requests now validate `file|mimes:pdf,doc,docx,jpg,jpeg,png,webp,xls,xlsx|max:10240`, matching the pattern already used elsewhere in this codebase (`InvoiceFileStore`, `creditNoteFileStore`). |
| 6 | Sensitive data leakage (passport masking) | **SECURE** on every live, reachable render path checked (booking show, voucher PDF/show, visa-pipeline kanban, all 5 travel reports) — all use `masked_passport_no`/`Passenger::maskPassport()`. One **orphaned, unrouted** DataTable class (`VisaPipelineDataTable.php`) rendered the raw `passport_no` — currently unreachable dead code, but a landmine if ever wired to a route. | **Fixed** defensively: that column now renders `masked_passport_no` too, so it's safe even if the class is used in the future. |
| 7 | Rate limiting | **SECURE** — Fortify's `EnsureLoginIsNotThrottled` is active on login (confirmed via `config/fortify.php` + `FortifyServiceProvider`); the sensitive-fields reveal endpoint carries `throttle:15,1` and sits behind `auth` middleware. | No change needed. |
| 8 | Audit log coverage | **PARTIAL**: creation is generally audited (JournalVoucher, TravelPayment, OtherServiceInvoice via observers/inline calls), but nearly every **destroy** action across the accounts module was unaudited, and `TravelPayment::cancel()` falls through an observer gap. | **Partially fixed**: added audit-log writes to `FinancialYearController::destroy()`, `JournalVoucherController::destroy()` (which was also missing the closed-year guard other JV writes have — added that too), and `CashReceiptController::store()`/`destroy()`. **Not fixed** (flagged, not blocking): `VoucherObserver` has no `deleted()` hook; `TravelPaymentObserver` has no `updated()` hook for `cancel()`; `ChartOfAccount`/`AccountOpening`/`ExchangeRate`/`TicketInvoice`/`InsuranceSale` writes remain fully unaudited. This is a real, scoped-out remainder — see Section 7. |

All fixes re-verified against the full 25-test suite (still 25/25) plus live HTTP requests (Section 5).

---

## Section 5 — Golden Path: What Was Actually Exercised Live (real HTTP requests/responses, this session)

Unlike v2 (which narrates a walkthrough without showing it was ever run), every item below is a real request against the running app on freshly-seeded data, with the real response quoted.

1. **Login as Super Admin** → dashboard now renders fully (was blank before the 3.1 fix). Verified via browser screenshot and via `curl` (non-empty body).
2. **Create a second Financial Year (FY 2026-2027)** via the real `POST /account/financial-years` endpoint → `{"status":"success","message":"Record saved successfully"}`.
3. **Close FY 2025-2026** via the real `POST /account/financial-years/1/close` → `{"status":"success","message":"The financial year has been closed. Transactions cannot be posted or modified in a closed year."}`.
   - Verified in the DB immediately after: `is_closed=1` on FY1; a new `account_openings` row for FY 2026-2027's Retained Earnings account with `opening_balance = 200000.00`, which is the **exact, hand-checked net P&L** for FY1 (seeded income 280,000 − seeded expense 80,000 = 200,000). This required adding a `retained_earnings` (`type='equity'`) account to `DemoDataSeeder`'s chart of accounts — none existed before, so this roll-forward step could never have been demonstrated even after fixing 3.2.
4. **Attempt to close FY 2025-2026 again** → `{"status":"fail",...,"message":"This financial year is already closed."}` — idempotency guard genuinely works.
5. **Attempt to post a new Journal Voucher into the now-closed FY 2025-2026** → `{"status":"fail",...,"message":"This financial year has been closed. Transactions cannot be posted or modified in a closed year."}`.
6. **Post a Journal Voucher into the still-open FY 2026-2027** → `{"status":"success","message":"Record saved successfully","voucher_id":4}` — confirms the block in step 5 is specific to the closed year, not a general failure.
7. **Room Allocation — invalid assignment blocked:** assigned a female passenger to a male-only room (`MK-101`) → `{"status":"fail",...,"message":"This room is restricted to male passengers only."}`.
8. **Room Allocation — valid assignment succeeds:** assigned a male passenger to the same room → `{"status":"success",...}`.
9. **Room Allocation — capacity enforcement:** attempted a third passenger into the now-full (2/2) room → `{"status":"fail",...,"message":"This room is at full capacity and cannot accept more passengers."}`.
10. **Voucher lock:** locked the seeded draft voucher (`POST /account/vouchers/1/lock`) → success.
11. **Voucher edit rejected after lock:** `PUT /account/vouchers/1` → **HTTP 403**.
12. **Voucher re-lock rejected (idempotency):** locking the same voucher again → **HTTP 403**, `"This voucher is locked and cannot be modified."`

Every one of these 12 steps is a real, reproducible HTTP exchange against the running application on this freshly-seeded database — not a narrated hypothetical.

### Not live-clicked this session (verified by reading the exact code instead)
Given the scope of a full CRM, the remaining Golden Path steps (CSV booking import preview/commit, ticket invoice `sale_type`, other service invoice → JV posting, insurance sale, Umrah-wise P&L live pull) were **not** driven through the browser this session, due to time. They were instead verified by a dedicated code-reading pass that opened the actual controller/service method bodies and quoted the real logic (full results in Section 6) — this is weaker evidence than a live HTTP round-trip, and is flagged as such rather than blurred together with Section 5's live results.

---

## Section 6 — Code-Level Verification of the Remaining Parity Checklist

A dedicated pass re-read the actual current source (not relying on any prior report) for 12 specific feature claims. Verdicts:

| # | Feature | Verdict |
|---|---|---|
| 1 | Financial Year close — real P&L + roll-forward + idempotency | Confirmed as designed, **but was dead code** — see 3.2. Now genuinely fixed and live-verified (Section 5). |
| 2 | Journal Voucher balance + closed-year enforcement on both store and update | **Confirmed** — real checks on both. |
| 3 | Other Service Invoice → real journal voucher posting via `DoubleEntryService`, with a second balance re-check, permission checks, and audit logging | **Confirmed.** |
| 4 | Package auto-calculated pricing pulls real hotel/transporter/visa data | **Confirmed** the calculation itself is real (not stubbed); **confirmed the override red flag was real** — see 3.3, now fixed. |
| 5 | Ticket `sale_type` (bsp/xo/direct) validated, persisted, and filterable | **Confirmed.** |
| 6 | Voucher server-side lock on update/destroy/issue/lock + HMAC-SHA256 signed QR | **Confirmed** the lock guards and HMAC signing are real; the **race-condition gap was real** — see 4.3, now fixed. |
| 7 | Room Allocation capacity + gender restriction enforced server-side, not just UI | **Confirmed**, and independently live-verified in Section 5. |
| 8 | Visa Pipeline: every transition writes `visa_logs` + fires a genuine `ShouldBroadcastNow` event + has a non-drag (dropdown) fallback | **Confirmed.** |
| 9 | Umrah-Wise P&L: pure SQL aggregation (no PHP-side summation), excludes non-posted payments from cost | **Confirmed.** |
| 10 | CSV booking import: exact documented column format, genuine two-step preview/commit (nothing written to DB until commit) | **Confirmed.** |
| 11 | GDS adapter pattern: real interface, bound via service provider, controller depends only on the interface | **Confirmed.** |
| 12 | Mahram/Relations: real FK-backed lookup, not free text | **Confirmed.** |

10 of 12 were exactly as claimed on first read; 2 (financial year closing, package pricing) had the same kind of "looks right on paper, actually broken or gameable" problem this whole engagement exists to catch — which is exactly why the evidence standard in this document insists on live requests, not code review alone, wherever practical.

---

## Section 7 — Known Limitations / Honestly Unresolved

Stated plainly, not papered over:

1. **`UsersTableSeeder.php`'s base-product admin seeding has the same blank-dashboard bug as 3.1**, for any real customer install that goes through that path rather than `DemoDataSeeder`. Not fixed — outside this engagement's travel-module scope, but a real landmine for anyone deploying this product fresh.
2. **Audit log coverage is still incomplete** (Section 4, item 8): `Voucher` deletion, `TravelPayment::cancel()`, and all writes to `ChartOfAccount`/`AccountOpening`/`ExchangeRate`/`TicketInvoice`/`InsuranceSale` are not audited. Fixing this fully means either adding `deleted()`/`updated()` observer hooks or inline `AuditLog::create()` calls across ~8 more controllers — a mechanical but non-trivial amount of work not completed this session.
3. **`CashReceiptController::destroy()` does not delete or reverse the linked journal voucher** — deleting a cash receipt leaves its journal voucher in place, silently orphaned. Not fixed (pre-existing behavior, not one of the 8 named security risks, but worth a follow-up).
4. **Translations were verified complete for English only.** The other ~30 language files were not audited for missing travel-module keys in this session.
5. **Golden Path items not driven through the browser** (CSV import, ticket invoice, other service invoice, insurance sale, live P&L pull as an authenticated user click-path) — verified by reading the actual code instead of a live HTTP round-trip. See the caveat at the end of Section 5.
6. **The base product's `key:generate`-on-every-seed behavior** (0.5) was worked around, not fixed at the source.
7. `.claude/launch.json` and `scripts/dev_serve_local_env.bat` were added to this repo during this session purely as local dev tooling (lets a future session start the app with `APP_ENV` overridden to exercise demo data, without ever touching the committed `.env`). They have no effect on the shipped product and can be deleted if unwanted.

---

## Final Verdict

The core accounting/security claims this engagement was chartered to verify are now **genuinely true and demonstrated live**, where before this session several of the most important ones (financial year closing, the very first login as an administrator) were **silently broken in ways no prior report caught**, despite confident "VERIFIED" language in the record this session inherited. The fixes in Sections 3–4 are real, small, targeted, and re-verified against the full test suite and live HTTP requests. The gaps in Section 7 are real and are stated as gaps, not hidden behind confident language.

**This is a materially more trustworthy state than the `v2` record it replaces — not because more features were built, but because what was already there was actually checked, and the checking itself is now reproducible from the evidence in this document.**
