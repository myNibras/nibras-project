import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { ApiService } from 'app/core/api/api.service';
import { Course } from 'app/shared/models/courses';
import { ApiResponse } from 'app/shared/models/api-response';
import { HttpParams, HttpResponse } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class CoursesService {
  constructor(private apiService: ApiService) { }

  // Get all courses
  get(): Observable<Course[]> {
    return this.apiService.get<ApiResponse<Course[]>>(`api/v1/courses?p=${new Date().getTime()}`, {}).pipe(
      map((httpResponse: HttpResponse<ApiResponse<Course[]>>) => {
        const response = httpResponse.body;
        if (response?.status && response.data) {
          return response.data;
        }
        throw new Error('Invalid API response');
      }),
      catchError(error => {
        console.error('Error fetching courses:', error);
        return of([]);
      })
    );
  }

  /**
   * List courses with optional multi-filters (Laravel-style): course_id[]=1&class_id[]=2
   */
  getFiltered(courseIds?: number[], classIds?: number[]): Observable<Course[]> {
    let httpParams = new HttpParams().set('p', String(Date.now()));
    courseIds?.forEach((id) => {
      httpParams = httpParams.append('course_id[]', String(id));
    });
    classIds?.forEach((id) => {
      httpParams = httpParams.append('class_id[]', String(id));
    });

    return this.apiService.get<ApiResponse<Course[]>>('api/v1/courses', httpParams).pipe(
      map((httpResponse: HttpResponse<ApiResponse<Course[]>>) => {
        const response = httpResponse.body;
        if (response?.status && response.data) {
          return response.data;
        }
        throw new Error('Invalid API response');
      }),
      catchError(error => {
        console.error('Error fetching filtered courses:', error);
        return of([]);
      })
    );
  }

  // Get courses by academic level slug
  getByLevelSlug(slug: string): Observable<Course[]> {
    return this.apiService.get<ApiResponse<Course[]>>(`api/v1/courses?p=${new Date().getTime()}`, {
      academic_level_slug: slug,
    }).pipe(
      map((httpResponse: HttpResponse<ApiResponse<Course[]>>) => {
        const response = httpResponse.body;
        if (response?.status && response.data) {
          return response.data;
        }
        throw new Error('Invalid API response');
      }),
      catchError(error => {
        console.error('Error fetching courses by level slug:', error);
        return of([]);
      })
    );
  }

  // Get a single course by ID (for enrolled students viewing their materials)
  getCourseById(courseId: number, authToken?: string | null): Observable<{ course: Course; related_courses: Course[] } | null> {
    const headers: Record<string, string> = {};
    if (authToken) {
      headers['Authorization'] = `Bearer ${authToken}`;
    }
    return this.apiService.get<ApiResponse<{ course: Course; related_courses: Course[] }>>(
      `api/v1/courses/${courseId}?p=${new Date().getTime()}`,
      {},
      headers
    ).pipe(
      map((httpResponse: HttpResponse<ApiResponse<{ course: Course; related_courses: Course[] }>>) => {
        const response = httpResponse.body;
        if (response?.status && response.data) {
          return response.data;
        }
        return null;
      }),
      catchError(error => {
        console.error('Error fetching course by id:', error);
        return of(null);
      })
    );
  }

  // Get a single course by levelSlug + courseSlug + courseId
  getCourseBySlug(
    levelSlug: string,
    courseSlug: string,
    courseId: string,
    authToken?: string | null
  ): Observable<{ course: Course; related_courses: Course[] } | null> {
    const headers: Record<string, string> = {};
    if (authToken) {
      headers['Authorization'] = `Bearer ${authToken}`;
    }
    return this.apiService.get<ApiResponse<{ course: Course; related_courses: Course[] }>>(
      `api/v1/courses/${levelSlug}/${courseSlug}/${courseId}?p=${new Date().getTime()}`,
      {},
      headers
    ).pipe(
      map((httpResponse: HttpResponse<ApiResponse<{ course: Course, related_courses: Course[] }>>) => {
        const response = httpResponse.body;
        if (response?.status && response.data) {
          return response.data; // ✅ contains both course + related_courses
        }
        return null;
      }),
      catchError(error => {
        console.error('Error fetching course by slug:', error);
        return of(null);
      })
    );
  }
  getRelatedCourses(): Observable<Course[]> {
    return this.apiService.get<ApiResponse<{ data: Course[] }>>(`api/v1/courses/related-courses?p=${new Date().getTime()}`, {}).pipe(
      map((httpResponse: HttpResponse<ApiResponse<{ data: Course[] }>>) => {
        const response = httpResponse.body;
        if (response?.status && response.data?.data) {
          return response.data.data;   // ✅ unwrap properly
        }
        throw new Error('Invalid API response');
      }),
      catchError(error => {
        console.error('Error fetching courses:', error);
        return of([] as Course[]);
      })
    );
  }

}
