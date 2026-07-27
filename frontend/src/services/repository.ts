import { apiDelete, apiGet, apiPatch, apiPost } from "./api";
import type { Repository } from "../types/repository";

// API (RepositoryResource) returns snake_case fields wrapped in "data"/"data[]".
// RawRepository mirrors the wire format; mapRepository() maps it to camelCase.
interface RawRepository {
  uuid: string;
  github_id: number;
  name: string;
  full_name: string;
  is_active: boolean;
  created_at: string;
}

function mapRepository(raw: RawRepository): Repository {
  return {
    uuid: raw.uuid,
    githubId: raw.github_id,
    name: raw.name,
    fullName: raw.full_name,
    isActive: raw.is_active,
    createdAt: raw.created_at,
  };
}

export async function listRepositories(): Promise<Repository[]> {
  const response = await apiGet<{ data: RawRepository[] }>("/repositories");

  return response.data.map(mapRepository);
}

export async function connectRepository(fullName: string): Promise<Repository> {
  const response = await apiPost<{ data: RawRepository }>("/repositories", { full_name: fullName });

  return mapRepository(response.data);
}

export async function setRepositoryActive(uuid: string, isActive: boolean): Promise<Repository> {
  const response = await apiPatch<{ data: RawRepository }>(`/repositories/${uuid}`, {
    is_active: isActive,
  });

  return mapRepository(response.data);
}

export async function deleteRepository(uuid: string): Promise<void> {
  await apiDelete(`/repositories/${uuid}`);
}
