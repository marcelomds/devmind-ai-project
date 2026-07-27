import { useCallback, useEffect, useState } from "react";
import { getAnalysis, listAnalyses } from "../services/analysis";
import type { Analysis } from "../types/analysis";

export function usePullRequestAnalyses() {
  const [analyses, setAnalyses] = useState<Analysis[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [expandedUuid, setExpandedUuid] = useState<string | null>(null);

  const refresh = useCallback(async () => {
    setLoading(true);
    setError(null);

    try {
      setAnalyses(await listAnalyses("pull_request"));
    } catch {
      setError("Could not load analyses.");
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    let cancelled = false;

    listAnalyses("pull_request")
      .then((data) => {
        if (!cancelled) setAnalyses(data);
      })
      .catch(() => {
        if (!cancelled) setError("Could not load analyses.");
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, []);

  const toggleExpand = useCallback(
    async (uuid: string) => {
      if (expandedUuid === uuid) {
        setExpandedUuid(null);
        return;
      }

      setExpandedUuid(uuid);

      // Findings aren't included in the list response — fetch the full
      // analysis on demand the first time a row is expanded.
      const current = analyses.find((a) => a.uuid === uuid);
      if (current && current.findings.length === 0 && current.status === "completed") {
        try {
          const full = await getAnalysis(uuid);
          setAnalyses((list) => list.map((a) => (a.uuid === uuid ? full : a)));
        } catch {
          setError("Could not load findings for this analysis.");
        }
      }
    },
    [analyses, expandedUuid],
  );

  return { analyses, loading, error, refresh, expandedUuid, toggleExpand };
}
