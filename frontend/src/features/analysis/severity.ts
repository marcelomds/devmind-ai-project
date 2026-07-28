import type { Severity } from "../../types/analysis";

interface SeverityStyle {
  label: string;
  bar: string;
  badge: string;
  // Hex value mirroring `bar`, for contexts (e.g. Recharts SVG fills) that can't use Tailwind classes.
  chartColor: string;
}

export const severityStyles: Record<Severity, SeverityStyle> = {
  critical: {
    label: "Critical",
    bar: "bg-red-500",
    badge: "bg-red-500/15 text-red-400",
    chartColor: "#ef4444",
  },
  high: {
    label: "High",
    bar: "bg-orange-500",
    badge: "bg-orange-500/15 text-orange-400",
    chartColor: "#f97316",
  },
  medium: {
    label: "Medium",
    bar: "bg-amber-500",
    badge: "bg-amber-500/15 text-amber-400",
    chartColor: "#f59e0b",
  },
  low: {
    label: "Low",
    bar: "bg-green-500",
    badge: "bg-green-500/15 text-green-400",
    chartColor: "#22c55e",
  },
  info: {
    label: "Info",
    bar: "bg-neutral-500",
    badge: "bg-neutral-500/15 text-neutral-400",
    chartColor: "#737373",
  },
};
