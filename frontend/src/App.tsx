import { useHealth } from "./hooks/useHealth";
import { AnalysisScreen } from "./features/analysis/AnalysisScreen";

function App() {
  const { status } = useHealth();

  const config = {
    loading: { label: "Checking...", dot: "bg-yellow-400" },
    online: { label: "API online", dot: "bg-green-500" },
    offline: { label: "API offline", dot: "bg-red-500" },
  }[status];

  return (
    <div className="min-h-screen bg-neutral-950 text-neutral-100">
      <div className="fixed top-4 right-4 z-50 flex items-center gap-2 rounded-full border border-neutral-800 bg-neutral-900/80 px-3 py-1.5 text-xs backdrop-blur">
        <span className={`h-2 w-2 rounded-full ${config.dot} animate-pulse`} />
        <span className="text-neutral-400">{config.label}</span>
      </div>

      <AnalysisScreen />
    </div>
  );
}

export default App;
