import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { ApiService } from 'app/core/api/api.service';
import { HomeSlider } from 'app/shared/models/home-slider';
import { ApiResponse } from 'app/shared/models/api-response';
import { HttpResponse } from '@angular/common/http';


@Injectable({
  providedIn: 'root'
})
export class HomeSliderService {

  constructor(private apiService: ApiService) {}

  get(): Observable<HomeSlider[]> {
    return this.apiService.get<ApiResponse<HomeSlider[]>>(`api/v1/home-sliders?p=${new Date().getTime()}`, {})
    .pipe(
      map((httpResponse: HttpResponse<ApiResponse<HomeSlider[]>>) => {
        const response = httpResponse.body;
        if (response?.status && response.data) {
          return response.data;
        }
        return [];
      }),
      catchError(error => {
        console.error('Error fetching home sliders:', error);
        return of([]);
      })
    );
  }
  
}
