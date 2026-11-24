# Graph Report - SAMSARA  (2026-11-25)

## Corpus Check
- 230 files · ~157,712 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 28 file(s) not represented in the graph (top: (none) 16, .example 2, .kmz 2)

## Summary
- 2588 nodes · 4848 edges · 232 communities (50 shown, 182 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 124 edges (avg confidence: 0.85)
- Token cost: 12,500 input · 3,400 output

## Graph Freshness
- Built from commit: `3b81a7cb`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- GateCheckInController
- Medical Conditions Schema & Migration
- Illuminate\Http\Request
- Frontend Tooling & Dependencies
- web.php
- SimaksiRegistration
- Ojek Dispatch & Logistics
- .get
- Illuminate\Support\Facades\Schema
- TestCase
- .log
- Illuminate\Database\Eloquent\Relations\BelongsTo
- OjekTrip
- f
- ce
- ee
- music.js
- rt
- .getExpeditionProfile
- Controller
- a
- User
- POS Cashier & Expense Tracking
- POS Cashier & Expense Tracking
- DigitalTicketAndPdfDownloadTest
- Financial Reports & Analytics
- N
- SimaksiController
- be
- l
- .getTariffs
- POS Cashier & Expense Tracking
- InspectionTemplateController.php
- Audit Logs & Governance
- Automated Test Suite (34)
- DailyQuota
- SIMAKSI Registration & Verification
- POS Cashier & Expense Tracking
- WhatsAppResult
- je
- ze
- AppServiceProvider.php
- POS Cashier & Expense Tracking
- Database Schema & Table Migrations (43)
- Database Schema & Table Migrations (44)
- POS Cashier & Expense Tracking
- Database Schema & Table Migrations (46)
- Ranger Checkpoints & Mountain Radar
- le
- RoleMiddleware Module
- ke
- POS Cashier & Expense Tracking
- Database Schema & Table Migrations (53)
- down() Module
- Database Schema & Table Migrations (55)
- SIMAKSI Registration & Verification
- SIMAKSI Registration & Verification
- SIMAKSI Registration & Verification
- SIMAKSI Registration & Verification
- SIMAKSI Registration & Verification
- SIMAKSI Registration & Verification
- SIMAKSI Registration & Verification
- SIMAKSI Registration & Verification
- public/assets/js/data.js Module
- SIMAKSI Registration & Verification
- AppSetting
- BookingGuardService
- POS Cashier & Expense Tracking
- POS Cashier & Expense Tracking
- deploy.sh Module
- public/assets/js/app.js Module
- Audit Logs & Governance
- escpos-bt.js
- management.layout Module
- Equipment Inventory & Rental
- management.layout Module
- management.layout Module
- management.layout Module
- POS Cashier & Expense Tracking
- management.layout Module
- POS Cashier & Expense Tracking
- management.layout Module
- management.layout Module
- Financial Reports & Analytics
- System Settings & Tariffs
- SIMAKSI Registration & Verification
- management.layout Module
- Role-Based Access Control (RBAC) Module
- login.blade.php
- print_climber_pass.blade.php
- print_ojek_voucher.blade.php
- print_parking_pass.blade.php
- layout.blade.php
- print_settlement.blade.php
- print_trip_voucher.blade.php
- print_expense_voucher.blade.php
- receipt.blade.php
- portal/index.blade.php
- ProductionPreflightCommand.php
- WhatsappMessage
- Q
- p
- sr
- wt
- cr
- pr
- ThermalReceiptService
- ft
- et
- ar
- ht
- pe
- _
- zt
- x
- manifest.json
- ResetSimulationDataCommand.php
- fe
- te
- xe
- s
- gr
- j
- ve
- SamsaraPWA
- QrCode
- Vr
- lt
- w
- R
- .encodeLayers
- it
- ot
- se
- at
- m
- me
- wr
- FourLevelAccessAndGateFlowTest
- ae
- b
- dt
- T
- BookingAntiFraudTest
- PosController
- y
- ue
- .encode
- mt
- tt
- Nr
- GatekeeperScannerAuditTest
- Navigation.php
- GoLiveHardeningTest.php
- ct
- fr
- he
- TicketLookupOwnershipTest
- Illuminate\Database\Migrations\Migration
- Audit & Uji Kelayakan Go-Live — SAMSARA
- O
- OjekManagementAndVoucherTest
- CashierDeskController
- TektokCampAndCheckpointTrackingTest
- kt
- UserManagementController
- pt
- xt
- ThermalBluetoothPrintTest.php
- 2026_11_22_024958_add_medical_conditions_to_simaksi_tables.php
- 2026_11_24_100000_revamp_four_level_roles_and_gate_inspection.php
- currency-input.js
- ut
- sw.js
- BasecampLocationAndQrScanValidationTest
- 2026_11_24_120000_add_payment_due_at_to_simaksi_registrations_table.php
- .setHints
- Icons
- home.blade.php
- waypoints.blade.php
- kasir.blade.php
- superadmin.blade.php
- desk/index.blade.php
- desk/show.blade.php
- ascent.blade.php
- descent.blade.php
- qr_scanner.blade.php
- thermal_bt.blade.php
- inspection.blade.php
- whatsapp.blade.php
- users/index.blade.php
- booking.blade.php
- services.blade.php
- telemetry.blade.php
- ne
- bt
- DashboardController.php
- UserFactory.php
- CashDrawerAndOperationalExpenseTest
- SimaksiCheckpoint

## God Nodes (most connected - your core abstractions)
1. `_` - 120 edges
2. `SimaksiRegistration` - 104 edges
3. `User` - 65 edges
4. `f` - 64 edges
5. `c` - 52 edges
6. `E` - 48 edges
7. `AppSetting` - 45 edges
8. `TestCase` - 44 edges
9. `p` - 41 edges
10. `sr` - 37 edges

## Surprising Connections (you probably didn't know these)
- `Tinggi / Sedang (sudah diperbaiki)` --references--> `SanitizeCurrencyInputs`  [INFERRED]
  docs/AUDIT-KELAYAKAN-GO-LIVE.md → app/Http/Middleware/SanitizeCurrencyInputs.php
- `Kritis (sudah diperbaiki)` --references--> `SuperAdminSeeder`  [INFERRED]
  docs/AUDIT-KELAYAKAN-GO-LIVE.md → database/seeders/SuperAdminSeeder.php
- `Kritis (sudah diperbaiki)` --references--> `DatabaseSeeder`  [INFERRED]
  docs/AUDIT-KELAYAKAN-GO-LIVE.md → database/seeders/DatabaseSeeder.php
- `Kritis (sudah diperbaiki)` --references--> `SamsaraManagementSeeder`  [INFERRED]
  docs/AUDIT-KELAYAKAN-GO-LIVE.md → database/seeders/SamsaraManagementSeeder.php
- `DigitalTicketAndPdfDownloadTest` --references--> `SimaksiRegistration`  [EXTRACTED]
  tests/Feature/DigitalTicketAndPdfDownloadTest.php → app/Models/SimaksiRegistration.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Expedition Production Stack & Architecture** — readme_samsara_expedition, readme_redis_architecture, readme_production_deployment [INFERRED 0.85]
- **SAMSARA End-to-End Mountaineering Permit Flow** — prd_portal_pendakian_samsara_simaksi_service, prd_portal_pendakian_samsara_pos_cashier, prd_portal_pendakian_samsara_gate_checkin, prd_portal_pendakian_samsara_trash_deposit [INFERRED 0.95]

## Communities (232 total, 182 thin omitted)

### Community 1 - "Medical Conditions Schema & Migration"
Cohesion: 0.18
Nodes (12): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#1}(), {closure#2}() (+4 more)

### Community 2 - "Illuminate\Http\Request"
Cohesion: 0.15
Nodes (3): AuthController, OperationalExpenseController, PublicSimaksiController

### Community 3 - "Frontend Tooling & Dependencies"
Cohesion: 0.12
Nodes (19): devDependencies, axios@^1.11.0, concurrently@^9.0.1, laravel-vite-plugin@^2.0.0, tailwindcss@^4.0.0, @tailwindcss/vite@^4.0.0, vite@^7.0.7, private (+11 more)

### Community 5 - "SimaksiRegistration"
Cohesion: 0.06
Nodes (6): PurgeExpiredGroupPhotosCommand, SimaksiRegistration, CheckInService, GateFlowService, GroupPhotoService, SimaksiService

### Community 9 - "TestCase"
Cohesion: 0.08
Nodes (9): ClimberMember, DailyQuota, InventoryItem, ExampleTest, ManagementRouteSmokeTest, PwaManagementTest, ResetSimulationDataCommandTest, SimaksiMedicalAndKtpPurgeTest (+1 more)

### Community 10 - ".log"
Cohesion: 0.06
Nodes (19): {closure#1}(), {closure#1}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#1}(), {closure#3}(), {closure#1}() (+11 more)

### Community 11 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.07
Nodes (7): AuditLog, OjekSettlement, OperationalExpense, PosOrder, PosOrderItem, RentalRecord, TrailCondition

### Community 12 - "OjekTrip"
Cohesion: 0.15
Nodes (3): OjekTrip, OjekService, PosService

### Community 16 - "music.js"
Cohesion: 0.06
Nodes (30): bass, buf, chords, fs, L, N, nBars, Rch (+22 more)

### Community 19 - "Controller"
Cohesion: 0.10
Nodes (4): Controller, TariffSettingController, SimaksiInspectionItem, AuditService

### Community 21 - "User"
Cohesion: 0.15
Nodes (4): {closure#3}(), {closure#2}(), {closure#3}(), User

### Community 22 - "POS Cashier & Expense Tracking"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 23 - "POS Cashier & Expense Tracking"
Cohesion: 0.22
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 31 - "POS Cashier & Expense Tracking"
Cohesion: 0.25
Nodes (8): require-dev, fakerphp/faker, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, phpunit/phpunit

### Community 33 - "Audit Logs & Governance"
Cohesion: 0.29
Nodes (7): Immutable Audit Trail System, Daily Quota & Carrying Capacity Control, Persewaan Tenda & Peralatan Camping, Gate Check-in & Manifest Verification, Point of Sale (POS) Cashier System, Logistik Shuttle Ojek Trail, SIMAKSI Self-Service & Permit System

### Community 37 - "POS Cashier & Expense Tracking"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 38 - "WhatsAppResult"
Cohesion: 0.09
Nodes (8): SendWhatsAppMessage, FonnteDriver, LogDriver, WablasDriver, WebhookDriver, WhatsAppGateway, WhatsAppManager, WhatsAppResult

### Community 39 - "je"
Cohesion: 0.06
Nodes (3): er, ge, je

### Community 40 - "ze"
Cohesion: 0.06
Nodes (3): we, ye, ze

### Community 41 - "AppServiceProvider.php"
Cohesion: 0.09
Nodes (7): AppServiceProvider, {closure#1}(), format_nominal(), parse_rupiah(), rupiah(), Money, IndonesianCurrencyFormattingTest

### Community 42 - "POS Cashier & Expense Tracking"
Cohesion: 0.33
Nodes (6): require, barryvdh/laravel-dompdf, laravel/framework, laravel/tinker, php, predis/predis

### Community 43 - "Database Schema & Table Migrations (43)"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 44 - "Database Schema & Table Migrations (44)"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 45 - "POS Cashier & Expense Tracking"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 46 - "Database Schema & Table Migrations (46)"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 47 - "Ranger Checkpoints & Mountain Radar"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 49 - "RoleMiddleware Module"
Cohesion: 0.18
Nodes (4): RoleMiddleware, SanitizeCurrencyInputs, {closure#1}(), {closure#3}()

### Community 51 - "POS Cashier & Expense Tracking"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 65 - "SIMAKSI Registration & Verification"
Cohesion: 0.40
Nodes (5): Robots TXT Crawler Configuration, Production Deployment Templates & Supervisor, Redis Database Index Isolation (DB 0-3), SAMSARA EXPEDITION Operational System, SIMAKSI Simplified Lifecycle (Pending, Paid, Checked-In, Selesai)

### Community 69 - "POS Cashier & Expense Tracking"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 70 - "POS Cashier & Expense Tracking"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 74 - "escpos-bt.js"
Cohesion: 0.13
Nodes (16): bleConnect(), bleSend(), currentDevice(), encode(), EscPos, findWritable(), loadSettings(), logoToMono() (+8 more)

### Community 106 - "portal/index.blade.php"
Cohesion: 0.22
Nodes (8): portal.partials.booking, portal.partials.bottom-nav, portal.partials.header, portal.partials.home, portal.partials.services, portal.partials.telemetry, portal.partials.waypoints, management.partials.head

### Community 109 - "ProductionPreflightCommand.php"
Cohesion: 0.09
Nodes (4): ExpireUnpaidSimaksiCommand, ProductionPreflightCommand, PurgeCompletedKtpCommand, ResetSimulationDataCommand

### Community 110 - "WhatsappMessage"
Cohesion: 0.09
Nodes (5): WhatsAppController, WhatsappMessage, NotificationService, WhatsAppNotifier, InspectionTemplate

### Community 111 - "Q"
Cohesion: 0.10
Nodes (3): c, Q, Z

### Community 123 - "_"
Cohesion: 0.15
Nodes (3): _, I, tr

### Community 126 - "manifest.json"
Cohesion: 0.11
Nodes (18): background_color, categories, description, dir, display, display_override, icons, id (+10 more)

### Community 131 - "s"
Cohesion: 0.12
Nodes (7): d, e(), n(), oe, r(), s, t()

### Community 136 - "QrCode"
Cohesion: 0.12
Nodes (4): Barcode, QrCode, ExampleTest, QrCodeMatrixTest

### Community 137 - "Vr"
Cohesion: 0.07
Nodes (6): br(), ir(), or, rr(), Rr, Vr

### Community 138 - "lt"
Cohesion: 0.12
Nodes (3): jt, Jt(), lt

### Community 141 - ".encodeLayers"
Cohesion: 0.25
Nodes (3): encode(), Ir, Xr

### Community 152 - "dt"
Cohesion: 0.11
Nodes (3): de, dt, re

### Community 171 - "Audit & Uji Kelayakan Go-Live — SAMSARA"
Cohesion: 0.13
Nodes (11): DatabaseSeeder, SamsaraManagementSeeder, A. Skor Fitur (0–10), Audit & Uji Kelayakan Go-Live — SAMSARA, B. Skor Kelayakan Deploy (0–100), C. Temuan & Status Perbaikan, D. Prosedur Uji Kelayakan di Server, Gap tersisa (belum diperbaiki, perlu keputusan) (+3 more)

### Community 181 - "2026_11_22_024958_add_medical_conditions_to_simaksi_tables.php"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 182 - "2026_11_24_100000_revamp_four_level_roles_and_gate_inspection.php"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 183 - "currency-input.js"
Cohesion: 0.48
Nodes (4): bindCurrencyInput(), formatInputElement(), initCurrencyInputs(), setCurrencyInputValue()

### Community 189 - ".setHints"
Cohesion: 0.29
Nodes (3): constructor(), gt, lr

### Community 234 - "SimaksiCheckpoint"
Cohesion: 0.18
Nodes (3): RangerCheckpointController, SimaksiCheckpoint, CheckpointTrackingService

## Knowledge Gaps
- **60 isolated node(s):** `management.layout`, `management.partials.head`, `management.layout`, `management.layout`, `management.layout` (+55 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1048 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **182 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `_` connect `_` to `fe`, `te`, `xe`, `s`, `gr`, `j`, `ve`, `Vr`, `lt`, `w`, `R`, `.encodeLayers`, `ce`, `ee`, `f`, `at`, `it`, `m`, `a`, `me`, `ae`, `b`, `dt`, `rt`, `N`, `T`, `be`, `l`, `ue`, `.encode`, `mt`, `tt`, `Nr`, `y`, `ct`, `je`, `fr`, `he`, `ze`, `O`, `kt`, `le`, `ke`, `pt`, `xt`, `ut`, `.setHints`, `ot`, `se`, `ne`, `bt`, `wr`, `Q`, `p`, `sr`, `wt`, `cr`, `pr`, `ft`, `et`, `ar`, `ht`, `pe`, `zt`, `x`?**
  _High betweenness centrality (0.188) - this node is a cross-community bridge._
- **What connects `management.layout`, `management.partials.head`, `management.layout` to the rest of the system?**
  _60 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Frontend Tooling & Dependencies` be split into smaller, more focused modules?**
  _Cohesion score 0.11857707509881422 - nodes in this community are weakly interconnected._
- **Why does `SimaksiRegistration` connect `SimaksiRegistration` to `GateCheckInController`, `Illuminate\Http\Request`, `TestCase`, `.log`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `OjekTrip`, `Controller`, `FourLevelAccessAndGateFlowTest`, `DigitalTicketAndPdfDownloadTest`, `SimaksiController`, `.getTariffs`, `GatekeeperScannerAuditTest`, `Navigation.php`, `GoLiveHardeningTest.php`, `TicketLookupOwnershipTest`, `ThermalBluetoothPrintTest.php`, `AppSetting`, `SimaksiCheckpoint`, `WhatsappMessage`, `ThermalReceiptService`, `ResetSimulationDataCommand.php`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **Should `web.php` be split into smaller, more focused modules?**
  _Cohesion score 0.09090909090909091 - nodes in this community are weakly interconnected._
- **Why does `f` connect `f` to `te`, `s`, `je`, `w`, `a`, `_`?**
  _High betweenness centrality (0.030) - this node is a cross-community bridge._
- **Should `SimaksiRegistration` be split into smaller, more focused modules?**
  _Cohesion score 0.0602322206095791 - nodes in this community are weakly interconnected._