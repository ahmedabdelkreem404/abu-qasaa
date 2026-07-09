export type BusinessUnit = {
  id: number;
  name: string;
  slug: string;
  type: string;
  status: string;
};

export type ActivityTemplate = {
  id: number;
  name: string;
  key: string;
  defaultModules: string[];
};

export type ActivityModule = {
  id: number;
  name: string;
  key: string;
  capabilities: string[];
};

export type Product = {
  id: number;
  businessUnitId?: number;
  name: string;
  slug: string;
  sku?: string;
  status: string;
};

export type Order = {
  id: number;
  businessUnitId: number;
  number: string;
  status: string;
  total: number;
  currency: string;
};

export type Payment = {
  id: number;
  businessUnitId: number;
  provider: string;
  status: string;
  amount: number;
  currency: string;
};

export type Lead = {
  id: number;
  businessUnitId: number;
  name: string;
  email?: string;
  phone?: string;
  status: string;
};
