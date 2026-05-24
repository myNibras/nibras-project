import { Component, HostListener, inject, signal } from '@angular/core';
import { CommonModule, DatePipe } from '@angular/common';
import { Router } from '@angular/router';
import { TranslateModule } from '@ngx-translate/core';
import { ChatNotificationsService } from 'app/shared/services/chat-notifications/chat-notifications.service';
import { ChatNotificationItem } from 'app/shared/models/chat-notification.model';
import { StorageService } from 'app/core/storage/storage.service';

@Component({
  selector: 'app-chat-notifications-bell',
  standalone: true,
  imports: [CommonModule, DatePipe, TranslateModule],
  templateUrl: './chat-notifications-bell.component.html',
  styleUrl: './chat-notifications-bell.component.scss',
})
export class ChatNotificationsBellComponent {
  private readonly service = inject(ChatNotificationsService);
  private readonly router = inject(Router);
  private readonly storage = inject(StorageService);

  readonly count = this.service.count;
  readonly recent$ = this.service.recent$;
  readonly open = signal<boolean>(false);

  toggle(): void {
    const next = !this.open();
    this.open.set(next);
    if (next) {
      this.service.loadRecent().subscribe();
    }
  }

  goToChat(item: ChatNotificationItem): void {
    this.open.set(false);
    const lang = this.storage.siteLanguage$.value === 'ar' ? 'ar' : 'en';
    const slug = lang === 'ar' ? (item.course_slug ?? item.course_slug_en) : (item.course_slug_en ?? item.course_slug);
    if (!slug) return;
    const segment = lang === 'ar' ? 'تفاصيل-مادة-الطالب' : 'student-subject-details';
    const mode = item.thread_type === 'direct' ? 'direct' : 'channel';
    this.router.navigate(
      ['/' + lang, segment, slug, item.course_id],
      { queryParams: { mode } }
    );
  }

  @HostListener('document:click', ['$event'])
  closeOnOutsideClick(event: MouseEvent): void {
    const target = event.target as HTMLElement | null;
    if (!target?.closest('app-chat-notifications-bell')) {
      this.open.set(false);
    }
  }
}
