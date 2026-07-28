import { formatLocation } from "../analysis/formatLocation";
import { severityStyles } from "../analysis/severity";
import { usePullRequestAnalyses } from "../../hooks/usePullRequestAnalyses";
import type { Analysis, AnalysisStatus } from "../../types/analysis";

const STATUS_STYLES: Record<AnalysisStatus, string> = {
  pending: "bg-neutral-800 text-neutral-400",
  processing: "bg-indigo-500/15 text-indigo-300",
  completed: "bg-green-950 text-green-400",
  failed: "bg-red-950 text-red-400",
};

function shortSha(sha: string | null) {
  return sha ? sha.slice(0, 7) : null;
}

export function PullRequestAnalysesScreen() {
  const { analyses, loading, error, refresh, expandedUuid, toggleExpand } = usePullRequestAnalyses();

  return (
    <main className="mx-auto flex max-w-3xl flex-col gap-8 px-6 py-16">
      <header className="flex items-start justify-between gap-4">
        <div>
          <p className="text-sm font-medium uppercase tracking-wider text-indigo-400">DevMind AI</p>
          <h1 className="mt-1 text-3xl font-semibold">Pull request analyses</h1>
          <p className="mt-2 text-sm text-neutral-500">
            Analyses triggered by the GitHub webhook for connected repositories.
          </p>
        </div>

        <button
          onClick={refresh}
          className="rounded-lg border border-neutral-700 px-3 py-1.5 text-xs font-medium text-neutral-200 transition hover:border-neutral-500"
        >
          Refresh
        </button>
      </header>

      {error && (
        <div className="rounded-lg border border-red-900 bg-red-950/40 px-4 py-3 text-sm text-red-300">
          {error}
        </div>
      )}

      {loading ? (
        <p className="text-sm text-neutral-500">Loading…</p>
      ) : analyses.length === 0 ? (
        <p className="text-sm text-neutral-500">
          No pull request analyses yet. Open or update a PR on a connected repository to trigger one.
        </p>
      ) : (
        <ul className="flex flex-col gap-3">
          {analyses.map((analysis) => (
            <AnalysisRow
              key={analysis.uuid}
              analysis={analysis}
              expanded={expandedUuid === analysis.uuid}
              onToggle={() => toggleExpand(analysis.uuid)}
            />
          ))}
        </ul>
      )}
    </main>
  );
}

function AnalysisRow({
  analysis,
  expanded,
  onToggle,
}: {
  analysis: Analysis;
  expanded: boolean;
  onToggle: () => void;
}) {
  return (
    <li className="overflow-hidden rounded-lg border border-neutral-800 bg-neutral-900">
      <button onClick={onToggle} className="flex w-full items-center justify-between gap-4 p-4 text-left">
        <div className="flex min-w-0 items-start gap-3">
          {analysis.prAuthorAvatarUrl && (
            <img
              src={analysis.prAuthorAvatarUrl}
              alt={analysis.prAuthorLogin ?? "PR author"}
              className="mt-0.5 h-9 w-9 shrink-0 rounded-full"
            />
          )}
          <div className="min-w-0">
            <p className="font-mono text-sm text-neutral-100">
              {analysis.repositoryFullName ?? "unknown repository"}
              {analysis.prNumber && <span className="text-neutral-500"> #{analysis.prNumber}</span>}
            </p>
            {analysis.prTitle && <p className="mt-0.5 truncate text-sm text-neutral-300">{analysis.prTitle}</p>}
            <p className="mt-1 text-xs text-neutral-500">
              {analysis.prAuthorLogin && <>{analysis.prAuthorLogin} · </>}
              {shortSha(analysis.commitSha)} · {new Date(analysis.createdAt).toLocaleString()}
            </p>
          </div>
        </div>

        <div className="flex items-center gap-3 text-sm text-neutral-400">
          {analysis.score !== null && (
            <span className="rounded-full bg-neutral-800 px-2.5 py-1 font-mono text-xs text-neutral-200">
              Score {analysis.score}
            </span>
          )}
          <span className={`rounded-full px-2.5 py-1 text-xs font-medium ${STATUS_STYLES[analysis.status]}`}>
            {analysis.status}
          </span>
        </div>
      </button>

      {expanded && (
        <div className="border-t border-neutral-800 p-4">
          {analysis.status === "failed" && (
            <p className="text-sm text-red-300">{analysis.errorMessage ?? "Analysis failed."}</p>
          )}

          {analysis.summary && <p className="text-sm text-neutral-400">{analysis.summary}</p>}

          {analysis.status === "completed" && analysis.findings.length === 0 && (
            <p className="text-sm text-neutral-500">No findings. Clean code.</p>
          )}

          {analysis.findings.length > 0 && (
            <ul className="mt-3 flex flex-col gap-3">
              {analysis.findings.map((finding) => {
                const styles = severityStyles[finding.severity];
                const location = formatLocation(finding.filePath, finding.lineStart, finding.lineEnd);

                return (
                  <li
                    key={finding.uuid}
                    className="flex overflow-hidden rounded-lg border border-neutral-800 bg-neutral-950"
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
        </div>
      )}
    </li>
  );
}
