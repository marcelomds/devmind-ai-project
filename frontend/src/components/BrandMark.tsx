export function BrandMark() {
  return (
    <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 text-white shadow-lg shadow-indigo-950/50">
      <svg viewBox="0 0 24 24" fill="none" className="h-6 w-6" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round">
        <path d="M12 12 L6 7 M12 12 L18 7 M12 12 L6 17 M12 12 L18 17" />
        <circle cx="12" cy="12" r="1.6" fill="currentColor" stroke="none" />
        <circle cx="6" cy="7" r="1.4" fill="currentColor" stroke="none" />
        <circle cx="18" cy="7" r="1.4" fill="currentColor" stroke="none" />
        <circle cx="6" cy="17" r="1.4" fill="currentColor" stroke="none" />
        <circle cx="18" cy="17" r="1.4" fill="currentColor" stroke="none" />
      </svg>
    </div>
  );
}
