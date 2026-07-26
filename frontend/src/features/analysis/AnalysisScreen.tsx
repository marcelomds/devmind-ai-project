import { useState } from "react";
import type { FormEvent } from "react";
import { useAnalysis } from "../../hooks/useAnalysis";
import type { AnalyzerType } from "../../types/analysis";
import { severityStyles } from "./severity";

// Findings are generated in English for now; no language picker in the UI.
const LANGUAGE = "en";

const ANALYZERS: { value: AnalyzerType; label: string }[] = [
  { value: "quality", label: "Quality" },
  { value: "docs", label: "Docs" },
];

function formatLocation(filePath: string | null, lineStart: number | null, lineEnd: number | null) {
  if (!filePath) return null;
  if (!lineStart) return filePath;
  if (lineEnd && lineEnd !== lineStart) return `${filePath}:${lineStart}-${lineEnd}`;
  return `${filePath}:${lineStart}`;
}

export function AnalysisScreen() {
  const [code, setCode] = useState("");
  const [analyzer, setAnalyzer] = useState<AnalyzerType>("quality");
  const { analysis, status, error, run } = useAnalysis();

  const isBusy = status === "submitting" || status === "polling";
  const canSubmit = code.trim().length > 0 && !isBusy;

  function handleSubmit(event: FormEvent) {
    event.preventDefault();
    if (!canSubmit) return;
    run(code, analyzer, LANGUAGE);
  }

  return (
    <main className="mx-auto flex max-w-3xl flex-col gap-8 px-6 py-16">
      <header>
        <p className="text-sm font-medium uppercase tracking-wider text-indigo-400">DevMind AI</p>
        <h1 className="mt-1 text-3xl font-semibold">New analysis</h1>
      </header>

      <form
        onSubmit={handleSubmit}
        className="flex flex-col gap-4 rounded-xl border border-neutral-800 bg-neutral-900 p-5"
      >
        <textarea
          value={code}
          onChange={(event) => setCode(event.target.value)}
          placeholder="Paste the code you want reviewed..."
          rows={12}
          className="w-full resize-y rounded-lg border border-neutral-800 bg-neutral-950 p-4 font-mono text-sm text-neutral-100 placeholder:text-neutral-600 focus:border-indigo-500 focus:outline-none"
        />

        <div className="flex items-center justify-between gap-4">
          <select
            value={analyzer}
            onChange={(event) => setAnalyzer(event.target.value as AnalyzerType)}
            disabled={isBusy}
            className="rounded-lg border border-neutral-800 bg-neutral-950 px-3 py-2 text-sm text-neutral-200 focus:border-indigo-500 focus:outline-none disabled:opacity-50"
          >
            {ANALYZERS.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>

          <button
            type="submit"
            disabled={!canSubmit}
            className="rounded-lg bg-indigo-500 px-5 py-2 text-sm font-medium text-white transition hover:bg-indigo-400 disabled:cursor-not-allowed disabled:opacity-40"
          >
            Analyze
          </button>
        </div>
      </form>

      {isBusy && (
        <div className="flex items-center gap-3 text-sm text-neutral-400">
          <span className="h-4 w-4 animate-spin rounded-full border-2 border-neutral-700 border-t-indigo-400" />
          Analyzing…
        </div>
      )}

      {status === "error" && (
        <div className="rounded-lg border border-red-900 bg-red-950/40 px-4 py-3 text-sm text-red-300">
          {error ?? "Something went wrong. Please try again."}
        </div>
      )}

      {status === "done" && analysis && (
        <section className="flex flex-col gap-4">
          <div className="flex items-center justify-between">
            <h2 className="text-lg font-semibold">Findings</h2>
            <div className="flex items-center gap-3 text-sm text-neutral-400">
              <span className="capitalize">{analysis.status}</span>
              {analysis.score !== null && (
                <span className="rounded-full bg-neutral-800 px-2.5 py-1 font-mono text-xs text-neutral-200">
                  Score {analysis.score}
                </span>
              )}
            </div>
          </div>

          {analysis.summary && <p className="text-sm text-neutral-400">{analysis.summary}</p>}

          {analysis.findings.length === 0 ? (
            <p className="text-sm text-neutral-500">No findings. Clean code.</p>
          ) : (
            <ul className="flex flex-col gap-3">
              {analysis.findings.map((finding) => {
                const styles = severityStyles[finding.severity];
                const location = formatLocation(finding.filePath, finding.lineStart, finding.lineEnd);

                return (
                  <li
                    key={finding.uuid}
                    className="flex overflow-hidden rounded-lg border border-neutral-800 bg-neutral-900"
                  >
                    <span className={`w-1.5 shrink-0 ${styles.bar}`} />
                    <div className="flex-1 p-4">
                      <div className="flex flex-wrap items-center gap-2 text-xs text-neutral-500">
                        <span className={`rounded-full px-2 py-0.5 font-medium ${styles.badge}`}>
                          {styles.label}
                        </span>
                        <span>{finding.category}</span>
                        {location && <span className="font-mono">{location}</span>}
                      </div>
                      <h3 className="mt-2 font-semibold text-neutral-100">{finding.title}</h3>
                      <p className="mt-1 text-sm text-neutral-400">{finding.message}</p>
                      {finding.suggestion && (
                        <p className="mt-2 text-sm text-indigo-300">Suggestion: {finding.suggestion}</p>
                      )}
                    </div>
                  </li>
                );
              })}
            </ul>
          )}
        </section>
      )}

      {status === "idle" && (
        <p className="text-sm text-neutral-500">
          Paste some code above and pick an analyzer to get started.
        </p>
      )}
    </main>
  );
}
