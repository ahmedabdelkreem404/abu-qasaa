"use client";

import {
  adjustStock,
  createBranch,
  createWarehouse,
  getInventorySummary,
  listBranches,
  listStockItems,
  listStockMovements,
  listStockTransfers,
  listWarehouses,
  receiveStock,
} from "@/api/client";
import { ApiErrorState, EmptyState } from "@/components/shared/api-state";
import type { Branch, InventorySummary, StockItem, StockMovement, StockTransfer, Warehouse } from "@/types/platform";
import { useCallback, useEffect, useState } from "react";

export default function DashboardInventoryPage() {
  const [summary, setSummary] = useState<InventorySummary | null>(null);
  const [branches, setBranches] = useState<Branch[]>([]);
  const [warehouses, setWarehouses] = useState<Warehouse[]>([]);
  const [stockItems, setStockItems] = useState<StockItem[]>([]);
  const [movements, setMovements] = useState<StockMovement[]>([]);
  const [transfers, setTransfers] = useState<StockTransfer[]>([]);
  const [businessUnitId, setBusinessUnitId] = useState("1");
  const [error, setError] = useState<string | null>(null);
  const [notice, setNotice] = useState<string | null>(null);

  const reload = useCallback(async () => {
    try {
      const params = new URLSearchParams();
      if (businessUnitId) params.set("business_unit_id", businessUnitId);
      const [summaryResponse, branchResponse, warehouseResponse, stockResponse, movementResponse, transferResponse] = await Promise.all([
        getInventorySummary(params),
        listBranches(params),
        listWarehouses(params),
        listStockItems(params),
        listStockMovements(params),
        listStockTransfers(params),
      ]);
      setSummary(summaryResponse.data);
      setBranches(branchResponse.data);
      setWarehouses(warehouseResponse.data);
      setStockItems(stockResponse.data);
      setMovements(movementResponse.data);
      setTransfers(transferResponse.data);
      setError(null);
    } catch (caught) {
      setError(caught instanceof Error && caught.name === "403" ? "Forbidden." : "Could not load inventory.");
    }
  }, [businessUnitId]);

  useEffect(() => {
    const timeout = window.setTimeout(() => void reload(), 0);

    return () => window.clearTimeout(timeout);
  }, [reload]);

  async function submitDemoBranch() {
    await createBranch({ business_unit_id: Number(businessUnitId), name_ar: "New Branch", name_en: "New Branch", slug: `branch-${Date.now()}`, status: "active", is_public: true });
    setNotice("Branch created.");
    await reload();
  }

  async function submitDemoWarehouse() {
    await createWarehouse({ business_unit_id: Number(businessUnitId), name_ar: "New Warehouse", name_en: "New Warehouse", slug: `warehouse-${Date.now()}`, type: "branch", status: "active", is_sellable: true });
    setNotice("Warehouse created.");
    await reload();
  }

  async function receiveFirstProduct() {
    const first = stockItems[0];
    if (!first) return;
    await receiveStock({ business_unit_id: first.business_unit_id, warehouse_id: first.warehouse_id, product_id: first.product_id, product_variant_id: first.product_variant_id, quantity: 5, note: "Dashboard receive." });
    setNotice("Stock received.");
    await reload();
  }

  async function adjustFirstProduct() {
    const first = stockItems[0];
    if (!first) return;
    await adjustStock({ business_unit_id: first.business_unit_id, warehouse_id: first.warehouse_id, product_id: first.product_id, product_variant_id: first.product_variant_id, type: "adjustment_out", quantity: 1, note: "Dashboard adjustment." });
    setNotice("Stock adjusted.");
    await reload();
  }

  if (error) return <ApiErrorState message={error} />;

  return (
    <section className="space-y-6">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-semibold">Inventory</h1>
          <p className="mt-1 text-sm text-slate-600">Branches, warehouses, stock levels, movements, reservations, and transfers.</p>
        </div>
        <label className="grid gap-1 text-sm">
          Business unit ID
          <input value={businessUnitId} onChange={(event) => setBusinessUnitId(event.target.value)} onBlur={() => void reload()} className="w-36 rounded-md border border-slate-300 px-3 py-2" />
        </label>
      </div>

      {notice ? <div className="rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{notice}</div> : null}

      <div className="grid gap-3 md:grid-cols-3 xl:grid-cols-6">
        <Metric label="Branches" value={summary?.branches_count} />
        <Metric label="Warehouses" value={summary?.warehouses_count} />
        <Metric label="Stock items" value={summary?.stock_items_count} />
        <Metric label="Low stock" value={summary?.low_stock_count} />
        <Metric label="Reserved" value={summary?.reserved_quantity} />
        <Metric label="Open transfers" value={summary?.open_transfers_count} />
      </div>

      <div className="flex flex-wrap gap-2">
        <button onClick={() => void submitDemoBranch()} className="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white">Create branch</button>
        <button onClick={() => void submitDemoWarehouse()} className="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white">Create warehouse</button>
        <button onClick={() => void receiveFirstProduct()} className="rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white">Receive +5</button>
        <button onClick={() => void adjustFirstProduct()} className="rounded-md bg-amber-700 px-3 py-2 text-sm font-medium text-white">Adjust -1</button>
      </div>

      <InventoryTable title="Stock" empty="No stock items yet." rows={stockItems.map((item) => [item.product?.name_en ?? item.product?.name_ar ?? `Product ${item.product_id}`, item.warehouse?.name_en ?? item.warehouse?.name_ar ?? `Warehouse ${item.warehouse_id}`, item.quantity_on_hand, item.quantity_reserved, item.quantity_available])} headers={["Product", "Warehouse", "On hand", "Reserved", "Available"]} />
      <InventoryTable title="Warehouses" empty="No warehouses yet." rows={warehouses.map((warehouse) => [warehouse.name_en ?? warehouse.name_ar, warehouse.type, warehouse.status, warehouse.is_default ? "Default" : "-", warehouse.is_sellable ? "Sellable" : "Internal"])} headers={["Name", "Type", "Status", "Default", "Mode"]} />
      <InventoryTable title="Branches" empty="No branches yet." rows={branches.map((branch) => [branch.name_en ?? branch.name_ar, branch.status, branch.city ?? "-", branch.is_public ? "Public" : "Private"])} headers={["Name", "Status", "City", "Visibility"]} />
      <InventoryTable title="Latest movements" empty="No stock movements yet." rows={movements.map((movement) => [movement.product?.name_en ?? movement.product?.name_ar ?? "Product", movement.type, movement.reason, movement.quantity, movement.quantity_after])} headers={["Product", "Type", "Reason", "Qty", "After"]} />
      <InventoryTable title="Transfers" empty="No transfers yet." rows={transfers.map((transfer) => [transfer.transfer_number, transfer.from_warehouse?.name_en ?? transfer.from_warehouse?.name_ar ?? "-", transfer.to_warehouse?.name_en ?? transfer.to_warehouse?.name_ar ?? "-", transfer.status])} headers={["Number", "From", "To", "Status"]} />
    </section>
  );
}

function Metric({ label, value }: { label: string; value?: string | number | null }) {
  return <div className="rounded-md border border-slate-200 bg-white p-4"><p className="text-xs uppercase text-slate-500">{label}</p><p className="mt-2 text-2xl font-semibold">{value ?? "-"}</p></div>;
}

function InventoryTable({ title, headers, rows, empty }: { title: string; headers: string[]; rows: Array<Array<string | number>>; empty: string }) {
  return (
    <div className="overflow-hidden rounded-md border border-slate-200 bg-white">
      <div className="border-b border-slate-100 px-4 py-3"><h2 className="font-semibold">{title}</h2></div>
      {rows.length === 0 ? <EmptyState message={empty} /> : <table className="w-full text-left text-sm"><thead className="bg-slate-50"><tr>{headers.map((header) => <th key={header} className="p-3">{header}</th>)}</tr></thead><tbody>{rows.map((row, index) => <tr key={`${title}-${index}`} className="border-t border-slate-100">{row.map((cell, cellIndex) => <td key={`${title}-${index}-${cellIndex}`} className="p-3">{cell}</td>)}</tr>)}</tbody></table>}
    </div>
  );
}
