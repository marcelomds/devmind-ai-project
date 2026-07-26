import { useCallback, useEffect, useRef, useState } from "react";
import { createAnalysis, getAnalysis } from "../services/analysis";
import type { Analysis, AnalyzerType, Language } from "../types/analysis";

type Status = "idle" | "submitting" | "polling" | "done" | "error";

const POLL_INTERVAL_MS = 2000;
const MAX_POLL_ATTEMPTS = 30; // ~1 minute at 2s/attempt — safety cap, never poll forever

export function useAnalysis() {
  const [analysis, setAnalysis] = useState<Analysis | null>(null);
  const [status, setStatus] = useState<Status>("idle");
  const [error, setError] = useState<string | null>(null);

  const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);
  const attemptsRef = useRef(0);

  const stopPolling = useCallback(() => {
    if (intervalRef.current !== null) {
      clearInterval(intervalRef.current);
      intervalRef.current = null;
    }
  }, []);

  // Stop polling if the component unmounts while a poll is still in flight.
  useEffect(() => stopPolling, [stopPolling]);

  const run = useCallback(
    async (code: string, analyzer: AnalyzerType, language: Language) => {
      stopPolling();
      attemptsRef.current = 0;
      setError(null);
      setStatus("submitting");

      try {
        // POST returns immediately with status "pending" — the analysis has
        // been created, but the queued job hasn't run yet, so there are no
        // findings to show. We only have the uuid to poll with at this point.
        const created = await createAnalysis({ code, analyzer, language });
        setAnalysis(created);
        setStatus("polling");

        // POLLING: since the job runs asynchronously off-request, the only
        // way to know when it's done is to keep asking. Every 2s we GET the
        // analysis again; once status is a terminal state (completed/failed)
        // we clear the interval and stop. The attempt cap prevents an
        // infinite loop if the job ever gets stuck.
        intervalRef.current = setInterval(async () => {
          attemptsRef.current += 1;

          if (attemptsRef.current > MAX_POLL_ATTEMPTS) {
            stopPolling();
            setStatus("error");
            setError("Analysis is taking too long. Please try again.");
            return;
          }

          try {
            const updated = await getAnalysis(created.uuid);
            setAnalysis(updated);

            if (updated.status === "completed") {
              stopPolling();
              setStatus("done");
            } else if (updated.status === "failed") {
              stopPolling();
              setStatus("error");
              setError("The analysis failed to complete.");
            }
            // otherwise still pending/processing — keep polling
          } catch {
            stopPolling();
            setStatus("error");
            setError("Lost connection while checking the analysis status.");
          }
        }, POLL_INTERVAL_MS);
      } catch {
        setStatus("error");
        setError("Could not start the analysis. Please try again.");
      }
    },
    [stopPolling],
  );

  return { analysis, status, error, run };
}
