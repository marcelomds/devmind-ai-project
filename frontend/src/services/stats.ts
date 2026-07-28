import { apiGet } from "./api";
import type { ScorePoint, SeverityCount, Stats } from "../types/stats";

interface RawSummary {
  current_score: number | null;
  score_delta: number | null;
  total_analyses: number;
  open_findings: number;
  critical_count: number;
  critical_delta: number | null;
}

interface RawStats {
  summary: RawSummary;
  score_over_time: ScorePoint[];
  findings_by_severity: SeverityCount[];
}

function mapStats(raw: RawStats): Stats {
  return {
    summary: {
      currentScore: raw.summary.current_score,
      scoreDelta: raw.summary.score_delta,
      totalAnalyses: raw.summary.total_analyses,
      openFindings: raw.summary.open_findings,
      criticalCount: raw.summary.critical_count,
      criticalDelta: raw.summary.critical_delta,
    },
    scoreOverTime: raw.score_over_time,
    findingsBySeverity: raw.findings_by_severity,
  };
}

export async function getStats(days?: number): Promise<Stats> {
  const path = days ? `/stats?days=${days}` : "/stats";
  const response = await apiGet<{ data: RawStats }>(path);

  return mapStats(response.data);
}
