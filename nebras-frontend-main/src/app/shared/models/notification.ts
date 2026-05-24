export interface StudentNotification {
  id: number;
  message: string;
  course_name: string | null;
  teacher_name: string | null;
  is_read: boolean;
  created_at: string;
}
