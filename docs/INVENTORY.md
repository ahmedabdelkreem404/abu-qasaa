# Inventory And Warehouse Foundation

Phase 8 adds inventory as a business-unit scoped module inside the modular monolith.

## Scope

- Branches and warehouses belong to one business unit.
- Stock items track product and optional variant quantities by warehouse.
- Stock movements are append-only audit records for receive, adjust, reserve, release, sale, and transfer operations.
- Stock reservations are created during backend checkout when inventory is enabled.
- Stock transfers move available stock between warehouses in the same business unit.
- Public storefronts can read branch locations and product availability.

## Checkout Behavior

Checkout reserves stock only after the backend creates an order and order items inside a database transaction. If any requested product has insufficient available stock, checkout fails and no paid state is created.

Return URLs and frontend screens never mark stock as fulfilled. Fulfillment happens only through order status changes to `shipped` or `delivered`, or the dashboard stock fulfillment endpoint.

## Dashboard Behavior

Dashboard inventory users can view summaries, branches, warehouses, stock levels, movements, and transfers. Users with adjustment permissions can receive stock, adjust stock, and fulfill reserved order stock.

## Boundaries

This phase does not implement shipping integrations, supplier purchase orders, valuation/accounting, lot or expiry tracking, barcode scanning, inventory deduction for non-order workflows, real estate logic, RFQ logic, or microservices.

## Configuration

Inventory is enabled per business unit through the `inventory` module and `inventory_enabled` setting. If either is disabled, checkout skips reservation and public availability treats products as available.
