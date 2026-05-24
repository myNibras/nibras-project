import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { HttpResponse } from '@angular/common/http';
import { ApiService } from 'app/core/api/api.service';
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import { ChatMessage, ChatMessagesResponse, ChatParticipant } from 'app/shared/models/chat.model';

@Injectable({
  providedIn: 'root'
})
export class ChatService {
  constructor(
    private apiService: ApiService,
    private authService: AuthService
  ) {}

  getMessages(courseId: number): Observable<ChatMessage[]> {
    const token = this.authService.getToken();
    return this.apiService.get<ChatMessagesResponse>(
      `api/v1/courses/${courseId}/chat/messages`,
      {},
      { Authorization: `Bearer ${token}` }
    ).pipe(
      map((res: HttpResponse<ChatMessagesResponse>) => {
        const body = res.body;
        if (body?.status && body.data) {
          return body.data;
        }
        return [];
      })
    );
  }

  sendMessage(
    courseId: number,
    body: string,
    replyToMessageId?: number,
    mentionedStudentIds: number[] = []
  ): Observable<ChatMessage | null> {
    const token = this.authService.getToken();
    const payload: { body: string; reply_to_message_id?: number; mentioned_student_ids?: number[] } = { body };
    if (replyToMessageId) {
      payload.reply_to_message_id = replyToMessageId;
    }
    if (mentionedStudentIds.length) {
      payload.mentioned_student_ids = mentionedStudentIds;
    }
    return this.apiService.post<ChatMessagesResponse & { data: ChatMessage }>(
      `api/v1/courses/${courseId}/chat/messages`,
      JSON.stringify(payload),
      { Authorization: `Bearer ${token}` }
    ).pipe(
      map((res: HttpResponse<ChatMessagesResponse & { data: ChatMessage }>) => {
        const response = res.body;
        if (response?.status && response.data) {
          return response.data;
        }
        return null;
      })
    );
  }

  /** One-to-one direct chat with the teacher for this course. */
  getDirectMessages(courseId: number): Observable<ChatMessage[]> {
    const token = this.authService.getToken();
    return this.apiService.get<ChatMessagesResponse>(
      `api/v1/courses/${courseId}/chat/direct`,
      {},
      { Authorization: `Bearer ${token}` }
    ).pipe(
      map((res: HttpResponse<ChatMessagesResponse>) => {
        const body = res.body;
        if (body?.status && body.data) {
          return body.data;
        }
        return [];
      })
    );
  }

  sendDirectMessage(courseId: number, body: string, replyToMessageId?: number): Observable<ChatMessage | null> {
    const token = this.authService.getToken();
    const payload: { body: string; reply_to_message_id?: number } = { body };
    if (replyToMessageId) {
      payload.reply_to_message_id = replyToMessageId;
    }
    return this.apiService.post<ChatMessagesResponse & { data: ChatMessage }>(
      `api/v1/courses/${courseId}/chat/direct`,
      JSON.stringify(payload),
      { Authorization: `Bearer ${token}` }
    ).pipe(
      map((res: HttpResponse<ChatMessagesResponse & { data: ChatMessage }>) => {
        const response = res.body;
        if (response?.status && response.data) {
          return response.data;
        }
        return null;
      })
    );
  }

  getGroupParticipants(courseId: number): Observable<ChatParticipant[]> {
    const token = this.authService.getToken();
    return this.apiService.get<{ status: boolean; data: ChatParticipant[] }>(
      `api/v1/courses/${courseId}/chat/group/participants`,
      {},
      { Authorization: `Bearer ${token}` }
    ).pipe(
      map((res: HttpResponse<{ status: boolean; data: ChatParticipant[] }>) => {
        return res.body?.status ? (res.body.data || []) : [];
      })
    );
  }

  likeGroupMessage(courseId: number, messageId: number): Observable<boolean> {
    const token = this.authService.getToken();
    return this.apiService.post<{ status: boolean }>(
      `api/v1/courses/${courseId}/chat/group/messages/${messageId}/like`,
      JSON.stringify({}),
      { Authorization: `Bearer ${token}` }
    ).pipe(map((res: HttpResponse<{ status: boolean }>) => !!res.body?.status));
  }

  unlikeGroupMessage(courseId: number, messageId: number): Observable<boolean> {
    const token = this.authService.getToken();
    return this.apiService.delete<{ status: boolean }>(
      `api/v1/courses/${courseId}/chat/group/messages/${messageId}/like`,
      {},
      { Authorization: `Bearer ${token}` }
    ).pipe(map((res: HttpResponse<{ status: boolean }>) => !!res.body?.status));
  }
}
