import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { HttpResponse } from '@angular/common/http';

import { ApiService } from 'app/core/api/api.service';
import { newsItem, newsResponse, NewsSectionData } from 'app/shared/models/news';
import { ApiResponse } from 'app/shared/models/api-response';

@Injectable({
  providedIn: 'root'
})
export class NewsService {
  constructor(private apiService: ApiService) { }

  /** Get news list. API returns { data: { section_title, section_description, data: [] } }. */
  getAll(): Observable<newsItem[]> {
    return this.apiService
      .get<ApiResponse<NewsSectionData>>(`api/v1/news?p=${Date.now()}`)
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<NewsSectionData>>) => {
          const response = httpResponse.body;
          if (!response?.status || !response.data) return [];
          const section = response.data;
          return Array.isArray(section.data) ? section.data : [];
        }),
        catchError(error => {
          console.error('Error fetching news:', error);
          return of([]);
        })
      );
  }

  /** Get news section (title, description, items). Use when you need section_title from API. */
  getSection(): Observable<NewsSectionData | null> {
    return this.apiService
      .get<ApiResponse<NewsSectionData>>(`api/v1/news?p=${Date.now()}`)
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<NewsSectionData>>) => {
          const response = httpResponse.body;
          return response?.status && response.data ? response.data : null;
        }),
        catchError(error => {
          console.error('Error fetching news section:', error);
          return of(null);
        })
      );
  }

  getById(id: number): Observable<newsItem | null> {
    return this.apiService
      .get<newsResponse>(`api/v1/news/${id}?p=${Date.now()}`)
      .pipe(
        map((httpResponse: HttpResponse<newsResponse>) => {
          const response = httpResponse.body;
          if (!response?.status || !response.data) return null;

          return Array.isArray(response.data)
            ? response.data[0]
            : response.data;
        }),
        catchError(error => {
          console.error('Error fetching news by id:', error);
          return of(null);
        })
      );
  }

  // ✅ NEW: Related news
  getRelatedNews(newsId: number): Observable<newsItem[]> {
    return this.apiService
      .get<ApiResponse<newsItem[]>>(
        `api/v1/news/related-news?id=${newsId}`
      )
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<newsItem[]>>) => {
          const response = httpResponse.body;
          return response?.status && response.data ? response.data : [];
        }),
        catchError(error => {
          console.error('Error fetching related news:', error);
          return of([]);
        })
      );
  }
}

