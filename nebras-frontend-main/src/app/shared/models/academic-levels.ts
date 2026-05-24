export interface AcademicLevel {
    id: number;
    title: string;
    description: string;
    slug: string;
    slug_ar: string;
    slug_en: string;
    image: string;
    created_at: string;
}

export interface AcademicLevelsResponse {
    section_title: string;
    section_description: string;
    data: AcademicLevel[];
}
