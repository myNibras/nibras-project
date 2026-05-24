export type ChatThreadType = 'group' | 'direct';

export interface ChatNotificationItem {
  id: number;
  course_id: number;
  course_name: string | null;
  course_slug: string | null;
  course_slug_en: string | null;
  thread_type: ChatThreadType;
  thread_partner_type: 'Student' | 'Teacher' | null;
  thread_partner_id: number | null;
  sender_name: string | null;
  body_preview: string;
  created_at: string;
}

export interface ChatUnreadCountResponse {
  status: boolean;
  data: { count: number };
}

export interface ChatNotificationsListResponse {
  status: boolean;
  data: ChatNotificationItem[];
}

export interface ChatMarkReadResponse {
  status: boolean;
  data: { marked: number };
}
