export interface newsResponse {
    status: boolean;
    message: string;
    data: newsItem[] | newsItem;
}

/** API list response: data is a section object with title, description, and items */
export interface NewsSectionData {
    section_title: string;
    section_description: string;
    data: newsItem[];
}

export interface newsItem {
    id: number;
    title: string;
    small_description: string;
    full_description: string;
    image: string;
    expiry_date: Date;
    created_at: Date;
}