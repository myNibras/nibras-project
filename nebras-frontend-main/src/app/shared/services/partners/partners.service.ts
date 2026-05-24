import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { ApiService } from 'app/core/api/api.service';
import { PartnersSection } from 'app/shared/models/partners';
import { ApiResponse } from 'app/shared/models/api-response';
import { HttpResponse } from '@angular/common/http';


@Injectable({
  providedIn: 'root'
})
export class PartnersService {

  constructor(private apiService: ApiService) { }

  get(): Observable<PartnersSection | null> {
  return this.apiService
    .get<ApiResponse<PartnersSection>>(`api/v1/partners?p=${new Date().getTime()}`, {})
    .pipe(
      map((httpResponse: HttpResponse<ApiResponse<PartnersSection>>) => {
        const response = httpResponse.body;
        if (response?.status && response.data) {
          return response.data;
        }
        throw new Error('Invalid API response');
      }),
      catchError(error => {
        console.error('Error fetching partners:', error);
        return of(null);
      })
    );
}


}
