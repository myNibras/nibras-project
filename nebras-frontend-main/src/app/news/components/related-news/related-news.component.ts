import { Component, Input, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Subject, takeUntil } from 'rxjs';
import { TranslateModule } from '@ngx-translate/core';
import { NewsService } from 'app/shared/services/news/news.service';
import { StorageService } from 'app/core/storage/storage.service';
import { newsItem } from 'app/shared/models/news';
import { NewsCardComponent } from '../news-card/news-card.component';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-related-news',
  standalone: true,
  imports: [CommonModule, NewsCardComponent, TranslateModule, NgIf],
  templateUrl: './related-news.component.html',
  styleUrl: './related-news.component.scss'
})
export class RelatedNewsComponent implements OnInit, OnDestroy {

  @Input() newsId!: number;

  relatedNews: newsItem[] = [];
  loading = true;

  private destroy$ = new Subject<void>();

  constructor(
    private newsService: NewsService,
    private storageService: StorageService
  ) { }

  ngOnInit(): void {
    if (!this.newsId) return;

    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        this.loadRelatedNews();
      });

    // initial load
    this.loadRelatedNews();
  }

  private loadRelatedNews(): void {
    this.loading = true;

    this.newsService.getRelatedNews(this.newsId)
      .pipe(takeUntil(this.destroy$))
      .subscribe(news => {
        this.relatedNews = news.slice(0, 3);
        this.loading = false;
      });
  }


  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
}
