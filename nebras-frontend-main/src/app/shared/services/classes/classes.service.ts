import { Injectable } from '@angular/core';
import { HttpResponse } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { ApiService } from 'app/core/api/api.service';
import { ApiResponse } from 'app/shared/models/api-response';
import { Grade } from 'app/shared/models/classes';

@Injectable({
  providedIn: 'root'
})
export class ClassesService {

  constructor(private apiService: ApiService) { }

  get(): Observable<Grade[]> {
    return this.apiService.get<ApiResponse<Grade[]>>(`api/v1/classes?p=${new Date().getTime()}`, {})
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<Grade[]>>) => {
          const response = httpResponse.body;
          if (response?.status && response.data) {
            return response.data;
          }
          return [];
        }),
        catchError(error => {
          console.error('Error fetching classes:', error);
          return of([]);
        })
      );
  }
}
