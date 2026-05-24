import { Injectable } from '@angular/core';
import { Observable, of, throwError } from 'rxjs';
import { map, catchError, switchMap } from 'rxjs/operators';
import { ApiService } from 'app/core/api/api.service';
import { TestimonialsSection } from 'app/shared/models/testimonial';
import { ApiResponse } from 'app/shared/models/api-response';
import { HttpResponse } from '@angular/common/http';
import { AuthService } from '../auth-service/auth-service.service';

@Injectable({
  providedIn: 'root'
})
export class TestimonialService {

  constructor(private apiService: ApiService, private authService: AuthService) { }

  get(): Observable<TestimonialsSection> {
    return this.apiService
      .get<ApiResponse<TestimonialsSection>>(`api/v1/testimonials?p=${new Date().getTime()}`, {})
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<TestimonialsSection>>) => {
          const response = httpResponse.body;
          if (response?.status && response.data) {
            return response.data;
          }
          return { section_title: '', section_description: '', data: [] };
        }),
        catchError(error => {
          console.error('Error fetching testimonials:', error);
          return of({ section_title: '', section_description: '', data: [] });
        })
      );
  }

  /** Get testimonials for a teacher. API: GET api/v1/testimonials?teacher_id=X */
  getByTeacherId(teacherId: number): Observable<TestimonialsSection> {
    return this.apiService
      .get<ApiResponse<TestimonialsSection>>(`api/v1/testimonials?teacher_id=${teacherId}&p=${new Date().getTime()}`, {})
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<TestimonialsSection>>) => {
          const response = httpResponse.body;
          if (response?.status && response.data) {
            return response.data;
          }
          return { section_title: '', section_description: '', data: [] };
        }),
        catchError(err => {
          console.error('Error fetching teacher testimonials:', err);
          return of({ section_title: '', section_description: '', data: [] });
        })
      );
  }

  create(body: any): Observable<boolean> {

    const token = this.authService.getToken();

    return this.apiService.post<ApiResponse<any>>(
      'api/v1/profile/testimonials',
      body,
      { Authorization: `Bearer ${token}` }
    ).pipe(
      switchMap((res: HttpResponse<ApiResponse<any>>) => {
        const b = res.body;
        if (b?.status) {
          return of(true);
        }
        return throwError(() => b ?? { status: false, message: 'Request failed' });
      })
    );

  }
}
