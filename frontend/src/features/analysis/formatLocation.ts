export function formatLocation(filePath: string | null, lineStart: number | null, lineEnd: number | null) {
  if (!filePath) return null;
  if (!lineStart) return filePath;
  if (lineEnd && lineEnd !== lineStart) return `${filePath}:${lineStart}-${lineEnd}`;
  return `${filePath}:${lineStart}`;
}
