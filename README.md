# Orangecat_PricesList

Customer-specific B2B price lists with volume tiers, multi-list resolution, and import support.

**Module:** `Orangecat_PricesList`
**Version:** 1.0.0
**License:** OSL-3.0
**Author:** Oliverio Gombert <olivertar@gmail.com>

---

## Table of Contents

1. [Overview](#overview)
2. [Theme Compatibility](#theme-compatibility)
3. [Requirements](#requirements)
4. [Installation](#installation)
5. [What Gets Installed](#what-gets-installed)
6. [Configuration](#configuration)
7. [Store Admin Guide](#store-admin-guide)
8. [Developer Guide](#developer-guide)
9. [REST API](#rest-api)
10. [Frontend Routes Reference](#frontend-routes-reference)
11. [DevOps & Integrator Notes](#devops--integrator-notes)

---

## Overview

`Orangecat_PricesList` provides a **named, reusable price list** system for Magento 2 B2B stores. Each price list contains per-SKU pricing rules (fixed price, percentage discount, or fixed amount discount) with optional volume tiers based on minimum quantity. Price lists are assigned to companies with a numeric priority that controls which list wins when a customer belongs to a company with multiple assigned lists.

The module also exposes a **CSV import** pipeline for bulk price management and a full **REST API** for integrations.

Responsibilities:

- Create and manage named price lists with date-bounded validity windows
- Define per-SKU pricing rules supporting three discount types and volume tiers
- Assign one or more price lists to a company with individual priority values
- Resolve the effective price for a SKU/company/qty combination via Cascade or Weight mode
- Register a `PricesListCalculator` into the `Orangecat_Prices` calculator pool
- Provide CSV bulk import for price list items via the native Magento import pipeline
- Expose a complete REST API for price list, item, and company-association management

### Position in the Orangecat B2B Dependency Chain

```
Orangecat_Core (via composer: orangecat/core)
  └── Orangecat_Company
        └── Orangecat_Prices
              └── Orangecat_PricesList     ← this module
                    └── Orangecat_PricesCompany  (optional bridge)
```

### Price Resolution Modes

When a company has more than one price list, the calculator resolves the effective price using the configured mode:

| Mode | Behaviour |
|------|-----------|
| **Cascade** (default) | Iterates lists in descending priority order. The first list that contains the SKU and satisfies the minimum quantity requirement wins. Lower-priority lists are ignored. |
| **Weight** | All valid lists are evaluated. The lowest computed price across all lists wins. |

In both modes, a list entry only qualifies if the cart quantity is ≥ the entry's `qty` threshold. Higher `priority` value = higher precedence.

### Discount Types

| Type | Stored value | Behaviour |
|------|-------------|-----------|
| Fixed Price Override | `fixed_price` | Replaces base price with `amount` |
| Percentage Discount | `percentage` | `base_price × (1 − amount / 100)` |
| Fixed Amount Discount | `fixed_amount` | `base_price − amount` |

All computed prices are floor-clamped to `0`.

---

## Theme Compatibility

| Theme | Status | Notes |
|-------|--------|-------|
| Luma | Supported | Admin UI only; no storefront templates |
| Hyvä | Supported | Admin UI only; no storefront templates |
| Breeze Evolution | Supported | Admin UI only; no storefront templates |

This module has no frontend (storefront) templates. Price calculation happens server-side inside `Orangecat_Prices`; the rendered price on the product page depends on how `Orangecat_Prices` injects the calculated price into the theme. No theme-specific work is required in this module.

---

## Requirements

| Dependency | Version / Notes |
|------------|----------------|
| PHP | ≥ 8.1 |
| Magento Framework | 2.4.x |
| `magento/module-catalog` | Bundled with Magento |
| `magento/module-import-export` | Bundled with Magento — required for CSV import |
| `orangecat/core` | Any |
| `Orangecat_Company` | Must be installed and enabled first |
| `Orangecat_Prices` | Must be installed and enabled first (provides `CalculatorPool`) |

---

## Installation

This module ships as a git submodule inside the B2B SDK repository.

```bash
# 1. Initialise the submodule (first time only)
git submodule update --init app/code/Orangecat/PricesList

# 2. Inside the PHP container
reward shell

# 3. Enable the module and run setup
bin/magento module:enable Orangecat_PricesList
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

---

## What Gets Installed

### Database Tables

#### `priceslist`

| Column | Type | Notes |
|--------|------|-------|
| `entity_id` | `int unsigned` | Primary key, auto-increment |
| `name` | `varchar(255)` | Display name |
| `code` | `varchar(255)` | Unique machine identifier used in the API |
| `is_active` | `boolean` | Default `1`; inactive lists are not applied |
| `description` | `text` | Optional description |
| `start_date` | `timestamp` | Nullable; list not applied before this date |
| `end_date` | `timestamp` | Nullable; list not applied after this date |
| `created_at` | `timestamp` | Set on insert |
| `updated_at` | `timestamp` | Updated automatically on change |

Unique constraint on `code`.

#### `priceslist_item`

| Column | Type | Notes |
|--------|------|-------|
| `item_id` | `int unsigned` | Primary key, auto-increment |
| `price_list_id` | `int unsigned` | FK → `priceslist.entity_id` (CASCADE DELETE) |
| `sku` | `varchar(64)` | Product SKU |
| `discount_type` | `varchar(32)` | `fixed_price`, `percentage`, or `fixed_amount` |
| `amount` | `decimal(20,4)` | The discount or price value |
| `qty` | `decimal(12,4)` | Minimum quantity threshold; default `1.0000` |
| `created_at` | `timestamp` | Set on insert |
| `updated_at` | `timestamp` | Updated automatically on change |

Unique constraint on `(price_list_id, sku, qty)` — enables volume tiers per SKU.

#### `priceslist_company`

| Column | Type | Notes |
|--------|------|-------|
| `link_id` | `int unsigned` | Primary key, auto-increment |
| `company_id` | `int unsigned` | FK → `mycompany.entity_id` (CASCADE DELETE) |
| `price_list_id` | `int unsigned` | FK → `priceslist.entity_id` (CASCADE DELETE) |
| `priority` | `int unsigned` | Default `0`; higher value = higher precedence |

Unique constraint on `(company_id, price_list_id)`.

### EAV Attributes

None.

### Data Patches

None. No default records, roles, or CMS pages are created on install.

---

## Configuration

### Section: Prices > Price Lists Configuration

Navigate to **Stores → Configuration → Prices → Price Lists Configuration**.

| Label | Config path | Default | Description |
|-------|-------------|---------|-------------|
| Enable Price Lists | `prices/priceslist/enabled` | Yes | Master switch. When disabled, the calculator returns `null` and native pricing applies. |
| Resolution Mode | `prices/priceslist/mode` | cascade | `cascade` or `weight` — see [Price Resolution Modes](#price-resolution-modes). Visible only when enabled. |

Config paths:

```
prices/priceslist/enabled
prices/priceslist/mode
```

Both settings are scoped to Default / Website / Store View.

---

## Store Admin Guide

### Navigating to Price Lists

**Catalog → Manage Price Lists** (added by this module under the `Orangecat_Prices::base` parent menu).

### Creating a Price List

1. Go to **Catalog → Manage Price Lists**.
2. Click **Add New Price List**.
3. Fill in:
   - **Name** — human-readable label.
   - **Code** — unique slug used in the API and CSV import (e.g. `B2B-WHOLESALE`).
   - **Is Active** — set to Active to make this list available for assignment.
   - **Description** — optional internal note.
   - **Start Date / End Date** — optional validity window (leave blank for no limit).
4. Click **Save and Continue Edit** to save and remain on the form.

### Adding Products to a Price List

After saving the price list (so an `entity_id` exists):

1. Inside the price list form, scroll to the **Items** section.
2. Click **Add Products** to open the product selection grid.
3. Select one or more products and click **Add Selected**.
4. A dialog appears for each selected product. For each row:
   - Choose **Discount Type** (`Fixed Price Override`, `Percentage Discount`, `Fixed Amount Discount`).
   - Enter **Amount** (the price, percentage, or discount value).
   - Enter **Qty** — the minimum cart quantity required to unlock this tier. Use `1` for the base price.
5. Click **Confirm & Add** to persist the rows.

To add a volume tier for the same SKU, add the same SKU again with a higher `Qty` value.

Existing items can be edited inline in the items grid or deleted individually with **Delete** or in bulk with **Delete Selected**.

### Assigning Price Lists to a Company

1. Go to **Companies → (select a company) → Edit**.
2. Open the **Price Lists** tab.
3. The grid shows currently assigned lists. Click **Reset Filter** to see all available lists.
4. Check the checkbox next to each list to assign it.
5. Set the **Priority** value for each assigned list. Higher values take precedence.
6. Click **Save Company**. The assignments are persisted atomically.

### Bulk Import via CSV

1. Go to **System → Import**.
2. Set **Entity Type** to `Price List Items`.
3. Select a **Behavior**: `Add/Update`, `Replace`, or `Delete`.
4. Upload a CSV file with columns: `price_list_code`, `sku`, `qty`, `discount_type`, `amount`.
5. Click **Check Data** to validate, then **Import** to execute.

Sample CSV (`Files/Sample/priceslist_item.csv`):

```csv
price_list_code,sku,qty,discount_type,amount
B2B-WHOLESALE,24-MB01,1,percentage,15
B2B-WHOLESALE,24-MB01,10,percentage,25
B2B-PREMIUM,24-MB01,1,fixed_price,25.99
B2B-PREMIUM,24-MB04,1,fixed_amount,5
```

Validation rules:
- `price_list_code` must exist in `priceslist`.
- `sku` must exist in the product catalog.
- `discount_type` must be one of `fixed_price`, `fixed_amount`, `percentage`.
- `qty` and `amount` are required numeric fields.

---

## Developer Guide

### Module Structure

```
Orangecat/PricesList/
├── Api/
│   ├── Data/
│   │   ├── PriceListInterface.php           # Price list entity contract
│   │   ├── PriceListItemInterface.php       # Price list item entity contract
│   │   ├── PriceListCompanyInterface.php    # Company-list link contract
│   │   ├── PriceListSearchResultsInterface.php
│   │   └── PriceListItemSearchResultsInterface.php
│   ├── PriceListRepositoryInterface.php     # CRUD + getByCode/deleteByCode
│   ├── PriceListItemRepositoryInterface.php # Item CRUD
│   ├── PriceListManagementInterface.php     # getPrices / addPrices / removePrices
│   └── PriceListCompanyManagementInterface.php  # Company-association management
├── Block/Adminhtml/
│   ├── Company/Edit/Tab/
│   │   ├── PriceListGrid.php               # Widget grid in Company edit form
│   │   └── Renderer/
│   │       ├── Checkbox.php                # Checkbox column renderer
│   │       └── PriorityInput.php           # Priority input renderer
│   └── PriceList/Edit/                     # Form button blocks
├── Controller/Adminhtml/
│   ├── PriceList/                          # Index, New, Edit, Save, Delete, InlineEdit
│   ├── PriceList/Item/                     # Add, Delete, MassDelete, InlineEdit, GetProducts
│   └── Company/Pricelist/Grid.php          # AJAX grid reload for Company tab
├── Model/
│   ├── PriceList.php / PriceListItem.php / PriceListCompany.php
│   ├── PriceListRepository.php / PriceListItemRepository.php
│   ├── PriceListManagement.php / PriceListCompanyManagement.php
│   ├── Config.php                          # system config accessor
│   ├── Config/Source/Mode.php              # cascade | weight
│   ├── Config/Source/DiscountType.php      # fixed_price | percentage | fixed_amount
│   ├── Calculator/PricesListCalculator.php # PriceCalculatorInterface implementation
│   ├── Import/PricesListItem.php           # Magento import entity
│   └── Import/PricesListItem/Validator.php
├── Plugin/Company/Controller/Adminhtml/Company/
│   └── SavePriceLists.php                  # afterExecute on Company\Save
├── Files/Sample/priceslist_item.csv
├── etc/
│   ├── db_schema.xml
│   ├── di.xml
│   ├── webapi.xml
│   ├── import.xml
│   ├── config.xml
│   └── adminhtml/menu.xml, routes.xml, system.xml
└── view/adminhtml/
    ├── layout/
    ├── ui_component/
    │   ├── priceslist_listing.xml
    │   ├── priceslist_form.xml
    │   ├── priceslist_item_listing.xml
    │   ├── priceslist_companies_listing.xml
    │   └── mycompany_company_form.xml       # injects Price Lists tab into Company form
    └── web/js/add-products.js              # product-picker + pricing dialog
```

### Key Classes

#### Service Contracts

| Interface | Implementation | Description |
|-----------|---------------|-------------|
| `PriceListRepositoryInterface` | `Model\PriceListRepository` | `save`, `getById`, `getByCode`, `getList`, `deleteById`, `deleteByCode` |
| `PriceListItemRepositoryInterface` | `Model\PriceListItemRepository` | Standard CRUD for price list items |
| `PriceListManagementInterface` | `Model\PriceListManagement` | `getPrices($code)`, `addPrices($code, $items[])`, `removePrices($code, $skus[])` |
| `PriceListCompanyManagementInterface` | `Model\PriceListCompanyManagement` | `getCompanies($code)`, `associate($code, $companyId, $priority)`, `removeAssociation($code, $companyId)` |

#### Calculator

`Model\Calculator\PricesListCalculator` implements `Orangecat\Prices\Api\PriceCalculatorInterface` and is registered into `Orangecat\Prices\Model\CalculatorPool` via `di.xml`. The orchestrator in `Orangecat_Prices` calls `calculate(sku, qty, companyId, basePrice)` on each registered calculator.

```php
public function calculate(string $sku, float $qty, int $companyId, float $basePrice = 0.0): ?float
public function getTiers(string $sku, int $companyId, float $basePrice = 0.0): array
```

Returns `null` when:
- The module is disabled in config.
- The company has no price lists assigned.
- No price list contains the requested SKU.
- No entry satisfies the quantity threshold.

### Observers

None. This module registers no event observers.

### Plugins

| Class | Target | Hook | Purpose |
|-------|--------|------|---------|
| `Plugin\Company\Controller\Adminhtml\Company\SavePriceLists` | `Orangecat\Company\Controller\Adminhtml\Company\Save` | `afterExecute` | Reads `price_list_ids[]` and `price_list_priority[]` from POST and atomically replaces `priceslist_company` rows for the saved company. |

### Admin JS Components

| File | Purpose |
|------|---------|
| `view/adminhtml/web/js/add-products.js` | Extends `Magento_Ui/js/form/components/button`. Orchestrates the product-picker modal → pricing dialog → AJAX save flow for adding items to a price list. |

### Email Templates

None.

### ACL Resources

There is no `etc/acl.xml` in this module. All admin controllers and REST API routes reference the resource `Orangecat_PricesList::priceslist`. This resource must be defined in a parent module or added manually if fine-grained ACL control is required. Admin users with full access will have access to all price list actions.

### Adding Custom Logic

- **Custom discount type:** Add a new constant to `Model\Config\Source\DiscountType` and handle the new value in `Model\Calculator\PricesListCalculator::calculate()` and `getTiers()`.
- **Custom calculator:** Implement `Orangecat\Prices\Api\PriceCalculatorInterface` and register it in `Orangecat\Prices\Model\CalculatorPool` via your module's `di.xml`. No changes to this module are needed.
- **Post-assignment hook:** Add an `afterExecute` plugin on `Model\PriceListCompanyManagement::associate` to trigger downstream events (cache warm-up, ERP sync, etc.).

---

## REST API

All endpoints require an admin integration token with the `Orangecat_PricesList::priceslist` ACL resource.

### Price List CRUD

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/V1/priceslist` | Create a new price list |
| `GET` | `/V1/priceslist/:entityId` | Get price list by ID |
| `PUT` | `/V1/priceslist/:entityId` | Update a price list |
| `DELETE` | `/V1/priceslist/:entityId` | Delete by ID |
| `GET` | `/V1/priceslist/search` | List with search criteria |
| `GET` | `/V1/priceslist/code/:code` | Get by unique code |
| `DELETE` | `/V1/priceslist/code/:code` | Delete by unique code |

### Price List Items

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/V1/priceslist/:priceListCode/prices` | Get all items for a price list |
| `POST` | `/V1/priceslist/:priceListCode/prices` | Add or update items (array of `PriceListItemInterface`) |
| `POST` | `/V1/priceslist/:priceListCode/prices/remove` | Remove items by SKU array |

### Company Associations

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/V1/priceslist/:priceListCode/companies` | Get companies assigned to this list |
| `POST` | `/V1/priceslist/:priceListCode/companies` | Assign a company (with priority) |
| `DELETE` | `/V1/priceslist/:priceListCode/companies/:companyId` | Remove a company assignment |

### Examples

**Create a price list:**

```bash
curl -X POST https://b2bsdk.test/rest/V1/priceslist \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "priceList": {
      "name": "Wholesale 2025",
      "code": "B2B-WHOLESALE",
      "is_active": true,
      "description": "Standard wholesale pricing"
    }
  }'
```

**Add items with a volume tier:**

```bash
curl -X POST https://b2bsdk.test/rest/V1/priceslist/B2B-WHOLESALE/prices \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "prices": [
      { "sku": "24-MB01", "discount_type": "percentage", "amount": 15, "qty": 1 },
      { "sku": "24-MB01", "discount_type": "percentage", "amount": 25, "qty": 10 }
    ]
  }'
```

**Assign to a company with priority:**

```bash
curl -X POST https://b2bsdk.test/rest/V1/priceslist/B2B-WHOLESALE/companies \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "companyId": 3,
    "priority": 10
  }'
```

---

## Frontend Routes Reference

This module has no frontend (storefront) routes. All controllers are under the `adminhtml` area.

Admin route prefix: `orangecat_priceslist`

| Path pattern | Controller | Access |
|-------------|------------|--------|
| `orangecat_priceslist/pricelist/index` | `Controller\Adminhtml\PriceList\Index` | Admin — `Orangecat_PricesList::priceslist` |
| `orangecat_priceslist/pricelist/new` | `Controller\Adminhtml\PriceList\NewAction` | Admin |
| `orangecat_priceslist/pricelist/edit` | `Controller\Adminhtml\PriceList\Edit` | Admin |
| `orangecat_priceslist/pricelist/save` | `Controller\Adminhtml\PriceList\Save` | Admin |
| `orangecat_priceslist/pricelist/delete` | `Controller\Adminhtml\PriceList\Delete` | Admin |
| `orangecat_priceslist/pricelist/inlineEdit` | `Controller\Adminhtml\PriceList\InlineEdit` | Admin |
| `orangecat_priceslist/pricelist_item/add` | `Controller\Adminhtml\PriceList\Item\Add` | Admin |
| `orangecat_priceslist/pricelist_item/delete` | `Controller\Adminhtml\PriceList\Item\Delete` | Admin |
| `orangecat_priceslist/pricelist_item/massDelete` | `Controller\Adminhtml\PriceList\Item\MassDelete` | Admin |
| `orangecat_priceslist/pricelist_item/inlineEdit` | `Controller\Adminhtml\PriceList\Item\InlineEdit` | Admin |
| `orangecat_priceslist/pricelist_item/getProducts` | `Controller\Adminhtml\PriceList\Item\GetProducts` | Admin (AJAX) |
| `orangecat_priceslist/company_pricelist/grid` | `Controller\Adminhtml\Company\Pricelist\Grid` | Admin (AJAX) |

---

## DevOps & Integrator Notes

### Deployment Checklist

```bash
# Inside reward shell
bin/magento module:enable Orangecat_PricesList
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
bin/magento indexer:reindex
```

### Integration Token Scope

Minimum ACL permission required for all REST API operations: `Orangecat_PricesList::priceslist`.

### Disabling Without Uninstalling

```bash
bin/magento module:disable Orangecat_PricesList
bin/magento setup:upgrade
bin/magento cache:flush
```

When disabled, `PricesListCalculator::calculate()` returns `null` immediately, so the `Orangecat_Prices` orchestrator falls back to the next registered calculator or the native Magento price. No data is removed.

### Data Integrity Notes

- Deleting a price list cascades to all `priceslist_item` rows for that list.
- Deleting a company (from `mycompany`) cascades to all `priceslist_company` rows for that company.
- Deleting a price list cascades to all `priceslist_company` rows linking it to companies.
- The unique constraint on `priceslist_item(price_list_id, sku, qty)` enforces that each price list has at most one price entry per SKU/quantity tier. The import uses `INSERT ON DUPLICATE KEY UPDATE` to upsert entries cleanly.
- The `code` field on `priceslist` is unique across the entire installation (no store-scope). Use descriptive, stable codes in integrations — the REST API and CSV import both reference price lists by code.
