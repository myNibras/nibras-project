import { ArticleCardComponent } from "../article-card/article-card.component";
import { Component, OnDestroy, OnInit } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { ArticlesService } from 'app/shared/services/articles/articles.service';
import { articlesItem } from 'app/shared/models/articles';
import { Subject, takeUntil } from 'rxjs';
import { NgFor, NgIf } from '@angular/common';
import { StorageService } from 'app/core/storage/storage.service';
import { BreadcrumbComponent } from 'app/shared/components/breadcrumb/breadcrumb.component';
import { Breadcrumb } from 'app/shared/components/breadcrumb/types/breadcrumb.types';

@Component({
  selector: 'app-all-articles',
  imports: [TranslateModule, ArticleCardComponent, NgFor, NgIf, BreadcrumbComponent],
  templateUrl: './all-articles.component.html',
  styleUrl: './all-articles.component.scss'
})
export class AllArticlesComponent implements OnInit, OnDestroy {

  articlesList: articlesItem[] = [];
  sectionTitle: string | null = null;
  loading = true;

  breadcrumb: Breadcrumb[] = [];

  destroy$ = new Subject<void>();


  constructor(
    private articlesService: ArticlesService,
    public storageService: StorageService,
  ) { }

  ngOnInit(): void {
    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe((lang) => {

        this.breadcrumb = [
          {
            title: 'Home',
            link: `/${lang}`
          },
          {
            title: 'Articles',
            link: ''   // last item not clickable
          }
        ];

        this.loadArticles();
      });
  }

  loadArticles() {
    this.loading = true;
    this.articlesList = [];
    this.sectionTitle = null;

    this.articlesService.getSection()
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (section) => {
          if (section) {
            this.sectionTitle = section.section_title || null;
            this.articlesList = Array.isArray(section.data) ? section.data : [];
          } else {
            this.articlesList = [];
          }
          this.loading = false;
        },
        error: () => {
          this.articlesList = [];
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
