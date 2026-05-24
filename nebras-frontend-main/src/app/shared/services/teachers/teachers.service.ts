import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { HttpParams, HttpResponse } from '@angular/common/http';

import { ApiService } from 'app/core/api/api.service';
import { ApiResponse } from 'app/shared/models/api-response';
import { Teacher, TeachersSectionData } from 'app/shared/models/teachers';
import { Course } from 'app/shared/models/courses';

@Injectable({
  providedIn: 'root'
})
export class TeachersService {

  constructor(private apiService: ApiService) { }

  /** Get teachers list. API returns { data: { section_title, section_description, data: [] } }. */
  getAll(): Observable<Teacher[]> {
    return this.apiService
      .get<ApiResponse<TeachersSectionData>>(`api/v1/teachers?p=${Date.now()}`)
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<TeachersSectionData>>) => {
          const response = httpResponse.body;
          if (!response?.status || !response.data) return [];
          const section = response.data;
          return Array.isArray(section.data) ? section.data : [];
        }),
        catchError(error => {
          console.error('Error fetching teachers:', error);
          return of([]);
        })
      );
  }

  /** Get teachers section (title, description, items). Use when you need section_title from API. */
  getSection(): Observable<TeachersSectionData | null> {
    return this.apiService
      .get<ApiResponse<TeachersSectionData>>(`api/v1/teachers?p=${Date.now()}`)
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<TeachersSectionData>>) => {
          const response = httpResponse.body;
          return response?.status && response.data ? response.data : null;
        }),
        catchError(error => {
          console.error('Error fetching teachers section:', error);
          return of(null);
        })
      );
  }

  // ✅ Get Teacher By ID
  getById(id: number): Observable<Teacher | null> {
    return this.apiService
      .get<ApiResponse<Teacher>>(`api/v1/teachers/${id}?p=${Date.now()}`)
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<Teacher>>) => {
          const response = httpResponse.body;
          return response?.status && response.data
            ? response.data
            : null;
        }),
        catchError(error => {
          console.error('Error fetching teacher by id:', error);
          return of(null);
        })
      );
  }

  // ✅ Get Courses By Teacher ID
  getCoursesByTeacherId(teacherId: number): Observable<Course[]> {
    return this.apiService
      .get<ApiResponse<Course[]>>(
        `api/v1/courses?teacher_id=${teacherId}&p=${Date.now()}`
      )
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<Course[]>>) => {
          const response = httpResponse.body;
          return response?.status && response.data ? response.data : [];
        }),
        catchError(error => {
          console.error('Error fetching teacher courses:', error);
          return of([]);
        })
      );
  }

  /**
   * Filter teachers by multiple courses and/or grades (class ids).
   * Sends Laravel-style array query params: course_id[]=1&course_id[]=2&class_id[]=3
   */
  getFilteredTeachers(
    courseIds?: number[],
    classIds?: number[],
  ): Observable<Teacher[]> {
    let httpParams = new HttpParams().set('p', String(Date.now()));

    courseIds?.forEach((id) => {
      httpParams = httpParams.append('course_id[]', String(id));
    });

    classIds?.forEach((id) => {
      httpParams = httpParams.append('class_id[]', String(id));
    });

    return this.apiService
      .get<ApiResponse<TeachersSectionData>>('api/v1/teachers', httpParams)
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<TeachersSectionData>>) => {
          const response = httpResponse.body;
          if (!response?.status || !response.data) return [];
          const section = response.data;
          return Array.isArray(section.data) ? section.data : [];
        }),
        catchError(error => {
          console.error('Error filtering teachers:', error);
          return of([]);
        })
      );
  }



}
