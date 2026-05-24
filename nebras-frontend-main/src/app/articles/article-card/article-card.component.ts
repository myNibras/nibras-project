import { TranslateModule } from '@ngx-translate/core';
import { StorageService } from 'app/core/storage/storage.service';
import { Router } from '@angular/router';
import { Component, Input, OnInit } from '@angular/core';
import { articlesItem } from 'app/shared/models/articles';
import { RouterModule } from '@angular/router';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-article-card',
  imports: [TranslateModule, RouterModule, NgIf],
  templateUrl: './article-card.component.html',
  styleUrl: './article-card.component.scss'
})
export class ArticleCardComponent implements OnInit {

  constructor(
    public storageService: StorageService,
    private router: Router,
  ) { }

  @Input() article?: articlesItem;

  formattedCreatedAt!: string;

  ngOnInit() {
    if (this.article?.created_at) {
      this.formattedCreatedAt = this.formatDate(this.article.created_at);
    }
  }

  formatDate(date: Date | string): string {
    const d = date instanceof Date ? date : new Date(date);
    return d.toISOString().split('T')[0];
  }

  articleRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/تفاصيل-المقال',
    en: 'en/article-details'
  };

  goToArticalePage() {
    const lang = this.storageService.siteLanguage$.value as 'ar' | 'en';
    this.router.navigate([`/${this.articleRoutes[lang]}`]);
  }
}
