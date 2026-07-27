import { useState } from "react";
import { useHealth } from "./hooks/useHealth";
import { AnalysisScreen } from "./features/analysis/AnalysisScreen";
import { RepositoriesScreen } from "./features/repositories/RepositoriesScreen";
import { PullRequestAnalysesScreen } from "./features/analyses/PullRequestAnalysesScreen";

type Tab = "analyze" | "repositories" | "pr-analyses";

const TABS: { value: Tab; label: string }[] = [
  { value: "analyze", label: "Analyze" },
  { value: "repositories", label: "Repositories" },
  { value: "pr-analyses", label: "PR Analyses" },
];

function App() {
  const { status } = useHealth();
  const [tab, setTab] = useState<Tab>("analyze");

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

      <nav className="fixed top-4 left-6 z-50 flex items-center gap-1 rounded-full border border-neutral-800 bg-neutral-900/80 p-1 text-xs backdrop-blur">
        {TABS.map((option) => (
          <button
            key={option.value}
            onClick={() => setTab(option.value)}
            className={`rounded-full px-3 py-1.5 font-medium transition ${
              tab === option.value
                ? "bg-indigo-500 text-white"
                : "text-neutral-400 hover:text-neutral-200"
            }`}
          >
            {option.label}
          </button>
        ))}
      </nav>

      {tab === "analyze" && <AnalysisScreen />}
      {tab === "repositories" && <RepositoriesScreen />}
      {tab === "pr-analyses" && <PullRequestAnalysesScreen />}
    </div>
  );
}

export default App;
