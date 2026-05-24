export interface FaqItem {
    id: number;
    question: string;
    answer: string;
    status: boolean;
    created_at: string;
    updated_at: string;
}

export interface FaqSection {
    section_title: string;
    section_description: string;
    data: FaqItem[];
}

export interface FaqResponse {
    status: boolean;
    message: string;
    data: FaqSection;
}
