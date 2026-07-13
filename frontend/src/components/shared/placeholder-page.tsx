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
        <p className="aq-eyebrow">
          Foundation
        </p>
        <h1 className="aq-title">
          {title}
        </h1>
        <p className="aq-subtitle max-w-3xl">
          {description}
        </p>
      </div>
      {items.length > 0 ? (
        <div className="aq-grid-auto">
          {items.map((item) => (
            <div
              key={item}
              className="aq-card-muted p-4 text-sm font-semibold text-[var(--aq-ink-2)]"
            >
              {item}
            </div>
          ))}
        </div>
      ) : null}
    </section>
  );
}
