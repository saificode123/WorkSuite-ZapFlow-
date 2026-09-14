# ZapFlow CRM — Demo Test Accounts

> **IMPORTANT**: These accounts are for testing and development only.
> ALL accounts use password: **`Password123!`**
> Never use this password on any real system.

## Setup — read this before seeding (corrected 2026-09-14, verified live)

The committed `.env` ships with `APP_ENV=codecanyon`. `database/seeders/DatabaseSeeder.php` skips `UsersTableSeeder`, `RoleSeeder`, and `DemoDataSeeder` entirely whenever `APP_ENV` is `codecanyon` or `production`.

**Do not run `php artisan db:seed --class=Database\Seeders\DemoDataSeeder` by itself on a fresh database** — this used to be documented here and is **broken**: it was verified this session to leave the login page throwing a 500 error (`Attempt to read property "google_status" on null` in `auth/login.blade.php`), because it skips `OrganisationSettingsTableSeeder`/`CoreDatabaseSeeder`/`ModulePermissionSeeder`, which the app depends on just to render the login page and to populate each user's enabled-modules list. Even when it doesn't 500, the resulting users have an **empty `modules` array**, so every travel/accounts controller (`abort_403(!in_array('accounts', $this->user->modules))`) returns 403 for everyone, including Super Admin — `abort_403` is a plain helper, not a Gate check, so the `Gate::before` admin bypass does not save you here.

**The tested, working procedure** (does not modify the committed `.env`):

```bash
php artisan migrate:fresh
APP_ENV=local php artisan db:seed
```

(On Windows PowerShell: `$env:APP_ENV = "local"; php artisan db:seed`.) This runs the full seeder chain — company, roles, module permissions, org settings, then `DemoDataSeeder` — and produces a fully working instance: login page renders, all ten accounts below authenticate, and every module below is actually reachable (verified live this session: financial-years, room-allocation, journal-vouchers, vouchers all returned HTTP 200 for `superadmin@zapflow.test` after this exact procedure).

Note: seeding also unconditionally calls `Artisan::call('key:generate')`, which rotates `.env`'s `APP_KEY` as a side effect — if you need to keep the original key, `git checkout -- .env` afterwards (re-run `php artisan config:clear` if config is cached).

---

## Internal Staff Accounts (Employee Users)

| Role | Name | Email | Password | Access Scope |
|---|---|---|---|---|
| Super Admin | Zafar Khan | `superadmin@zapflow.test` | `Password123!` | Full system access (Gate::before bypass) |
| Admin | Fatima Malik | `admin@zapflow.test` | `Password123!` | Full admin access |
| Accountant | Bilal Ahmed | `accountant@zapflow.test` | `Password123!` | Accounts module only (COA, JV, Payments, Reports) |
| Visa Officer | Ayesha Siddiqui | `visaofficer@zapflow.test` | `Password123!` | Booking view + Visa Pipeline |
| Ops Staff | Usman Tariq | `opsstaff@zapflow.test` | `Password123!` | Booking view/edit, Room Allocation, Passport Delivery |
| Ticketing Staff | Nadia Rauf | `ticketing@zapflow.test` | `Password123!` | Ticketing module only |

---

## External / Client Accounts

| Role | Name | Email | Password | Notes |
|---|---|---|---|---|
| B2B Agent 1 | Al-Noor Travel Agency | `agent1@zapflow.test` | `Password123!` | Has Umrah + Ticket sub-ledgers. Owns BG-2026-001 |
| B2B Agent 2 | Barakah Hajj Group | `agent2@zapflow.test` | `Password123!` | Isolation test � owns BG-2026-002 |
| Sub-Agent | Rahmat Sub-Agency | `subagent1@zapflow.test` | `Password123!` | Parented to Agent-1; limited scope |
| B2C Customer | Hassan Iqbal | `customer@zapflow.test` | `Password123!` | Individual pilgrim � own booking visibility only |

---

## Seeded Demo Data Reference

### Booking Groups
| Group No. | Name | Owner | Passengers | Departure |
|---|---|---|---|---|
| BG-2026-001 | Demo Umrah Group Alpha | Agent-1 | 4 (all visa stages) | +2 months |
| BG-2026-002 | Demo Umrah Group Beta | Agent-2 | 2 (all draft) | +3 months |

### Passengers (BG-2026-001 � Golden Path group)
| Name | Passport | Visa Status | Notes |
|---|---|---|---|
| Ahmad Raza | AA1234567 | `draft` | Starting point � move through pipeline |
| Kiran Bano | BB9876543 | `sent_to_embassy` | Already submitted |
| Tariq Mehmood | CC5544332 | `mofa_received` | MoFA ref: MOFA-2026-TM-004481 |
| Sana Fatima | DD1122334 | `issued` | Fully processed |

### Vouchers
| Voucher No. | Status | Notes |
|---|---|---|
| VCH-2026-0001 | `draft` | Lock this during Step 6 of Golden Path test |

### Journal Vouchers (all balanced PKR)
| JV No. | Dr | Cr | Amount | Purpose |
|---|---|---|---|---|
| JV-DEMO-001 | Bank | Agent1 Umrah AR | 150,000 | Partial payment received |
| JV-DEMO-002 | Hotel Expense | Bank | 80,000 | Hotel advance to Zam Zam Tower |
| JV-DEMO-003 | Agent1 Umrah AR | Umrah Revenue | 280,000 | Revenue recognition |

**Expected P&L for BG-2026-001:**
- Revenue: PKR 280,000
- Hotel Cost: PKR 80,000
- Gross Profit: PKR 200,000

---

## Golden Path � Manual Test Sequence

1. Login as `superadmin@zapflow.test`
2. Navigate to Bookings ? Demo Umrah Group Alpha � confirm 4 passengers visible
3. Login as `visaofficer@zapflow.test` ? Visa Pipeline ? Move Ahmad Raza to `sent_to_embassy`
4. Login as `opsstaff@zapflow.test` ? Room Allocation ? Assign a passenger to room MK-101
5. Login as `superadmin@zapflow.test` ? Vouchers ? Open VCH-2026-0001 ? Click **Lock**
6. Attempt to edit VCH-2026-0001 ? Confirm 403 error appears
7. Login as `accountant@zapflow.test` ? Journal Vouchers ? View JV-DEMO-003 ? Confirm Dr=Cr=280,000
8. Navigate Travel Reports ? Umrah Wise P&L ? Confirm BG-2026-001 shows Revenue 280,000, Cost 80,000
9. Login as `agent2@zapflow.test` ? Bookings ? Confirm ONLY BG-2026-002 is visible (not Alpha)
10. Login as `customer@zapflow.test` ? Confirm no access to any staff-level modules
