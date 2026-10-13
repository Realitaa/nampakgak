/**
 * Development fixtures for the NampakGak? landing page.
 *
 * The report module (models, routes, controllers) is not implemented yet, so
 * these sample reports stand in for real application data. Keep all fixture
 * data here and do not mix it with anything persisted by the backend.
 */

export type CategoryId =
    | 'documents'
    | 'electronics'
    | 'wallets-bags'
    | 'keys'
    | 'clothing'
    | 'other';

export type ReportType = 'lost' | 'found';

export type ReportStatus = 'searching' | 'awaiting-claim' | 'returned';

export type ReportTypeFilter = 'all' | ReportType;

export type LandingReport = {
    id: number;
    type: ReportType;
    title: string;
    category: CategoryId;
    description: string;
    location: string;
    occurredAt: string;
    status: ReportStatus;
};

export type LandingCategory = {
    id: CategoryId;
    label: string;
};

export const LANDING_CATEGORIES: LandingCategory[] = [
    { id: 'documents', label: 'ID Cards & Documents' },
    { id: 'electronics', label: 'Electronics' },
    { id: 'wallets-bags', label: 'Wallets & Bags' },
    { id: 'keys', label: 'Keys' },
    { id: 'clothing', label: 'Clothing' },
    { id: 'other', label: 'Other Items' },
];

export const CATEGORY_LABELS: Record<CategoryId, string> = Object.fromEntries(
    LANDING_CATEGORIES.map(({ id, label }) => [id, label]),
) as Record<CategoryId, string>;

export const REPORT_TYPE_LABELS: Record<ReportType, string> = {
    lost: 'Lost',
    found: 'Found',
};

export const REPORT_STATUS_LABELS: Record<ReportStatus, string> = {
    searching: 'Still searching',
    'awaiting-claim': 'Awaiting claim',
    returned: 'Reunited',
};

// Sample reports only. Found-item entries deliberately contain no owner
// names, contact details, or identifying document numbers.
export const LANDING_REPORTS: LandingReport[] = [
    {
        id: 1,
        type: 'lost',
        title: 'Student ID card (KTM)',
        category: 'documents',
        description:
            'Lost between lectures on the second floor, most likely around the staircase near Room B2. Card has a photo; I can describe all details for verification.',
        location: 'Building B, FMIPA — 2nd floor',
        occurredAt: '2026-10-12',
        status: 'searching',
    },
    {
        id: 2,
        type: 'found',
        title: 'ID card found in the library',
        category: 'documents',
        description:
            'Found on a reading-room table and kept by the finder for safekeeping. No owner details are shown here. Submit a claim and describe the card to verify ownership before hand-over.',
        location: 'Campus library — reading room',
        occurredAt: '2026-10-11',
        status: 'awaiting-claim',
    },
    {
        id: 3,
        type: 'lost',
        title: 'Black wireless earbuds in charging case',
        category: 'electronics',
        description:
            'Left the case on a canteen table between 12:00 and 13:00. Small scratch on the lid. The case holds two earbuds with a blue dot sticker.',
        location: 'Faculty canteen, FMIPA',
        occurredAt: '2026-10-10',
        status: 'searching',
    },
    {
        id: 4,
        type: 'found',
        title: 'Scientific calculator left in lecture hall',
        category: 'electronics',
        description:
            'Found after the afternoon statistics class. Casio model, cover slightly scuffed. Describe the model and any stickers to claim it.',
        location: 'Lecture hall 3',
        occurredAt: '2026-10-09',
        status: 'awaiting-claim',
    },
    {
        id: 5,
        type: 'lost',
        title: 'Bunch of keys with a blue keychain',
        category: 'keys',
        description:
            'Three keys on a ring with a small blue whale keychain. Lost somewhere near the motorcycle parking area after the morning break.',
        location: 'Parking area B',
        occurredAt: '2026-10-08',
        status: 'searching',
    },
    {
        id: 6,
        type: 'found',
        title: 'Brown leather wallet (emptied for safekeeping)',
        category: 'wallets-bags',
        description:
            'Found near the shuttle stop. For safety, the cash and cards inside were handed to campus security separately — only the wallet itself is part of this report. Describe its contents to reclaim your items.',
        location: 'Campus shuttle stop',
        occurredAt: '2026-10-07',
        status: 'awaiting-claim',
    },
    {
        id: 7,
        type: 'lost',
        title: 'Navy blue hoodie, medium size',
        category: 'clothing',
        description:
            'Left on the fence beside the court during the afternoon futsal game. Small embroidered logo on the left chest.',
        location: 'Court behind FMIPA',
        occurredAt: '2026-10-06',
        status: 'returned',
    },
    {
        id: 8,
        type: 'found',
        title: 'Prescription glasses in a black case',
        category: 'other',
        description:
            'Found in the lab corridor after the practical session. Dark rectangular frames with a thin metal rim. Describe the case contents to claim.',
        location: 'Lab corridor, Building A',
        occurredAt: '2026-10-05',
        status: 'returned',
    },
];

const dateFormatter = new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
});

export function formatReportDate(isoDate: string): string {
    return dateFormatter.format(new Date(`${isoDate}T00:00:00`));
}

export function filterReports(
    reports: LandingReport[],
    filters: {
        query?: string;
        type?: ReportTypeFilter;
        category?: CategoryId | 'all';
    },
): LandingReport[] {
    const query = filters.query?.trim().toLowerCase() ?? '';
    const type = filters.type ?? 'all';
    const category = filters.category ?? 'all';

    return reports.filter((report) => {
        if (type !== 'all' && report.type !== type) {
            return false;
        }

        if (category !== 'all' && report.category !== category) {
            return false;
        }

        if (!query) {
            return true;
        }

        return [
            report.title,
            report.description,
            report.location,
            CATEGORY_LABELS[report.category],
        ].some((field) => field.toLowerCase().includes(query));
    });
}

export function hasActiveFilters(filters: {
    query?: string;
    type?: ReportTypeFilter;
    category?: CategoryId | 'all';
}): boolean {
    return Boolean(
        filters.query?.trim() ||
        (filters.type && filters.type !== 'all') ||
        (filters.category && filters.category !== 'all'),
    );
}
