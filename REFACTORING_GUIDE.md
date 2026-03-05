# Aggregate Columns Refactoring Guide

**Date:** March 5, 2026  
**Status:** COMPLETE REFACTORING  
**Goal:** Remove persisted aggregate/derived fields and replace with computed queries

---

## 📊 BEFORE → AFTER SUMMARY

### Tables Removed

| Table | Type | Status | Reason |
|-------|------|--------|--------|
| `vendor_summary` | Summary Table | ✅ DROPPED | All metrics aggregateable from orders + walk_in_sales + market_fees |
| `market_summary` | Summary Table | ✅ DROPPED | All metrics aggregateable from orders + walk_in_sales |
| `vendor_transactions` | Audit Trail | ✅ DROPPED | Unused intermediate table; data reconstructible from source tables |

### Removed Columns

**vendor_summary:**
- ❌ `total_pre_orders` → `COUNT(order_items WHERE status IN ['confirmed', 'completed', ...])`
- ❌ `total_walk_in_sales` → `COUNT(walk_in_sales)`
- ❌ `gross_sales` → `SUM(qty * price)`
- ❌ `total_fees` → **NOT NEEDED** (vendors pay NO transaction fees)
- ❌ `net_sales` → **SAME AS gross_sales** (vendors keep 100% of sales revenue)

**market_summary:**
- ❌ `total_vendors_present` → `COUNT(DISTINCT vendor_id FROM stall_assignments)`
- ❌ `total_orders` → `COUNT(DISTINCT order_id FROM orders)`
- ❌ `total_walk_in_sales` → `COUNT(walk_in_sales)`
- ❌ `gross_market_revenue` → `SUM(all_sales.qty * price)`
- ❌ `total_market_fees` → **NOT APPLICABLE** (no transaction fees; stall rent is separate)
- ❌ `net_vendor_payout` → **SAME AS gross_market_revenue**

**vendor_transactions:**
- ❌ `vendor_id`, `transaction_type`, `order_id`, `sale_id` (all source data)
- ❌ `gross_amount`, `fee_amount` (source data)
- ❌ `net_amount` → DERIVED: `gross_amount - fee_amount`

---

## 🗂️ FILES CREATED

### 1. Drop Migrations (Safe to Deploy)

```
database/migrations/2026_03_05_000001_drop_vendor_summary_table.php
database/migrations/2026_03_05_000002_drop_market_summary_table.php
database/migrations/2026_03_05_000003_drop_vendor_transactions_table.php
```

These migrations safely drop the tables. The `down()` methods restore them if needed for rollback.

### 2. Service Classes (Replacement Logic)

```
app/Services/VendorSummaryService.php
app/Services/MarketSummaryService.php
```

**Static methods for computing metrics on-demand:**
- `getSummaryForDate(vendorId, date)` - Get all metrics for a single day
- `getSummaryForDateRange(vendorId, startDate, endDate)` - Get metrics for a range
- Individual getters: `calculateGrossSales()`, `calculateTotalFees()`, `countPreOrders()`, etc.

### 3. Trait for Model Integration (Optional)

```
app/Traits/VendorMetricsTrait.php
```

Add to `Vendor` model to access metrics as methods:

```php
class Vendor extends Model {
    use VendorMetricsTrait;
    // ...
}

// Then use:
$vendor->getSummaryForDate('2026-03-05');
$vendor->getGrossSalesForDate();
$vendor->getNetSalesForDate('2026-03-01');
```

### 4. Optional: Database Views

```
database/migrations/2026_03_05_000004_create_summary_views.php
```

Creates two read-only SQL views if you prefer direct SQL queries:
- `vendor_summary_view` - Like the old `vendor_summary` table
- `market_summary_view` - Like the old `market_summary` table

**⚠️ NOTE:** The SQL for views is simplified for readability. For production, optimize the JOIN logic.

---

## 💡 USAGE EXAMPLES

### Example 1: Get Vendor Metrics for Today

**OLD (Deprecated):**
```php
$summary = VendorSummary::where('vendor_id', $vendor->id)
    ->whereDate('summary_date', today())
    ->first();

$gross = $summary->gross_sales;
$fees = $summary->total_fees;
$net = $summary->net_sales;
```

**NEW (Using Service):**
```php
use App\Services\VendorSummaryService;

$summary = VendorSummaryService::getSummaryForDate($vendor->id, today());

$gross = $summary['gross_sales'];
$net = $summary['net_sales']; // Same as gross - no transaction fees
```

**NEW (Using Trait):**
```php
$summary = $vendor->getSummaryForDate(today());

$gross = $summary['gross_sales'];
$net = $summary['net_sales']; // Same as gross - no transaction fees
```

### Example 2: Get Market Summary for a Date Range

**OLD (Deprecated):**
```php
$summaries = MarketSummary::whereBetween('summary_date', [$start, $end])->get();
$total = $summaries->sum('gross_market_revenue');
```

**NEW (Using Service):**
```php
use App\Services\MarketSummaryService;

$summary = MarketSummaryService::getSummaryForDateRange('2026-03-01', '2026-03-31');

$total = $summary['gross_market_revenue'];
$payout = $summary['net_vendor_payout']; // Same as gross - no transaction fees
```

### Example 3: Building a Vendor Revenue Report

```php
use App\Services\VendorSummaryService;

$vendor = Vendor::find(1);
$startDate = '2026-03-01';
$endDate = '2026-03-31';

$metrics = VendorSummaryService::getSummaryForDateRange($vendor->id, $startDate, $endDate);

return view('vendor.revenue_report', [
    'vendor' => $vendor,
    'totalOrders' => $metrics['total_pre_orders'],
    'totalWalkIn' => $metrics['total_walk_in_sales'],
    'grossRevenue' => $metrics['gross_sales'],
    'netRevenue' => $metrics['net_sales'], // Same as gross - vendors keep 100%
]);
```

---

## ⚡ BUSINESS LOGIC CLARIFICATION

### Revenue Model

**Vendors keep 100% of their sales revenue.** There are NO transaction fees or percentage cuts on sales.

**Market revenue comes from stall rental fees only:**
- Managed via the `stall_payments` table
- Vendors pay monthly/periodic rent for their stall space
- This is completely separate from their sales transactions

**Therefore:**
- `gross_sales` = `net_sales` (no deductions)
- The `market_fees` table is NOT used for sales transactions
- `total_fees` and `total_market_fees` columns are removed (not applicable)

---

## ⚡ PERFORMANCE CONSIDERATIONS & INDEXING

### DO NOT Rely on Summary for Real-Time High-Traffic Queries

If you need millisecond-level performance for frequently accessed summaries, consider:

1. **Add these indexes** (for query optimization):

```php
// In a new migration
Schema::table('order_items', function (Blueprint $table) {
    $table->index(['vendor_id', 'item_status']);
    $table->index(['order_id', 'vendor_id']);
});

Schema::table('orders', function (Blueprint $table) {
    $table->index(['order_date', 'order_status']);
});

Schema::table('walk_in_sales', function (Blueprint $table) {
    $table->index(['vendor_id', 'sale_date']);
});

Schema::table('stall_assignments', function (Blueprint $table) {
    $table->index(['vendor_id', 'assigned_date']);
});
```

2. **Optional: Implement Caching** (if metrics are requested frequently):

```php
use Illuminate\Support\Facades\Cache;

public static function getSummaryForDate($vendorId, $date)
{
    $date = Carbon::parse($date)->toDateString();
    $cacheKey = "vendor_summary:{$vendorId}:{$date}";
    
    return Cache::remember($cacheKey, 3600, function () use ($vendorId, $date) {
        // Expensive queries here
        return [
            'gross_sales' => self::calculateGrossSales($vendorId, $date),
            // ...
        ];
    });
}
```

3. **Optional: Use Database Views** (for pre-computed SQL queries):

```sql
-- Commented out by default in migration 2026_03_05_000004_create_summary_views.php
-- Uncomment if you want to use SQL views directly
SELECT * FROM vendor_daily_sales_view 
WHERE vendor_id = 1 AND summary_date = '2026-03-05';
```

---

## 🔍 EDGE CASES & CORRECTNESS

### 1. Cancelled/Refunded Orders

**Status filters in countPreOrders():**
- Includes: `'confirmed', 'ready', 'completed', 'out_for_delivery', 'delivered'`
- Excludes: `'cancelled', 'pending', 'awaiting_rider'`

If you have refund logic, update the status filters accordingly.

### 2. No Transaction Fees

**Vendors keep 100% of sales revenue:**
- `net_sales` = `gross_sales` (no deductions)
- `total_fees` has been removed (not applicable)
- Market revenue = stall rental fees (see `stall_payments` table)

### 3. Market Fees Table

The `market_fees` table exists but is **NOT used** for sales transactions.
- It may have been intended for transaction fees originally
- Now vendors pay NO percentage or fixed fees on sales
- Only expense is stall rent (billed separately via `stall_payments`)

### 4. Date Boundary Issues

When querying `WHERE DATE(column) = $date`:
- Accurate to the day (time portion ignored)
- Uses database's DATE() function, not PHP

---

## 🚀 DEPLOYMENT STEPS

### Step 1: Backup

```bash
# Backup current database
mysqldump -u user -p database > backup_$(date +%s).sql
```

### Step 2: Run Migrations

```bash
php artisan migrate
# Or for specific migrations:
php artisan migrate --path=database/migrations/2026_03_05_000001_drop_vendor_summary_table.php
```

### Step 3: Search & Replace in Codebase

Search for any references to removed tables:
```bash
grep -r "vendor_summary\|market_summary\|vendor_transactions" app/
grep -r "->vendor_summary\|->market_summary" resources/
```

**No matches expected** (these tables were unused).

### Step 4: Test

```bash
php artisan tinker

# Test Vendor metrics
>>> use App\Services\VendorSummaryService;
>>> VendorSummaryService::getSummaryForDate(1, '2026-03-05');

# Test Market metrics  
>>> use App\Services\MarketSummaryService;
>>> MarketSummaryService::getSummaryForDate('2026-03-05');
```

---

## 📝 ROLLBACK PLAN

If needed to revert:

```bash
php artisan migrate:rollback --steps=3
```

This will restore:
1. `vendor_transactions` table
2. `market_summary` table
3. `vendor_summary` table

The `down()` methods in migrations handle the restoration.

---

## ⚠️ KNOWN LIMITATIONS

1. **Slow Complex Queries**: If you have millions of orders, the daterange queries might be slow. Use database views or add caching.

2. **Fee Calculation Accuracy**: Fixed fees are applied uniformly. If you need per-transaction fees, enhance the logic.

3. **Views Not Materialized**: Database views are computed on every query. Not suitable for very high-traffic dashboards without caching.

---

## 🔗 RELATED TABLES (NOT CHANGED)

These tables are NOT affected and remain as-is:

- `orders` - Pre-order records
- `order_items` - Individual items in orders
- `walk_in_sales` - Walk-in sales records
- `market_fees` - Fee configuration table (exists but NOT used for sales transactions)
- `stall_assignments` - Vendor ↔ Stall mappings
- `stall_payments` - **PRIMARY revenue source for market** (vendors pay rent for stalls)
- `products` - Product catalog
- `vendors` - Vendor profiles

**Note:** The `market_fees` table may be repurposed or removed in the future since vendors pay NO transaction fees.

---

## 📞 SUMMARY

### Key Design Decisions:

1. **NO Transaction Fees:** Vendors keep 100% of sales revenue
   - `gross_sales` = `net_sales`
   - `total_fees` removed from all summary responses

2. **Market Revenue = Stall Rent Only**
   - Managed via `stall_payments` table
   - Separate from sales transactions

3. **Use PHP Service Classes (Recommended)**
   - `VendorSummaryService` for vendor metrics
   - `MarketSummaryService` for market-wide metrics
   - Database views are optional and commented out

4. **Add indexes for query performance**
   - See migration `2026_03_05_000005_add_performance_indexes_for_summaries.php`

5. **Consider caching for high-traffic scenarios**
   - Use Laravel Cache for frequently accessed summaries

All migration files are reversible via `php artisan migrate:rollback`.
