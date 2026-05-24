import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { CommonModule, NgFor, NgIf } from '@angular/common';
import { Subject, takeUntil } from 'rxjs';
import { RelatedArticlesComponent } from '../related-articles/related-articles.component';
import { IsolateContentDirective } from 'app/shared/directives/isolate-content.directive';
import { ArticlesService } from 'app/shared/services/articles/articles.service';
import { StorageService } from 'app/core/storage/storage.service';
import { articlesItem } from 'app/shared/models/articles';
import { BreadcrumbComponent } from 'app/shared/components/breadcrumb/breadcrumb.component';
import { Breadcrumb } from 'app/shared/components/breadcrumb/types/breadcrumb.types';

@Component({
  selector: 'app-article-details',
  imports: [CommonModule, RelatedArticlesComponent, NgIf, IsolateContentDirective, BreadcrumbComponent, NgFor],
  templateUrl: './article-details.component.html',
  styleUrl: './article-details.component.scss'
})
export class ArticleDetailsComponent implements OnInit, OnDestroy {
  article: articlesItem | null = null;
  loading = false;
  formattedCreatedAt!: string;

  breadcrumb: Breadcrumb[] = [];

  private destroy$ = new Subject<void>();
  private articleId!: number;

  constructor(
    private route: ActivatedRoute,
    private articleService: ArticlesService,
    public storageService: StorageService
  ) { }

  ngOnInit(): void {
    // Subscribe to route parameter changes
    this.route.paramMap
      .pipe(takeUntil(this.destroy$))
      .subscribe(params => {
        const id = Number(params.get('id'));
        if (!isNaN(id)) {
          this.articleId = id;
          this.fetchArticles();
        }
      });

    // Subscribe to language changes
    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        if (this.articleId) {
          this.fetchArticles();
        }
      });
  }

  formatDate(date: Date | string): string {
    const d = date instanceof Date ? date : new Date(date);
    return d.toISOString().split('T')[0];
  }

  private fetchArticles(): void {
    if (isNaN(this.articleId)) return;

    this.loading = true;

    this.articleService.getById(this.articleId)
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: article => {
          this.article = article;

          if (article?.created_at) {
            this.formattedCreatedAt = this.formatDate(article.created_at);
          }

          this.buildBreadcrumb();

          this.loading = false;
        },
        error: () => {
          this.article = null;
          this.loading = false;
        }
      });
  }
  private buildBreadcrumb(): void {
    if (!this.article) return;

    const lang = this.storageService.siteLanguage$.value;

    const articlesLink = lang === 'ar'
      ? '/ar/مقالات'
      : '/en/articles';

    this.breadcrumb = [
      {
        title: 'Home',
        link: `/${lang}`
      },
      {
        title: 'Articles',
        link: articlesLink
      },
      {
        title: this.article.title,
        link: '' // last item not clickable
      }
    ];
  }


  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
}
