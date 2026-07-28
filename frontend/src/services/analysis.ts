import { apiGet, apiPost } from "./api";
import type { Analysis, AnalysisSource, AnalyzerType, Finding, Language } from "../types/analysis";

// The API (AnalysisResource/FindingResource) returns snake_case fields and
// wraps the payload in a top-level "data" key. These Raw* shapes mirror the
// wire format exactly; mapAnalysis()/mapFinding() translate them into the
// camelCase types the rest of the app uses.
interface RawFinding {
  uuid: string;
  severity: Finding["severity"];
  category: string;
  title: string;
  message: string;
  suggestion: string | null;
  file_path: string | null;
  line_start: number | null;
  line_end: number | null;
}

interface RawAnalysis {
  uuid: string;
  analyzer: AnalyzerType;
  status: Analysis["status"];
  source_type: AnalysisSource;
  pr_number: number | null;
  pr_title: string | null;
  commit_sha: string | null;
  repository_full_name: string | null;
  pr_author_login: string | null;
  pr_author_avatar_url: string | null;
  score: number | null;
  summary: string | null;
  error_message: string | null;
  created_at: string;
  findings?: RawFinding[];
}

function mapFinding(raw: RawFinding): Finding {
  return {
    uuid: raw.uuid,
    severity: raw.severity,
    category: raw.category,
    title: raw.title,
    message: raw.message,
    suggestion: raw.suggestion,
    filePath: raw.file_path,
    lineStart: raw.line_start,
    lineEnd: raw.line_end,
  };
}

function mapAnalysis(raw: RawAnalysis): Analysis {
  return {
    uuid: raw.uuid,
    analyzer: raw.analyzer,
    status: raw.status,
    sourceType: raw.source_type,
    prNumber: raw.pr_number,
    prTitle: raw.pr_title,
    commitSha: raw.commit_sha,
    repositoryFullName: raw.repository_full_name,
    prAuthorLogin: raw.pr_author_login,
    prAuthorAvatarUrl: raw.pr_author_avatar_url,
    score: raw.score,
    summary: raw.summary,
    errorMessage: raw.error_message,
    createdAt: raw.created_at,
    findings: (raw.findings ?? []).map(mapFinding),
  };
}

export async function createAnalysis(input: {
  code: string;
  analyzer: AnalyzerType;
  language: Language;
}): Promise<Analysis> {
  const response = await apiPost<{ data: RawAnalysis }>("/analyses", {
    input_code: input.code,
    analyzer: input.analyzer,
    language: input.language,
  });

  return mapAnalysis(response.data);
}

export async function getAnalysis(uuid: string): Promise<Analysis> {
  const response = await apiGet<{ data: RawAnalysis }>(`/analyses/${uuid}`);

  return mapAnalysis(response.data);
}

export async function listAnalyses(sourceType: AnalysisSource): Promise<Analysis[]> {
  const params = new URLSearchParams({ source_type: sourceType });
  const response = await apiGet<{ data: RawAnalysis[] }>(`/analyses?${params}`);

  return response.data.map(mapAnalysis);
}
