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
