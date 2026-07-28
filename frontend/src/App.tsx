import { useState } from "react";
import { useHealth } from "./hooks/useHealth";
import { useAuth } from "./hooks/useAuth";
import { AnalysisScreen } from "./features/analysis/AnalysisScreen";
import { RepositoriesScreen } from "./features/repositories/RepositoriesScreen";
import { PullRequestAnalysesScreen } from "./features/analyses/PullRequestAnalysesScreen";
import { DashboardScreen } from "./features/dashboard/DashboardScreen";
import { LoginScreen } from "./features/auth/LoginScreen";
import { RegisterScreen } from "./features/auth/RegisterScreen";

type Tab = "analyze" | "repositories" | "pr-analyses" | "dashboard";

const TABS: { value: Tab; label: string }[] = [
  { value: "dashboard", label: "Dashboard" },
  { value: "repositories", label: "Repositories" },
  { value: "pr-analyses", label: "PR Analyses" },
  { value: "analyze", label: "Manual Analyze" },
];

function App() {
  const { status } = useHealth();
  const { user, loading, logout } = useAuth();
  const [tab, setTab] = useState<Tab>("dashboard");
  const [authMode, setAuthMode] = useState<"login" | "register">("login");

  const config = {
    loading: { label: "Checking...", dot: "bg-yellow-400" },
    online: { label: "API online", dot: "bg-green-500" },
    offline: { label: "API offline", dot: "bg-red-500" },
  }[status];

  if (loading) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-neutral-950 text-neutral-100">
        <span className="h-6 w-6 animate-spin rounded-full border-2 border-neutral-700 border-t-indigo-400" />
      </div>
    );
  }

  if (!user) {
    return (
      <div className="min-h-screen bg-neutral-950 text-neutral-100">
        {authMode === "login" ? (
          <LoginScreen onSwitchToRegister={() => setAuthMode("register")} />
        ) : (
          <RegisterScreen onSwitchToLogin={() => setAuthMode("login")} />
        )}
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-neutral-950 text-neutral-100">
      <header className="fixed inset-x-0 top-0 z-50 flex items-center justify-between gap-4 border-b border-neutral-800 bg-neutral-900/80 px-6 py-3 backdrop-blur">
        <div className="flex items-center gap-6">
          <span className="text-sm font-semibold tracking-wide text-indigo-400">DevMind AI</span>

          <nav className="flex items-center gap-1 rounded-full border border-neutral-800 bg-neutral-950/60 p-1 text-xs">
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
        </div>

        <div className="flex items-center gap-2 text-xs">
          <div className="flex items-center gap-2 rounded-full border border-neutral-800 bg-neutral-950/60 px-3 py-1.5">
            <span className={`h-2 w-2 rounded-full ${config.dot} animate-pulse`} />
            <span className="text-neutral-400">{config.label}</span>
          </div>

          <div className="flex items-center gap-2 rounded-full border border-neutral-800 bg-neutral-950/60 py-1.5 pr-1.5 pl-3">
            <span className="text-neutral-400">{user.name}</span>
            <button
              onClick={() => logout()}
              className="rounded-full border border-neutral-700 px-2.5 py-1 font-medium text-neutral-300 transition hover:border-neutral-500"
            >
              Log out
            </button>
          </div>
        </div>
      </header>

      {tab === "analyze" && <AnalysisScreen />}
      {tab === "repositories" && <RepositoriesScreen />}
      {tab === "pr-analyses" && <PullRequestAnalysesScreen />}
      {tab === "dashboard" && <DashboardScreen />}
    </div>
  );
}

export default App;
