import { Component, OnInit, OnDestroy } from '@angular/core';
import { NewsCardComponent } from "./components/news-card/news-card.component";
import { TranslateModule } from '@ngx-translate/core';
import { NgFor, NgIf } from '@angular/common';
import { Subject, takeUntil } from 'rxjs';
import { NewsService } from 'app/shared/services/news/news.service';
import { newsItem } from 'app/shared/models/news';
import { StorageService } from 'app/core/storage/storage.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-news',
  standalone: true,
  imports: [NewsCardComponent, TranslateModule, NgFor, NgIf],
  templateUrl: './news.component.html',
  styleUrls: ['./news.component.scss']
})
export class NewsComponent implements OnInit, OnDestroy {

  newsList: newsItem[] = [];
  sectionTitle: string | null = null;
  sectionDescription: string | null = null;
  loading = true;
  destroy$ = new Subject<void>();

  constructor(
    private newsService: NewsService,
    public storageService: StorageService,
    private router: Router,
  ) { }

  newsRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/أخبارنا',
    en: 'en/our-news'
  };

  ngOnInit(): void {
    // Reload news whenever language changes
    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        this.loadNews();
      });

    // Initial load
    this.loadNews();
  }

  loadNews() {
    this.loading = true;
    this.sectionTitle = null;
    this.newsService.getSection()
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (section) => {
          if (section) {
            this.sectionTitle = section.section_title || null;
            this.sectionDescription = section.section_description || null;
            const data = Array.isArray(section.data) ? section.data : [];
            this.newsList = data.slice(0, 2);
          } else {
            this.newsList = [];
          }
          this.loading = false;
        },
        error: (err) => {
          console.error('Error loading news:', err);
          this.newsList = [];
          this.sectionTitle = null;
          this.loading = false;
        }
      });
  }

  goToNewsPage() {
    const lang = this.storageService.siteLanguage$.value as 'ar' | 'en';
    this.router.navigate([`/${this.newsRoutes[lang]}`]);
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
}
