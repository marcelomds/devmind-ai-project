import { useState } from "react";
import type { FormEvent } from "react";
import { useRepositories } from "../../hooks/useRepositories";

export function RepositoriesScreen() {
  const [fullName, setFullName] = useState("");
  const [connecting, setConnecting] = useState(false);
  const { repositories, loading, error, connect, toggleActive, remove } = useRepositories();

  const canSubmit = fullName.trim().length > 0 && !connecting;

  async function handleSubmit(event: FormEvent) {
    event.preventDefault();
    if (!canSubmit) return;

    setConnecting(true);
    await connect(fullName.trim());
    setConnecting(false);
    setFullName("");
  }

  return (
    <main className="mx-auto flex max-w-3xl flex-col gap-8 px-6 py-10">
      <header>
        <p className="text-sm font-medium uppercase tracking-wider text-indigo-400">DevMind AI</p>
        <h1 className="mt-1 text-3xl font-semibold">Repositories</h1>
        <p className="mt-2 text-sm text-neutral-500">
          Connect a GitHub repo, then add its webhook manually (Settings → Webhooks) pointing to{" "}
          <code className="text-neutral-400">/api/v1/webhooks/github</code>.
        </p>
      </header>

      <form
        onSubmit={handleSubmit}
        className="flex items-center gap-3 rounded-xl border border-neutral-800 bg-neutral-900 p-5"
      >
        <input
          value={fullName}
          onChange={(event) => setFullName(event.target.value)}
          placeholder="owner/repo"
          className="flex-1 rounded-lg border border-neutral-800 bg-neutral-950 px-4 py-2 font-mono text-sm text-neutral-100 placeholder:text-neutral-600 focus:border-indigo-500 focus:outline-none"
        />
        <button
          type="submit"
          disabled={!canSubmit}
          className="rounded-lg bg-indigo-500 px-5 py-2 text-sm font-medium text-white transition hover:bg-indigo-400 disabled:cursor-not-allowed disabled:opacity-40"
        >
          {connecting ? "Connecting…" : "Connect"}
        </button>
      </form>

      {error && (
        <div className="rounded-lg border border-red-900 bg-red-950/40 px-4 py-3 text-sm text-red-300">
          {error}
        </div>
      )}

      {loading ? (
        <p className="text-sm text-neutral-500">Loading…</p>
      ) : repositories.length === 0 ? (
        <p className="text-sm text-neutral-500">No repositories connected yet.</p>
      ) : (
        <ul className="flex flex-col gap-3">
          {repositories.map((repository) => (
            <li
              key={repository.uuid}
              className="flex items-center justify-between rounded-lg border border-neutral-800 bg-neutral-900 p-4"
            >
              <div>
                <p className="font-mono text-sm text-neutral-100">{repository.fullName}</p>
                <p className="mt-1 text-xs text-neutral-500">github_id {repository.githubId}</p>
              </div>

              <div className="flex items-center gap-3">
                <span
                  className={`rounded-full px-2.5 py-1 text-xs font-medium ${
                    repository.isActive
                      ? "bg-green-950 text-green-400"
                      : "bg-neutral-800 text-neutral-400"
                  }`}
                >
                  {repository.isActive ? "Watching" : "Paused"}
                </span>

                <button
                  onClick={() => toggleActive(repository)}
                  className="rounded-lg border border-neutral-700 px-3 py-1.5 text-xs font-medium text-neutral-200 transition hover:border-neutral-500"
                >
                  {repository.isActive ? "Pause" : "Resume"}
                </button>

                <button
                  onClick={() => remove(repository)}
                  className="rounded-lg border border-red-900 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:border-red-700"
                >
                  Remove
                </button>
              </div>
            </li>
          ))}
        </ul>
      )}
    </main>
  );
}
