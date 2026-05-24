import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { HttpResponse } from '@angular/common/http';

import { ApiService } from 'app/core/api/api.service';
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import { ApiResponse } from 'app/shared/models/api-response';
import { Testimonial } from 'app/shared/models/testimonial';

@Injectable({
  providedIn: 'root'
})
export class TestimonialsService {

  constructor(
    private apiService: ApiService,
    private authService: AuthService
  ) { }

  /** Get testimonials for a teacher (public). API: GET api/v1/testimonials?teacher_id=X */
  getByTeacherId(teacherId: number): Observable<Testimonial[]> {
    return this.apiService
      .get<ApiResponse<Testimonial[]>>(`api/v1/testimonials?teacher_id=${teacherId}&p=${Date.now()}`)
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<Testimonial[]>>) => {
          const response = httpResponse.body;
          if (response?.status && response.data) {
            return Array.isArray(response.data) ? response.data : [];
          }
          return [];
        }),
        catchError(err => {
          console.error('Error fetching teacher testimonials:', err);
          return of([]);
        })
      );
  }

  /** Get current student's testimonials (list) */
  getMyTestimonials(): Observable<Testimonial[]> {
    const token = this.authService.getToken();

    return this.apiService
      .get<ApiResponse<Testimonial[]>>(`api/v1/profile/testimonials?p=${Date.now()}`, {},
      { Authorization: `Bearer ${token}` })
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<Testimonial[]>>) => {
          const response = httpResponse.body;
          if (response?.status && response.data) {
            return Array.isArray(response.data) ? response.data : [];
          }
          return [];
        }),
        catchError(err => {
          console.error('Error fetching student testimonials:', err);
          return of([]);
        })
      );
  }
}
