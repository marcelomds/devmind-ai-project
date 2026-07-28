import {
  Bar,
  BarChart,
  CartesianGrid,
  Cell,
  Line,
  LineChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";
import { useStats } from "../../hooks/useStats";
import { severityStyles } from "../analysis/severity";

const DAYS = 30;

interface MetricCardProps {
  label: string;
  value: string | number;
  delta?: number | null;
  // Whether a positive delta is good news (score) or bad news (critical count).
  deltaGoodDirection?: "up" | "down";
}

function MetricCard({ label, value, delta, deltaGoodDirection = "up" }: MetricCardProps) {
  const hasDelta = delta !== undefined && delta !== null && delta !== 0;
  const isPositive = (delta ?? 0) > 0;
  const isGood = deltaGoodDirection === "up" ? isPositive : !isPositive;

  return (
    <div className="rounded-xl border border-neutral-800 bg-neutral-900 p-5">
      <p className="text-xs font-medium uppercase tracking-wider text-neutral-500">{label}</p>
      <div className="mt-2 flex items-baseline gap-2">
        <span className="text-2xl font-semibold text-neutral-100">{value}</span>
        {hasDelta && (
          <span className={`text-xs font-medium ${isGood ? "text-green-400" : "text-red-400"}`}>
            {isPositive ? "▲" : "▼"} {Math.abs(delta ?? 0)}
          </span>
        )}
      </div>
    </div>
  );
}

export function DashboardScreen() {
  const { stats, loading, error } = useStats(DAYS);

  return (
    <main className="mx-auto flex max-w-5xl flex-col gap-8 px-6 py-16">
      <header>
        <p className="text-sm font-medium uppercase tracking-wider text-indigo-400">DevMind AI</p>
        <h1 className="mt-1 text-3xl font-semibold">Code health</h1>
      </header>

      {loading && (
        <div className="flex items-center gap-3 text-sm text-neutral-400">
          <span className="h-4 w-4 animate-spin rounded-full border-2 border-neutral-700 border-t-indigo-400" />
          Loading dashboard…
        </div>
      )}

      {!loading && (error || !stats) && (
        <div className="rounded-lg border border-red-900 bg-red-950/40 px-4 py-3 text-sm text-red-300">
          {error ?? "Could not load dashboard stats."}
        </div>
      )}

      {!loading && stats && stats.summary.totalAnalyses === 0 && (
        <p className="text-sm text-neutral-500">Run a few analyses to see trends.</p>
      )}

      {!loading && stats && stats.summary.totalAnalyses > 0 && (
        <>
          <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <MetricCard
              label="Current score"
              value={stats.summary.currentScore ?? "—"}
              delta={stats.summary.scoreDelta}
            />
            <MetricCard label="Total analyses" value={stats.summary.totalAnalyses} />
            <MetricCard label="Open findings" value={stats.summary.openFindings} />
            <MetricCard
              label="Critical findings"
              value={stats.summary.criticalCount}
              delta={stats.summary.criticalDelta}
              deltaGoodDirection="down"
            />
          </div>

          <section className="rounded-xl border border-neutral-800 bg-neutral-900 p-5">
            <h2 className="text-sm font-medium text-neutral-300">Score over time</h2>
            <div className="mt-4 h-64">
              <ResponsiveContainer width="100%" height="100%">
                <LineChart data={stats.scoreOverTime}>
                  <CartesianGrid strokeDasharray="3 3" stroke="#262626" />
                  <XAxis dataKey="date" stroke="#737373" fontSize={12} />
                  <YAxis domain={[0, 100]} stroke="#737373" fontSize={12} />
                  <Tooltip contentStyle={{ background: "#171717", border: "1px solid #262626" }} />
                  <Line type="monotone" dataKey="score" stroke="#6366f1" strokeWidth={2} dot={{ r: 3 }} />
                </LineChart>
              </ResponsiveContainer>
            </div>
          </section>

          <section className="rounded-xl border border-neutral-800 bg-neutral-900 p-5">
            <h2 className="text-sm font-medium text-neutral-300">Findings by severity</h2>
            <div className="mt-4 h-64">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={stats.findingsBySeverity}>
                  <CartesianGrid strokeDasharray="3 3" stroke="#262626" />
                  <XAxis dataKey="severity" stroke="#737373" fontSize={12} />
                  <YAxis allowDecimals={false} stroke="#737373" fontSize={12} />
                  <Tooltip contentStyle={{ background: "#171717", border: "1px solid #262626" }} />
                  <Bar dataKey="count">
                    {stats.findingsBySeverity.map((entry) => (
                      <Cell key={entry.severity} fill={severityStyles[entry.severity].chartColor} />
                    ))}
                  </Bar>
                </BarChart>
              </ResponsiveContainer>
            </div>
          </section>
        </>
      )}
    </main>
  );
}
