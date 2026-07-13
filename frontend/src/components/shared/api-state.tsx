export function ApiErrorState({ message }: { message: string }) {
  return (
    <div className="aq-card border-[color:rgb(160_90_0_/_0.28)] bg-[color:rgb(255_249_235_/_0.95)] p-5 text-sm text-[var(--aq-warning)]">
      <p className="font-black">Action needed</p>
      <p className="mt-1 leading-7">{message}</p>
    </div>
  );
}

export function EmptyState({ message }: { message: string }) {
  return (
    <div className="aq-card-muted p-8 text-center text-sm text-[var(--aq-muted)]">
      <div className="mx-auto mb-4 h-12 w-12 rounded-full border border-[color:var(--aq-line)] bg-white" />
      <p className="font-bold">{message}</p>
    </div>
  );
}
