import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { HttpResponse } from '@angular/common/http';

import { ApiService } from 'app/core/api/api.service';
import { articlesResponse, articlesItem, ArticlesSectionData } from 'app/shared/models/articles';
import { ApiResponse } from 'app/shared/models/api-response';

@Injectable({
  providedIn: 'root'
})
export class ArticlesService {
  constructor(private apiService: ApiService) { }

  /** Get articles list. API returns { data: { section_title, section_description, data: [] } }. */
  getAll(): Observable<articlesItem[]> {
    return this.apiService
      .get<ApiResponse<ArticlesSectionData>>(`api/v1/articles?p=${Date.now()}`)
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<ArticlesSectionData>>) => {
          const response = httpResponse.body;
          if (!response?.status || !response.data) return [];
          const section = response.data;
          return Array.isArray(section.data) ? section.data : [];
        }),
        catchError(error => {
          console.error('Error fetching articles:', error);
          return of([]);
        })
      );
  }

  /** Get articles section (title, description, items). Use when you need section_title from API. */
  getSection(): Observable<ArticlesSectionData | null> {
    return this.apiService
      .get<ApiResponse<ArticlesSectionData>>(`api/v1/articles?p=${Date.now()}`)
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<ArticlesSectionData>>) => {
          const response = httpResponse.body;
          return response?.status && response.data ? response.data : null;
        }),
        catchError(error => {
          console.error('Error fetching articles section:', error);
          return of(null);
        })
      );
  }

  getById(id: number): Observable<articlesItem | null> {
    return this.apiService
      .get<articlesResponse>(`api/v1/articles/${id}?p=${Date.now()}`)
      .pipe(
        map((httpResponse: HttpResponse<articlesResponse>) => {
          const response = httpResponse.body;
          if (!response?.status || !response.data) return null;

          return Array.isArray(response.data)
            ? response.data[0]
            : response.data;
        }),
        catchError(error => {
          console.error('Error fetching article by id:', error);
          return of(null);
        })
      );
  }

  // ✅ NEW: Related news
  getRelatedNews(newsId: number): Observable<articlesItem[]> {
    return this.apiService
      .get<ApiResponse<articlesItem[]>>(
        `api/v1/articles/related-articles?id=${newsId}?p=${Date.now()}`
      )
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<articlesItem[]>>) => {
          const response = httpResponse.body;
          return response?.status && response.data ? response.data : [];
        }),
        catchError(error => {
          console.error('Error fetching related articles:', error);
          return of([]);
        })
      );
  }
}