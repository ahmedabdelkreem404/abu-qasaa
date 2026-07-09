export function ApiErrorState({ message }: { message: string }) {
  return (
    <div className="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
      {message}
    </div>
  );
}

export function EmptyState({ message }: { message: string }) {
  return (
    <div className="rounded-md border border-slate-200 bg-white p-6 text-sm text-slate-600">
      {message}
    </div>
  );
}
