import { Component, Input, OnInit } from '@angular/core';
import { newsItem } from 'app/shared/models/news';
import { TranslateModule } from '@ngx-translate/core';
import { RouterModule } from '@angular/router';
import { StorageService } from 'app/core/storage/storage.service';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-news-card',
  imports: [TranslateModule, RouterModule, NgIf],
  templateUrl: './news-card.component.html',
  styleUrl: './news-card.component.scss'
})
export class NewsCardComponent implements OnInit {

  @Input() news?: newsItem;

  formattedCreatedAt!: string;

  constructor(public storageService: StorageService) { }

  ngOnInit() {
    if (this.news?.created_at) {
      this.formattedCreatedAt = this.formatDate(this.news.created_at);
    }
  }

  formatDate(date: Date | string): string {
    const d = date instanceof Date ? date : new Date(date);
    return d.toISOString().split('T')[0];
  }
}
