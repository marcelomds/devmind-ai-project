import type { Severity } from "./analysis";

export interface SummaryStats {
  currentScore: number | null;
  scoreDelta: number | null;
  totalAnalyses: number;
  openFindings: number;
  criticalCount: number;
  criticalDelta: number | null;
}

export interface ScorePoint {
  date: string;
  score: number | null;
}

export interface SeverityCount {
  severity: Severity;
  count: number;
}

export interface Stats {
  summary: SummaryStats;
  scoreOverTime: ScorePoint[];
  findingsBySeverity: SeverityCount[];
}
