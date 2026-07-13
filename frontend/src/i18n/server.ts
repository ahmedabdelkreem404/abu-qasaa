import { cookies } from "next/headers";
import { dictionaries, pickLocale, type Dictionary, type Locale } from ".";

export async function getLocale(): Promise<Locale> {
  const store = await cookies();
  return pickLocale(store.get("abu_qasaa_locale")?.value);
}

export async function getDictionary(): Promise<Dictionary> {
  return dictionaries[await getLocale()];
}
