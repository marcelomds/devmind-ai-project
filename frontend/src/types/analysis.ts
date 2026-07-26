export type Severity = "critical" | "high" | "medium" | "low" | "info";

export type AnalysisStatus = "pending" | "processing" | "completed" | "failed";

export type AnalyzerType = "quality" | "docs";

export type Language = "en" | "pt-BR";

export interface Finding {
  uuid: string;
  severity: Severity;
  category: string;
  title: string;
  message: string;
  suggestion: string | null;
  filePath: string | null;
  lineStart: number | null;
  lineEnd: number | null;
}

export interface Analysis {
  uuid: string;
  analyzer: AnalyzerType;
  status: AnalysisStatus;
  score: number | null;
  summary: string | null;
  findings: Finding[];
}
