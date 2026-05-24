import { NgFor } from '@angular/common';
import { Component } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';

@Component({
  selector: 'app-group-chats',
  imports: [TranslateModule, NgFor],
  templateUrl: './group-chats.component.html',
  styleUrl: './group-chats.component.scss'
})
export class GroupChatsComponent {

  groupChats = [
    {
      subject: 'اللغة العربية',
      date: '2025.12.12 14:45:00'
    },
    {
      subject: 'اللغة الإنجليزية',
      date: '2025.12.12 14:45:00'
    },
    {
      subject: 'الرياضيات',
      date: '2025.12.12 14:45:00'
    }
  ];

}
