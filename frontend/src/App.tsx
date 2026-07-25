import { useHealth } from "./hooks/useHealth";

function App() {
  const { status, data } = useHealth();

  const config = {
    loading: { label: "Verificando...", dot: "bg-yellow-400", text: "text-yellow-400" },
    online:  { label: "API Online",     dot: "bg-green-500",  text: "text-green-500" },
    offline: { label: "API Offline",    dot: "bg-red-500",    text: "text-red-500" },
  }[status];

  return (
    <div className="min-h-screen bg-neutral-950 text-neutral-100 flex flex-col items-center justify-center gap-8 p-6">
      <div className="flex items-center gap-3">
        <span className={`h-4 w-4 rounded-full ${config.dot} animate-pulse`} />
        <h1 className={`text-4xl font-semibold ${config.text}`}>{config.label}</h1>
      </div>

      {data && (
        <div className="w-full max-w-md rounded-xl border border-neutral-800 bg-neutral-900 overflow-hidden">
          {Object.entries(data).map(([key, value]) => (
            <div
              key={key}
              className="flex justify-between px-5 py-3 border-b border-neutral-800 last:border-b-0"
            >
              <span className="text-neutral-500 capitalize">{key}</span>
              <span className="font-mono text-sm text-neutral-200">{String(value)}</span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

export default App;