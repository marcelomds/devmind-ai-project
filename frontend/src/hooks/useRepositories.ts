import { useCallback, useEffect, useState } from "react";
import {
  connectRepository,
  deleteRepository,
  listRepositories,
  setRepositoryActive,
} from "../services/repository";
import type { Repository } from "../types/repository";

export function useRepositories() {
  const [repositories, setRepositories] = useState<Repository[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let cancelled = false;

    listRepositories()
      .then((data) => {
        if (!cancelled) setRepositories(data);
      })
      .catch(() => {
        if (!cancelled) setError("Could not load repositories.");
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, []);

  const connect = useCallback(async (fullName: string) => {
    setError(null);

    try {
      const repository = await connectRepository(fullName);
      setRepositories((current) => [repository, ...current.filter((r) => r.uuid !== repository.uuid)]);
    } catch {
      setError(`Could not connect "${fullName}". Check the name and the GITHUB_TOKEN permissions.`);
    }
  }, []);

  const toggleActive = useCallback(async (repository: Repository) => {
    setError(null);

    try {
      const updated = await setRepositoryActive(repository.uuid, !repository.isActive);
      setRepositories((current) => current.map((r) => (r.uuid === updated.uuid ? updated : r)));
    } catch {
      setError("Could not update this repository.");
    }
  }, []);

  const remove = useCallback(async (repository: Repository) => {
    setError(null);

    try {
      await deleteRepository(repository.uuid);
      setRepositories((current) => current.filter((r) => r.uuid !== repository.uuid));
    } catch {
      setError("Could not remove this repository.");
    }
  }, []);

  return { repositories, loading, error, connect, toggleActive, remove };
}
