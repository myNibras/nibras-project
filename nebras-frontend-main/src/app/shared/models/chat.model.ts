export type ChatMode = 'channel' | 'direct';

export interface ChatMessage {
  id: number;
  sender_type: 'student' | 'teacher';
  sender_id: number;
  sender_name: string;
  body: string;
  reply_to_message_id?: number | null;
  reply_to?: {
    id: number;
    sender_name: string;
    body: string;
  } | null;
  likes_count?: number;
  liked_by_me?: boolean;
  mentioned_students?: Array<{
    id: number;
    name: string;
  }>;
  created_at: string;
}

export interface ChatParticipant {
  id: number;
  name: string;
}

export interface ChatMessagesResponse {
  status: boolean;
  message?: string;
  data: ChatMessage[];
}
