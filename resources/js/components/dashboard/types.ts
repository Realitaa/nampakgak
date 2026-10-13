/**
 * Shared prop shapes for the authenticated user dashboard.
 *
 * These mirror exactly what `DashboardController::index` sends to Inertia —
 * deliberately narrow, and carrying no contact details for other users.
 */

export type DashboardSummary = {
    activeLost: number;
    activeFound: number;
    pendingClaims: number;
    resolved: number;
};

export type DashboardReport = {
    id: number;
    title: string;
    type: 'lost' | 'found';
    status: string;
    category: { id: number; name: string };
    location: { id: number; name: string };
    event_date: string | null;
    created_at: string | null;
};

export type DashboardClaim = {
    id: number;
    direction: 'incoming' | 'submitted';
    reportTitle: string;
    reportId: number;
    claimant: string | null;
    submittedAt: string;
    status: string;
    proofDescription: string | null;
    rejectionReason: string | null;
};

/** value => label maps sent from the backend enums. */
export type DashboardOptions = Record<string, string>;
