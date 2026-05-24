/** API list response: data is a section object with title, description, and items */
export interface TeachersSectionData {
    section_title: string;
    section_description: string;
    data: Teacher[];
}

export interface Teacher {
    id: number;
    image: string | null;
    name: string;
    description: string | null;
    video: string | null;
    video_url?: string | null;
    video_embed_url?: string | null;
    position: string | null;
    years_of_experience: number;
    reviews: number;
    number_of_classes: number;
    number_of_students: number | null;
    courses: string[];
}