export interface Teacher {
  id: number;
  name: string;
}

export interface Course {
  id: number;
  title: string;
  teacher: Teacher;
}

export interface ClassRoom {
  id: number;
  name: string;
}

export type ReviewStatus = 'approved' | 'pending' | 'rejected';

export interface Testimonial {
  id: number;
  name: string;
  text: string;
  /** Optional glyph(s) shown as the decorative quote (e.g. " or "). Defaults to “ if omitted. */
  quote?: string;
  /** Hex color for the decorative quote (e.g. #1396FD). */
  quote_icon_color?: string;
  rate: number;
  status: ReviewStatus;
  image: string;
  class_room?: ClassRoom;
  course: Course;
  created_at: string;  // or Date if you parse it
  updated_at: string;  // or Date if you parse it
}

export interface TestimonialsSection {
  section_title: string;
  section_description: string;
  data: Testimonial[];
}
