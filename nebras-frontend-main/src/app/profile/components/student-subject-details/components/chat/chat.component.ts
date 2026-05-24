import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { DatePipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { TranslateModule } from '@ngx-translate/core';
import { Subject, combineLatest, interval, takeUntil, switchMap, startWith } from 'rxjs';
import { ChatService } from 'app/shared/services/chat/chat.service';
import { ChatNotificationsService } from 'app/shared/services/chat-notifications/chat-notifications.service';
import { ChatMessage, ChatMode, ChatParticipant } from 'app/shared/models/chat.model';
import { LinkifyPipe } from 'app/shared/pipes/linkify.pipe';

@Component({
  selector: 'app-chat',
  imports: [DatePipe, FormsModule, TranslateModule, LinkifyPipe],
  templateUrl: './chat.component.html',
  styleUrl: './chat.component.scss'
})
export class ChatComponent implements OnInit, OnDestroy {
  messages: ChatMessage[] = [];
  groupParticipants: ChatParticipant[] = [];
  mentionStudentId: number | null = null;
  selectedMentionIds: number[] = [];
  mentionSuggestions: ChatParticipant[] = [];
  mentionQuery = '';
  showMentionSuggestions = false;
  replyToMessage: ChatMessage | null = null;
  newMessage = '';
  loading = true;
  sending = false;
  courseId: number | null = null;
  error: string | null = null;
  /** 'channel' = course chat (teacher + all students), 'direct' = private with teacher */
  chatMode: ChatMode = 'channel';

  private destroy$ = new Subject<void>();

  constructor(
    private route: ActivatedRoute,
    private chatService: ChatService,
    private chatNotifications: ChatNotificationsService,
  ) {}

  ngOnInit(): void {
    combineLatest([this.route.paramMap, this.route.queryParamMap])
      .pipe(takeUntil(this.destroy$))
      .subscribe(([params, queryParams]) => {
        const id = params.get('course_id');
        const newCourseId = id ? +id : null;
        const requestedMode = queryParams.get('mode');
        const nextMode: ChatMode = requestedMode === 'direct' ? 'direct' : 'channel';

        const courseChanged = newCourseId !== this.courseId;
        const modeChanged = nextMode !== this.chatMode;

        this.courseId = newCourseId;
        this.chatMode = nextMode;

        if (!this.courseId) {
          this.loading = false;
          return;
        }

        if (!courseChanged && !modeChanged) return;

        this.messages = [];
        this.error = null;
        this.replyToMessage = null;
        this.selectedMentionIds = [];
        this.mentionStudentId = null;

        this.loadMessages();
        this.loadGroupParticipantsIfNeeded();
        if (courseChanged) this.startPolling();
        this.markCurrentThreadRead();
      });
  }

  setMode(mode: ChatMode): void {
    if (this.chatMode === mode) return;
    this.chatMode = mode;
    this.messages = [];
    this.replyToMessage = null;
    this.selectedMentionIds = [];
    this.mentionStudentId = null;
    if (this.courseId) {
      this.loadMessages();
      this.loadGroupParticipantsIfNeeded();
      this.markCurrentThreadRead();
    }
  }

  private markCurrentThreadRead(): void {
    if (this.courseId == null) return;
    const threadType = this.chatMode === 'direct' ? 'direct' : 'group';
    this.chatNotifications.markRead(this.courseId, threadType).subscribe();
  }

  private loadMessages(): void {
    if (!this.courseId) return;
    this.loading = true;
    const req = this.chatMode === 'direct'
      ? this.chatService.getDirectMessages(this.courseId)
      : this.chatService.getMessages(this.courseId);
    req.subscribe({
      next: (msgs) => {
        this.messages = msgs;
        this.loading = false;
        this.scrollToBottom();
      },
      error: (err) => {
        this.error = err?.error?.message || err?.message || 'Failed to load chat';
        this.loading = false;
      }
    });
  }

  private loadGroupParticipantsIfNeeded(): void {
    if (!this.courseId || this.chatMode !== 'channel') {
      this.groupParticipants = [];
      return;
    }
    this.chatService.getGroupParticipants(this.courseId).subscribe({
      next: (participants) => {
        this.groupParticipants = participants;
      }
    });
  }

  private startPolling(): void {
    if (!this.courseId) return;
    interval(5000).pipe(
      startWith(0),
      takeUntil(this.destroy$),
      switchMap(() => this.chatMode === 'direct'
        ? this.chatService.getDirectMessages(this.courseId!)
        : this.chatService.getMessages(this.courseId!))
    ).subscribe({
      next: (msgs) => {
        this.messages = msgs;
        this.scrollToBottom();
      }
    });
  }

  sendMessage(): void {
    const body = this.newMessage?.trim();
    if (!body || !this.courseId || this.sending) return;
    this.sending = true;
    const replyToMessageId = this.replyToMessage?.id;
    const mentionedStudentIds = this.chatMode === 'channel' ? this.selectedMentionIds : [];
    const req = this.chatMode === 'direct'
      ? this.chatService.sendDirectMessage(this.courseId, body, replyToMessageId)
      : this.chatService.sendMessage(this.courseId, body, replyToMessageId, mentionedStudentIds);
    req.subscribe({
      next: (msg) => {
        if (msg) {
          this.messages = [...this.messages, msg];
          this.newMessage = '';
          this.replyToMessage = null;
          this.selectedMentionIds = [];
          this.mentionStudentId = null;
          this.mentionSuggestions = [];
          this.mentionQuery = '';
          this.showMentionSuggestions = false;
          this.scrollToBottom();
        }
        this.sending = false;
      },
      error: () => {
        this.sending = false;
      }
    });
  }

  selectReply(msg: ChatMessage): void {
    this.replyToMessage = msg;
  }

  clearReply(): void {
    this.replyToMessage = null;
  }

  addMention(): void {
    if (!this.mentionStudentId) return;
    if (!this.selectedMentionIds.includes(this.mentionStudentId)) {
      this.selectedMentionIds = [...this.selectedMentionIds, this.mentionStudentId];
      const target = this.groupParticipants.find(p => p.id === this.mentionStudentId);
      if (target && !this.newMessage.includes(`@${target.name}`)) {
        this.newMessage = `${this.newMessage} @${target.name}`.trim();
      }
    }
    this.mentionStudentId = null;
  }

  removeMention(id: number): void {
    this.selectedMentionIds = this.selectedMentionIds.filter(x => x !== id);
  }

  getMentionName(id: number): string {
    return this.groupParticipants.find(p => p.id === id)?.name || `${id}`;
  }

  toggleLike(msg: ChatMessage): void {
    if (!this.courseId || this.chatMode !== 'channel') return;
    const req = msg.liked_by_me
      ? this.chatService.unlikeGroupMessage(this.courseId, msg.id)
      : this.chatService.likeGroupMessage(this.courseId, msg.id);
    req.subscribe({
      next: (ok) => {
        if (!ok) return;
        this.messages = this.messages.map(m => {
          if (m.id !== msg.id) return m;
          const liked = !m.liked_by_me;
          const likesCount = (m.likes_count || 0) + (liked ? 1 : -1);
          return { ...m, liked_by_me: liked, likes_count: Math.max(0, likesCount) };
        });
      }
    });
  }

  onMessageInputChange(value: string): void {
    this.newMessage = value;
    if (this.chatMode !== 'channel') {
      this.showMentionSuggestions = false;
      return;
    }
    const atMatch = value.match(/(?:^|\s)@([^\s@]*)$/);
    if (!atMatch) {
      this.showMentionSuggestions = false;
      this.mentionSuggestions = [];
      this.mentionQuery = '';
      return;
    }
    this.mentionQuery = (atMatch[1] || '').toLowerCase();
    this.mentionSuggestions = this.groupParticipants
      .filter(p => !this.selectedMentionIds.includes(p.id))
      .filter(p => !this.mentionQuery || p.name.toLowerCase().includes(this.mentionQuery))
      .slice(0, 6);
    this.showMentionSuggestions = this.mentionSuggestions.length > 0;
  }

  chooseMention(participant: ChatParticipant): void {
    const match = this.newMessage.match(/(?:^|\s)@([^\s@]*)$/);
    if (!match) return;
    const replacement = `@${participant.name} `;
    this.newMessage = this.newMessage.replace(/(?:^|\s)@([^\s@]*)$/, (full) => {
      const hasLeadingSpace = full.startsWith(' ');
      return `${hasLeadingSpace ? ' ' : ''}${replacement}`;
    });
    if (!this.selectedMentionIds.includes(participant.id)) {
      this.selectedMentionIds = [...this.selectedMentionIds, participant.id];
    }
    this.showMentionSuggestions = false;
    this.mentionSuggestions = [];
    this.mentionQuery = '';
  }

  startMention(): void {
    if (this.chatMode !== 'channel') return;
    this.newMessage = this.newMessage.trimEnd();
    this.newMessage = this.newMessage ? `${this.newMessage} @` : '@';
    this.onMessageInputChange(this.newMessage);
  }

  private scrollToBottom(): void {
    setTimeout(() => {
      const el = document.getElementById('chatMessagesArea');
      if (el) el.scrollTop = el.scrollHeight;
    }, 100);
  }

  formatTime(iso: string): string {
    if (!iso) return '';
    const d = new Date(iso);
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
}
