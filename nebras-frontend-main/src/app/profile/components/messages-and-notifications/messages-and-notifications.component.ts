import { NgFor, NgIf } from '@angular/common';
import { Component, OnInit, inject } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { NotificationsService } from 'app/shared/services/notifications/notifications.service';
import { StudentNotification } from 'app/shared/models/notification';

@Component({
  selector: 'app-messages-and-notifications',
  imports: [TranslateModule, NgFor, NgIf],
  templateUrl: './messages-and-notifications.component.html',
  styleUrl: './messages-and-notifications.component.scss'
})
export class MessagesAndNotificationsComponent implements OnInit {
  private readonly notificationsService = inject(NotificationsService);

  openedIndex: number | null = null;
  loading = true;

  notifications: StudentNotification[] = [];

  ngOnInit(): void {    
    this.notificationsService.getMyNotifications().subscribe({
      next: (list) => {        
        this.notifications = list;
        this.loading = false;        
      },
      error: () => {
        this.notifications = [];
        this.loading = false;
      }
    });
  }

  toggleMessage(index: number) {
    this.openedIndex = this.openedIndex === index ? null : index;
  }

  formatDate(dateStr: string): string {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString(undefined, {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: false
    });
  }
}
