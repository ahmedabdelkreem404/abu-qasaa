# Database Plan

The platform uses one MySQL database. Business-specific records must include `business_unit_id` unless they are intentionally global or shared, such as activity templates, activity modules, and optionally shared catalog records.

## Main Tables

`business_units`, `activity_templates`, `activity_modules`, `business_unit_modules`, `business_unit_settings`, `feature_flags`, and `user_business_units` provide the platform control plane.

Phase 1 hardens the control-plane schema:

- `business_units`: hierarchy, Arabic/English names, slug, type, status, branding fields, settings JSON, and creator placeholder.
- `activity_templates`: default module keys and settings for new business units.
- `activity_modules`: code-backed capabilities that Super Admins can enable.
- `business_unit_modules`: per-business-unit module enablement.
- `business_unit_settings`: typed key/value settings per business unit.
- `feature_flags`: global flags when `business_unit_id` is null and scoped flags when present.

## Catalog And Ecommerce

`categories`, `brands`, `products`, `product_variants`, `price_lists`, and `product_prices` support product-based activities. `orders` and `order_items` support ecommerce workflows.

## Inventory

`branches`, `warehouses`, `stock_items`, and `stock_movements` prepare the platform for warehouse and branch-aware stock tracking.

## Payments

`payments` uses a polymorphic payable reference so different modules can receive payments later. `payment_transactions` stores provider interaction history. `manual_payment_proofs` is prepared for Vodafone Cash, Instapay, bank transfer, and similar manual methods. Paymob credentials remain environment-only placeholders.

## Services And RFQ

Import/export workflows should use `services`, `rfq_requests`, and `rfq_items` by default instead of ecommerce checkout.

## Real Estate

`real_estate_projects`, `properties`, `property_units`, `leads`, and `appointments` isolate real estate workflows from product commerce.

## CMS And Audit

`cms_pages` and `media` support public content. `audit_logs` records actor and entity changes across modules.
