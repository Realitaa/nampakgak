export type ItemType = 'lost' | 'found';

export type ItemStatus = 'active' | 'claimed' | 'resolved';

export type ItemCategory = {
    id: number;
    name: string;
};

export type ItemLocation = {
    id: number;
    name: string;
};

/**
 * A lost or found report as returned by the items listing endpoint.
 */
export type ItemReport = {
    id: number;
    title: string;
    type: ItemType;
    description: string;
    image_path: string | null;
    status: ItemStatus;
    event_date: string | null;
    created_at: string;
    category: ItemCategory;
    location: ItemLocation;
};

/**
 * Active filters shared by the backend and the Discover page UI.
 */
export type ItemFilters = {
    search: string | null;
    type: ItemType | null;
    category: number | null;
    location: number | null;
};

export type TypeOptions = Record<string, string>;

export type StatusOptions = Record<string, string>;

/**
 * A report as returned to its owner on the management pages, where the raw
 * foreign keys and the nullable incident date are needed by the edit form.
 */
export type OwnedItemReport = {
    id: number;
    title: string;
    type: ItemType;
    description: string;
    image_path: string | null;
    status: ItemStatus;
    event_date: string | null;
    category_id: number;
    location_id: number;
    category: ItemCategory;
    location: ItemLocation;
};

/**
 * Public report data on the shared detail page. Contains no owner identity and
 * no contact details; those are owner-only props.
 */
export type ItemDetail = {
    id: number;
    title: string;
    type: ItemType;
    description: string;
    image_path: string | null;
    status: ItemStatus;
    event_date: string | null;
    created_at: string | null;
    category: ItemCategory;
    location: ItemLocation;
};

/**
 * Server-computed action flags for the detail page. The backend is the
 * authority; Vue only renders what these allow.
 */
export type ItemDetailPermissions = {
    isOwner: boolean;
    canManage: boolean;
    canReportFound: boolean;
    canClaim: boolean;
    canEdit: boolean;
    canDelete: boolean;
};

/**
 * Contact details shown only to the report owner, and only for the workflow
 * that needs them (returning the item / reviewing a sighting).
 */
export type OwnerContact = {
    name: string;
    email: string;
    phone_number: string | null;
};

export type OwnerClaim = {
    id: number;
    proof_description: string;
    proof_image: string | null;
    status: string;
    rejection_reason: string | null;
    created_at: string | null;
    claimant: OwnerContact;
};

export type OwnerFoundNotification = {
    id: number;
    note: string;
    image_path: string | null;
    created_at: string | null;
    reporter: OwnerContact;
};

export type OwnerLinks = {
    edit: string;
    delete: string;
    index: string;
};

/**
 * Active filters for the owner-facing report listings (both kinds).
 */
export type OwnedItemFilters = {
    search: string | null;
    status: ItemStatus | null;
    category: number | null;
    location: number | null;
};

export type FilterOption = {
    id: number;
    name: string;
};

/**
 * Length-aware paginator payload serialized by Laravel.
 */
export type PaginatedItems = {
    current_page: number;
    data: ItemReport[];
    first_page_url: string | null;
    from: number | null;
    last_page: number;
    last_page_url: string | null;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
};
