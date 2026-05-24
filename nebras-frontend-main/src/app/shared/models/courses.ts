
// Single Course
export interface Course {
    id: number;
    title: string;
    rating?: number;
    course_type?: string;
    course_link?: string | null;
    short_description: string;
    description: string;
    duration: string;
    price: string;
    discount_price: string;
    schedule: string;
    image: string;
    class: string;
    class_id: number | null;
    slug: string;
    slug_ar: string;
    slug_en: string;
    payment_type?: string;
    semester_months?: number;
    monthly_amount?: number | string;
    available_seats?: number | null;
    registered_students_count?: number | null;
    final_available_seats?: number | null;
    allow_send_testimonial?: boolean;
    purchased?: boolean;
    teacher: Teacher;
    semester: Semester;
    academic_level: AcademicLevel;
    curriculums: Curriculum[];
    related_courses: Course[];
}

// Teacher
export interface Teacher {
    id: number;
    name: string;
}

// Semester
export interface Semester {
    id: number;
    title: string;
    type: string;
    type_id: number;
}

// Academic Level
export interface AcademicLevel {
    id: number;
    title: string;
    description: string;
    image: string;
    slug: string;
    slug_ar: string;
    slug_en: string;
}

// Curriculum
export interface Curriculum {
    id: number;
    title: string;
    units: Unit[];
}

// Unit inside curriculum
export interface Unit {
    id: number;
    title: string;
    link: string | null;
    /** Whether the link should open in a new tab. null when no link is exposed. */
    open_in_new_tab?: boolean | null;
}

export interface PurchasedCourse {
  course_id: number;
  title: string;
  class: string;
  schedule: string;
  duration: string;
  slug_en:string;
  slug_ar:string;
  slug:string;
  image:string;
  course_type : string;
  course_link:string;
  teacher: Teacher;
  semester: string;
  available_seats?: number | null;
  registered_students_count?: number | null;
  final_available_seats?: number | null;
  /** Whether the current student has any testimonial for this course */
  has_submitted_testimonial?: boolean;
}

export interface PurchasedCoursesResponse {
  status: boolean;
  message: string;
  data: PurchasedCourse[];
}