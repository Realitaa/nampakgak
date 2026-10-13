/**
 * Development fixtures for the NampakGak? user dashboard.
 *
 * The report and claim modules (models, routes, controllers) are not
 * implemented yet, so this static mock data stands in for real application
 * data. Keep everything mock here; the summary counts are derived from these
 * fixtures so the numbers always agree with the sample activity below.
 */

import type { LandingReport } from '@/components/landing/fixtures';

export const MOCK_USER = {
    name: 'Reza',
};

export type ClaimStatus = 'pending' | 'approved' | 'declined';

export type DashboardClaim = {
    id: number;
    direction: 'incoming' | 'submitted';
    reportTitle: string;
    claimant: string | null;
    submittedAt: string;
    status: ClaimStatus;
};

export const DASHBOARD_REPORTS: LandingReport[] = [
    {
        id: 101,
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
        id: 102,
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
        id: 103,
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
        id: 104,
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
        id: 105,
        type: 'lost',
        title: 'Navy blue hoodie, medium size',
        category: 'clothing',
        description:
            'Left on the fence beside the court during the afternoon futsal game. Small embroidered logo on the left chest.',
        location: 'Court behind FMIPA',
        occurredAt: '2026-10-06',
        status: 'returned',
    },
];

export const DASHBOARD_CLAIMS: DashboardClaim[] = [
    {
        id: 1,
        direction: 'incoming',
        reportTitle: 'Brown leather wallet (emptied for safekeeping)',
        claimant: 'Siti Maharani',
        submittedAt: '2026-10-10',
        status: 'pending',
    },
    {
        id: 2,
        direction: 'incoming',
        reportTitle: 'Scientific calculator left in lecture hall',
        claimant: 'Andi Pratama',
        submittedAt: '2026-10-09',
        status: 'pending',
    },
    {
        id: 3,
        direction: 'submitted',
        reportTitle: 'ID card found in the library',
        claimant: null,
        submittedAt: '2026-10-11',
        status: 'pending',
    },
    {
        id: 4,
        direction: 'submitted',
        reportTitle: 'Prescription glasses in a black case',
        claimant: null,
        submittedAt: '2026-10-02',
        status: 'approved',
    },
];

export type DashboardSummary = {
    activeLost: number;
    activeFound: number;
    pendingClaims: number;
    resolved: number;
};

export function buildDashboardSummary(): DashboardSummary {
    return {
        activeLost: DASHBOARD_REPORTS.filter(
            (report) => report.type === 'lost' && report.status === 'searching',
        ).length,
        activeFound: DASHBOARD_REPORTS.filter(
            (report) =>
                report.type === 'found' && report.status === 'awaiting-claim',
        ).length,
        pendingClaims: DASHBOARD_CLAIMS.filter(
            (claim) =>
                claim.direction === 'incoming' && claim.status === 'pending',
        ).length,
        resolved: DASHBOARD_REPORTS.filter(
            (report) => report.status === 'returned',
        ).length,
    };
}
