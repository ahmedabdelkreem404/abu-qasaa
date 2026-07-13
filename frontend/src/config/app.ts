export const appConfig = {
  name: process.env.NEXT_PUBLIC_APP_NAME ?? "Abnaa Abu Qasaa Trading",
  apiUrl:
    process.env.NEXT_PUBLIC_API_URL ??
    process.env.NEXT_PUBLIC_API_BASE_URL ??
    "http://localhost:8000/api/v1",
};
