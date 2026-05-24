import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { HttpResponse } from '@angular/common/http';

import { ApiService } from 'app/core/api/api.service';
import { FaqResponse, FaqSection } from 'app/shared/models/faq';

@Injectable({
  providedIn: 'root'
})
export class FaqService {

  constructor(private apiService: ApiService) { }

  getSection(limit?: number): Observable<FaqSection | null> {
    const params = new URLSearchParams({
      p: String(new Date().getTime())
    });

    if (limit && limit > 0) {
      params.set('limit', String(limit));
    }

    return this.apiService
      .get<FaqResponse>(`api/v1/faqs?${params.toString()}`)
      .pipe(
        map((httpResponse: HttpResponse<FaqResponse>) => {
          const response = httpResponse.body;
          return response?.status && response.data ? response.data : null;
        }),
        catchError(error => {
          console.error('Error fetching FAQ section:', error);
          return of(null);
        })
      );
  }
}
