export interface articlesResponse {
    status: boolean;
    message: string;
    data: articlesItem[] | articlesItem;
}

/** API list response: data is a section object with title, description, and items */
export interface ArticlesSectionData {
    section_title: string;
    section_description: string;
    data: articlesItem[];
}

export interface articlesItem {
    id: number;
    title: string;
    small_description: string;
    full_description: string;
    image: string;
    expiry_date: Date;
    created_at: Date;
}