/**
 * Shared types and helpers for the NampakGak? landing page.
 *
 * The landing page renders a small, real slice of the reports table. Keep the
 * types in sync with the props the backend sends so the client-side filtering
 * below stays honest about what is available.
 */

export type ReportType = 'lost' | 'found';

export type ReportTypeFilter = 'all' | ReportType;

export type LandingReport = {
    id: number;
    title: string;
    type: ReportType;
    description: string;
    image_path: string | null;
    status: string;
    event_date: string | null;
    category: {
        id: number;
        name: string;
    };
    location: {
        id: number;
        name: string;
    };
};

const dateFormatter = new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
});

/**
 * Format a `Y-m-d` incident date, or return `null` when there is nothing
 * sensible to show. Invalid values never throw: a missing or malformed date
 * simply hides the date row.
 */
export function formatReportDate(value: string | null): string | null {
    if (!value) {
        return null;
    }

    const date = new Date(`${value}T00:00:00`);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    return dateFormatter.format(date);
}
