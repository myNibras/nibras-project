import { Component, Input, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Subject, takeUntil } from 'rxjs';
import { TranslateModule } from '@ngx-translate/core';
import { StorageService } from 'app/core/storage/storage.service';
import { ArticlesService } from 'app/shared/services/articles/articles.service';
import { articlesItem } from 'app/shared/models/articles';
import { ArticleCardComponent } from '../article-card/article-card.component';


@Component({
  selector: 'app-related-articles',
  imports: [CommonModule, ArticleCardComponent, TranslateModule],
  templateUrl: './related-articles.component.html',
  styleUrl: './related-articles.component.scss'
})
export class RelatedArticlesComponent implements OnInit, OnDestroy {

  @Input() articleId!: number;

  relatedArticles: articlesItem[] = [];
  loading = true;

  private destroy$ = new Subject<void>();

  constructor(
    private articleService: ArticlesService,
    private storageService: StorageService
  ) { }

  ngOnInit(): void {
    if (!this.articleId) return;

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

    this.articleService.getRelatedNews(this.articleId)
      .pipe(takeUntil(this.destroy$))
      .subscribe(articles => {
        this.relatedArticles = articles.slice(0, 3);
        this.loading = false;
      });
  }


  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

}
