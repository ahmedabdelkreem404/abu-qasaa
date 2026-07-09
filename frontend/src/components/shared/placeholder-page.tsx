type PlaceholderPageProps = {
  title: string;
  description: string;
  items?: string[];
};

export function PlaceholderPage({
  title,
  description,
  items = [],
}: PlaceholderPageProps) {
  return (
    <section className="space-y-6">
      <div className="space-y-2">
        <p className="text-sm font-medium uppercase tracking-wide text-teal-700">
          Foundation
        </p>
        <h1 className="text-3xl font-semibold tracking-normal text-slate-950">
          {title}
        </h1>
        <p className="max-w-3xl text-base leading-7 text-slate-600">
          {description}
        </p>
      </div>
      {items.length > 0 ? (
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          {items.map((item) => (
            <div
              key={item}
              className="rounded-md border border-slate-200 bg-white p-4 text-sm text-slate-700"
            >
              {item}
            </div>
          ))}
        </div>
      ) : null}
    </section>
  );
}
