export type ItemTypeValue = 'lost' | 'found';

export type ItemStatusValue = 'active' | 'claimed' | 'resolved';

export type ItemCategoryRef = {
    id: number;
    name: string;
};

export type ItemLocationRef = {
    id: number;
    name: string;
};

export type ItemReport = {
    id: number;
    title: string;
    type: ItemTypeValue;
    description: string;
    image_path: string | null;
    status: ItemStatusValue;
    event_date: string;
    category: ItemCategoryRef;
    location: ItemLocationRef;
};

export type FilterOption = {
    id: number;
    name: string;
};

export type ItemFilters = {
    search: string | null;
    type: ItemTypeValue | null;
    category: number | null;
    location: number | null;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLink[];
};
