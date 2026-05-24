import { ArticleCardComponent } from '../article-card/article-card.component';
import { TranslateModule } from '@ngx-translate/core';
import { StorageService } from 'app/core/storage/storage.service';
import { Router } from '@angular/router';
import { Component, OnInit, OnDestroy } from '@angular/core';
import { NgFor, NgIf } from '@angular/common';
import { Subject, takeUntil } from 'rxjs';
import { ArticlesService } from 'app/shared/services/articles/articles.service';
import { articlesItem } from 'app/shared/models/articles';

@Component({
  selector: 'app-articles-section',
  imports: [ArticleCardComponent, TranslateModule, NgFor, NgIf],
  templateUrl: './articles-section.component.html',
  styleUrl: './articles-section.component.scss'
})
export class ArticlesSectionComponent implements OnInit, OnDestroy {

  articlesList: articlesItem[] = [];
  sectionTitle: string | null = null;
  sectionDescription: string | null = null;
  loading = true;
  destroy$ = new Subject<void>();

  constructor(
    private articlesService: ArticlesService,
    public storageService: StorageService,
    private router: Router,
  ) { }

  articlesRoutes: Record<'ar' | 'en', string> = {
    ar: 'ar/مقالات',
    en: 'en/articles'
  };

  goToArticlesPage() {
    const lang = this.storageService.siteLanguage$.value as 'ar' | 'en';
    this.router.navigate([`/${this.articlesRoutes[lang]}`]);
  }
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
    this.sectionDescription = null;
    this.articlesService.getSection()
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (section) => {
          if (section) {
            this.sectionTitle = section.section_title || null;
            this.sectionDescription = section.section_description || null;
            const data = Array.isArray(section.data) ? section.data : [];
            this.articlesList = data.slice(0, 3);
          } else {
            this.articlesList = [];
          }
          this.loading = false;
        },
        error: (err) => {
          console.error('Error loading articles:', err);
          this.articlesList = [];
          this.sectionTitle = null;
          this.sectionDescription = null;
          this.loading = false;
        }
      });
  }


  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

}
