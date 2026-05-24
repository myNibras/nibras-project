import { Component, OnDestroy, OnInit } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { NewsService } from 'app/shared/services/news/news.service';
import { newsItem } from 'app/shared/models/news';
import { NewsCardComponent } from "../news-card/news-card.component";
import { Subject, takeUntil } from 'rxjs';
import { NgFor, NgClass, NgIf } from '@angular/common';
import { StorageService } from 'app/core/storage/storage.service';
import { BreadcrumbComponent } from 'app/shared/components/breadcrumb/breadcrumb.component';
import { Breadcrumb } from 'app/shared/components/breadcrumb/types/breadcrumb.types';

@Component({
  selector: 'app-all-news',
  standalone: true,
  imports: [TranslateModule, NewsCardComponent, NgFor, NgClass, NgIf, BreadcrumbComponent],
  templateUrl: './all-news.component.html',
  styleUrls: ['./all-news.component.scss']
})
export class AllNewsComponent implements OnInit, OnDestroy {
  newsList: newsItem[] = [];
  sectionTitle: string | null = null;
  loading = true;

  breadcrumb: Breadcrumb[] = [];

  destroy$ = new Subject<void>();


  constructor(
    private newsService: NewsService,
    public storageService: StorageService,
  ) { }

  ngOnInit(): void {
    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        this.loadNews();
      });
  }

  loadNews() {
    this.loading = true;
    this.newsList = [];
    this.sectionTitle = null;

    this.newsService.getSection()
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (section) => {

          const lang = this.storageService.siteLanguage$.value;

          // Build breadcrumb
          this.breadcrumb = [
            {
              title: 'Home',
              link: `/${lang}`
            },
            {
              title: 'Our News',
              link: '' // last item not clickable
            }
          ];

          if (section) {
            this.sectionTitle = section.section_title || null;
            this.newsList = Array.isArray(section.data) ? section.data : [];
          } else {
            this.newsList = [];
          }

          this.loading = false;
        },
        error: () => {
          this.newsList = [];
          this.sectionTitle = null;
          this.loading = false;
        }
      });
  }



  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
}
