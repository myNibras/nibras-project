import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { CommonModule } from '@angular/common';
import { Subject, takeUntil } from 'rxjs';
import { RelatedNewsComponent } from '../related-news/related-news.component';
import { IsolateContentDirective } from 'app/shared/directives/isolate-content.directive';
import { NewsService } from 'app/shared/services/news/news.service';
import { StorageService } from 'app/core/storage/storage.service';
import { newsItem } from 'app/shared/models/news';
import { NgIf } from '@angular/common';
import { BreadcrumbComponent } from 'app/shared/components/breadcrumb/breadcrumb.component';
import { Breadcrumb } from 'app/shared/components/breadcrumb/types/breadcrumb.types';

@Component({
  selector: 'app-news-details',
  standalone: true,
  imports: [CommonModule, RelatedNewsComponent, NgIf, IsolateContentDirective, BreadcrumbComponent],
  templateUrl: './news-details.component.html',
  styleUrls: ['./news-details.component.scss']
})
export class NewsDetailsComponent implements OnInit, OnDestroy {

  news: newsItem | null = null;
  loading = false;
  formattedCreatedAt!: string;

  private destroy$ = new Subject<void>();
  private newsId!: number;

  breadcrumb: Breadcrumb[] = [];

  constructor(
    private route: ActivatedRoute,
    private newsService: NewsService,
    public storageService: StorageService
  ) { }

  ngOnInit(): void {
    this.route.paramMap
      .pipe(takeUntil(this.destroy$))
      .subscribe(params => {
        const id = Number(params.get('id'));
        if (!isNaN(id)) {
          this.newsId = id;
          this.fetchNews();
        }
      });

    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        if (this.newsId != null) {
          this.fetchNews();
        }
      });
  }

  formatDate(date: Date | string): string {
    const d = date instanceof Date ? date : new Date(date);
    return d.toISOString().split('T')[0];
  }

  private fetchNews(): void {
    if (isNaN(this.newsId)) return;

    this.loading = true;

    this.newsService.getById(this.newsId)
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: news => {
          this.news = news;

          if (news?.created_at) {
            this.formattedCreatedAt = this.formatDate(news.created_at);
          }

          this.buildBreadcrumb();

          this.loading = false;
        },
        error: () => {
          this.news = null;
          this.loading = false;
        }
      });
  }

  private buildBreadcrumb(): void {
    if (!this.news) return;

    const lang = this.storageService.siteLanguage$.value;

    const newsLink = lang === 'ar'
      ? '/ar/أخبارنا'
      : '/en/our-news';

    this.breadcrumb = [
      {
        title: 'Home',
        link: `/${lang}`
      },
      {
        title: 'Our News',
        link: newsLink
      },
      {
        title: this.news.title,
        link: '' // last item not clickable
      }
    ];
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
}
