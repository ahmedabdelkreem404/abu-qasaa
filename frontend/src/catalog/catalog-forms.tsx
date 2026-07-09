"use client";

import {
  createBrand,
  createCategory,
  createPriceList,
  createProduct,
  updateBrand,
  updateCategory,
  updatePriceList,
  updateProduct,
  type BrandPayload,
  type CategoryPayload,
  type PriceListPayload,
  type ProductPayload,
} from "@/api/client";
import type { Brand, BrandStatus, Category, CategoryStatus, PriceList, PriceListType, Product, ProductStatus, ProductType, ProductVisibility } from "@/types/platform";
import { useRouter } from "next/navigation";
import { FormEvent, useState } from "react";

function text(form: FormData, key: string) {
  return String(form.get(key) ?? "");
}

function nullableText(form: FormData, key: string) {
  const value = text(form, key);
  return value === "" ? null : value;
}

function numberOrNull(form: FormData, key: string) {
  const value = text(form, key);
  return value === "" ? null : Number(value);
}

function bool(form: FormData, key: string) {
  return form.get(key) === "on";
}

export function CategoryForm({ category }: { category?: Category }) {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const payload: CategoryPayload = {
      business_unit_id: Number(form.get("business_unit_id")),
      parent_id: numberOrNull(form, "parent_id"),
      name_ar: text(form, "name_ar"),
      name_en: nullableText(form, "name_en"),
      slug: text(form, "slug"),
      description_en: nullableText(form, "description_en"),
      status: text(form, "status") as CategoryStatus,
      sort_order: Number(form.get("sort_order") || 0),
    };
    try {
      await (category ? updateCategory(category.id, payload) : createCategory(payload));
      router.push("/dashboard/catalog/categories");
      router.refresh();
    } catch {
      setError("Could not save category.");
    }
  }

  return <CatalogForm onSubmit={onSubmit} error={error} submit="Save category">
    <BasicScopeFields item={category} />
    <TextField name="name_ar" label="Arabic name" required defaultValue={category?.name_ar} />
    <TextField name="name_en" label="English name" defaultValue={category?.name_en ?? ""} />
    <TextField name="slug" label="Slug" required defaultValue={category?.slug} />
    <TextField name="parent_id" label="Parent category ID" defaultValue={category?.parent_id ?? ""} />
    <SelectField name="status" label="Status" defaultValue={category?.status ?? "active"} values={["active", "inactive", "archived"]} />
    <TextAreaField name="description_en" label="English description" defaultValue={category?.description_en ?? ""} />
    <TextField name="sort_order" label="Sort order" defaultValue={category?.sort_order ?? 0} />
  </CatalogForm>;
}

export function BrandForm({ brand }: { brand?: Brand }) {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const payload: BrandPayload = {
      business_unit_id: Number(form.get("business_unit_id")),
      name_ar: text(form, "name_ar"),
      name_en: nullableText(form, "name_en"),
      slug: text(form, "slug"),
      description_en: nullableText(form, "description_en"),
      status: text(form, "status") as BrandStatus,
      sort_order: Number(form.get("sort_order") || 0),
    };
    try {
      await (brand ? updateBrand(brand.id, payload) : createBrand(payload));
      router.push("/dashboard/catalog/brands");
      router.refresh();
    } catch {
      setError("Could not save brand.");
    }
  }

  return <CatalogForm onSubmit={onSubmit} error={error} submit="Save brand">
    <BasicScopeFields item={brand} />
    <TextField name="name_ar" label="Arabic name" required defaultValue={brand?.name_ar} />
    <TextField name="name_en" label="English name" defaultValue={brand?.name_en ?? ""} />
    <TextField name="slug" label="Slug" required defaultValue={brand?.slug} />
    <SelectField name="status" label="Status" defaultValue={brand?.status ?? "active"} values={["active", "inactive", "archived"]} />
    <TextAreaField name="description_en" label="English description" defaultValue={brand?.description_en ?? ""} />
    <TextField name="sort_order" label="Sort order" defaultValue={brand?.sort_order ?? 0} />
  </CatalogForm>;
}

export function ProductForm({ product }: { product?: Product }) {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    let specs: Record<string, unknown> | undefined;
    try {
      specs = text(form, "specs_json") ? JSON.parse(text(form, "specs_json")) : undefined;
    } catch {
      setError("Specs must be valid JSON.");
      return;
    }
    const payload: ProductPayload = {
      business_unit_id: Number(form.get("business_unit_id")),
      category_id: numberOrNull(form, "category_id"),
      brand_id: numberOrNull(form, "brand_id"),
      name_ar: text(form, "name_ar"),
      name_en: nullableText(form, "name_en"),
      slug: text(form, "slug"),
      sku: nullableText(form, "sku"),
      product_type: text(form, "product_type") as ProductType,
      status: text(form, "status") as ProductStatus,
      visibility: text(form, "visibility") as ProductVisibility,
      short_description_en: nullableText(form, "short_description_en"),
      description_en: nullableText(form, "description_en"),
      base_price: nullableText(form, "base_price"),
      compare_at_price: nullableText(form, "compare_at_price"),
      cost_price: nullableText(form, "cost_price"),
      currency: text(form, "currency") || "EGP",
      is_featured: bool(form, "is_featured"),
      is_taxable: !form.has("is_taxable") ? false : bool(form, "is_taxable"),
      min_order_quantity: Number(form.get("min_order_quantity") || 1),
      max_order_quantity: numberOrNull(form, "max_order_quantity"),
      specs_json: specs,
    };
    try {
      const response = await (product ? updateProduct(product.id, payload) : createProduct(payload));
      router.push(`/dashboard/catalog/products/${response.data.id}`);
      router.refresh();
    } catch {
      setError("Could not save product.");
    }
  }

  return <CatalogForm onSubmit={onSubmit} error={error} submit="Save product">
    <BasicScopeFields item={product} />
    <div className="grid gap-4 md:grid-cols-2">
      <TextField name="category_id" label="Category ID" defaultValue={product?.category_id ?? ""} />
      <TextField name="brand_id" label="Brand ID" defaultValue={product?.brand_id ?? ""} />
    </div>
    <TextField name="name_ar" label="Arabic name" required defaultValue={product?.name_ar} />
    <TextField name="name_en" label="English name" defaultValue={product?.name_en ?? ""} />
    <div className="grid gap-4 md:grid-cols-2">
      <TextField name="slug" label="Slug" required defaultValue={product?.slug} />
      <TextField name="sku" label="SKU" defaultValue={product?.sku ?? ""} />
    </div>
    <div className="grid gap-4 md:grid-cols-3">
      <SelectField name="product_type" label="Type" defaultValue={product?.product_type ?? "simple"} values={["simple", "variable", "bundle"]} />
      <SelectField name="status" label="Status" defaultValue={product?.status ?? "draft"} values={["draft", "published", "archived"]} />
      <SelectField name="visibility" label="Visibility" defaultValue={product?.visibility ?? "public"} values={["public", "hidden", "private"]} />
    </div>
    <div className="grid gap-4 md:grid-cols-3">
      <TextField name="base_price" label="Base price" defaultValue={product?.base_price ?? ""} />
      <TextField name="compare_at_price" label="Compare price" defaultValue={product?.compare_at_price ?? ""} />
      <TextField name="cost_price" label="Cost price" defaultValue={product?.cost_price ?? ""} />
    </div>
    <TextField name="currency" label="Currency" defaultValue={product?.currency ?? "EGP"} />
    <TextAreaField name="short_description_en" label="Short description" defaultValue={product?.short_description_en ?? ""} />
    <TextAreaField name="description_en" label="Full description" defaultValue={product?.description_en ?? ""} />
    <TextAreaField name="specs_json" label="Specs JSON" defaultValue={JSON.stringify(product?.specs_json ?? {}, null, 2)} />
    <div className="flex gap-6 text-sm">
      <label><input name="is_featured" type="checkbox" defaultChecked={product?.is_featured ?? false} /> Featured</label>
      <label><input name="is_taxable" type="checkbox" defaultChecked={product?.is_taxable ?? true} /> Taxable</label>
    </div>
    <div className="grid gap-4 md:grid-cols-2">
      <TextField name="min_order_quantity" label="Min order quantity" defaultValue={product?.min_order_quantity ?? 1} />
      <TextField name="max_order_quantity" label="Max order quantity" defaultValue={product?.max_order_quantity ?? ""} />
    </div>
  </CatalogForm>;
}

export function PriceListForm({ priceList }: { priceList?: PriceList }) {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const payload: PriceListPayload = {
      business_unit_id: Number(form.get("business_unit_id")),
      name: text(form, "name"),
      key: text(form, "key"),
      type: text(form, "type") as PriceListType,
      description: nullableText(form, "description"),
      is_default: bool(form, "is_default"),
      is_active: bool(form, "is_active"),
    };
    try {
      await (priceList ? updatePriceList(priceList.id, payload) : createPriceList(payload));
      router.push("/dashboard/catalog/price-lists");
      router.refresh();
    } catch {
      setError("Could not save price list.");
    }
  }

  return <CatalogForm onSubmit={onSubmit} error={error} submit="Save price list">
    <BasicScopeFields item={priceList} />
    <TextField name="name" label="Name" required defaultValue={priceList?.name} />
    <TextField name="key" label="Key" required defaultValue={priceList?.key} />
    <SelectField name="type" label="Type" defaultValue={priceList?.type ?? "retail"} values={["retail", "wholesale", "distributor", "special"]} />
    <TextAreaField name="description" label="Description" defaultValue={priceList?.description ?? ""} />
    <div className="flex gap-6 text-sm">
      <label><input name="is_default" type="checkbox" defaultChecked={priceList?.is_default ?? false} /> Default</label>
      <label><input name="is_active" type="checkbox" defaultChecked={priceList?.is_active ?? true} /> Active</label>
    </div>
  </CatalogForm>;
}

function BasicScopeFields({ item }: { item?: { business_unit_id?: number } }) {
  return <TextField name="business_unit_id" label="Business unit ID" required defaultValue={item?.business_unit_id ?? ""} />;
}

function CatalogForm({ children, error, submit, onSubmit }: { children: React.ReactNode; error: string | null; submit: string; onSubmit: (event: FormEvent<HTMLFormElement>) => void }) {
  return <form onSubmit={onSubmit} className="grid gap-4 rounded-md border border-slate-200 bg-white p-5">
    {error ? <p className="text-sm text-red-600">{error}</p> : null}
    {children}
    <button className="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white">{submit}</button>
  </form>;
}

function TextField({ label, ...props }: React.InputHTMLAttributes<HTMLInputElement> & { label: string }) {
  return <label className="grid gap-1 text-sm">{label}<input {...props} className="rounded-md border border-slate-300 px-3 py-2" /></label>;
}

function TextAreaField({ label, ...props }: React.TextareaHTMLAttributes<HTMLTextAreaElement> & { label: string }) {
  return <label className="grid gap-1 text-sm">{label}<textarea {...props} className="min-h-24 rounded-md border border-slate-300 px-3 py-2" /></label>;
}

function SelectField({ label, values, ...props }: React.SelectHTMLAttributes<HTMLSelectElement> & { label: string; values: string[] }) {
  return <label className="grid gap-1 text-sm">{label}<select {...props} className="rounded-md border border-slate-300 px-3 py-2">{values.map((value) => <option key={value} value={value}>{value}</option>)}</select></label>;
}
