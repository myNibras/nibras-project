import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { ApiService } from 'app/core/api/api.service';
import { AcademicLevelsResponse } from 'app/shared/models/academic-levels';
import { ApiResponse } from 'app/shared/models/api-response';
import { HttpResponse } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class AcademicLevelsService {

  constructor(private apiService: ApiService) { }

  get(): Observable<AcademicLevelsResponse> {
    return this.apiService.get<ApiResponse<AcademicLevelsResponse>>(`api/v1/academic-levels?p=${new Date().getTime()}`, {})
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<AcademicLevelsResponse>>) => {
          const response = httpResponse.body;
          if (response?.status && response.data) {
            return response.data;
          }
          throw new Error('Invalid API response');
        }),
        catchError(error => {
          console.error('Error fetching academic levels:', error);
          return of({ section_title: '', section_description: '', data: [] });
        })
      );
  }

}
