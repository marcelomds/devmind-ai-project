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

export type AnalysisSource = "manual" | "pull_request";

export interface Analysis {
  uuid: string;
  analyzer: AnalyzerType;
  status: AnalysisStatus;
  sourceType: AnalysisSource;
  prNumber: number | null;
  prTitle: string | null;
  commitSha: string | null;
  repositoryFullName: string | null;
  prAuthorLogin: string | null;
  prAuthorAvatarUrl: string | null;
  score: number | null;
  summary: string | null;
  errorMessage: string | null;
  createdAt: string;
  findings: Finding[];
}
