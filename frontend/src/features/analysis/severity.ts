import type { Severity } from "../../types/analysis";

interface SeverityStyle {
  label: string;
  bar: string;
  badge: string;
}

export const severityStyles: Record<Severity, SeverityStyle> = {
  critical: { label: "Critical", bar: "bg-red-500", badge: "bg-red-500/15 text-red-400" },
  high: { label: "High", bar: "bg-orange-500", badge: "bg-orange-500/15 text-orange-400" },
  medium: { label: "Medium", bar: "bg-amber-500", badge: "bg-amber-500/15 text-amber-400" },
  low: { label: "Low", bar: "bg-green-500", badge: "bg-green-500/15 text-green-400" },
  info: { label: "Info", bar: "bg-neutral-500", badge: "bg-neutral-500/15 text-neutral-400" },
};
