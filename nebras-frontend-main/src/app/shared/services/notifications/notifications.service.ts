import { Injectable } from '@angular/core';
import { HttpResponse } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { catchError, map } from 'rxjs/operators';

import { ApiService } from 'app/core/api/api.service';
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import { ApiResponse } from 'app/shared/models/api-response';
import { StudentNotification } from 'app/shared/models/notification';

@Injectable({
  providedIn: 'root'
})
export class NotificationsService {
  constructor(
    private apiService: ApiService,
    private authService: AuthService
  ) {}

  getMyNotifications(): Observable<StudentNotification[]> {
    const token = this.authService.getToken();

    return this.apiService
      .get<ApiResponse<StudentNotification[]>>(
        `api/v1/profile/notifications?p=${Date.now()}`,
        {},
        { Authorization: `Bearer ${token}` }
      )
      .pipe(
        map((httpResponse: HttpResponse<ApiResponse<StudentNotification[]>>) => {
          const response = httpResponse.body;
          if (response?.status && response.data) {
            return Array.isArray(response.data) ? response.data : [];
          }
          return [];
        }),
        catchError((err) => {
          console.error('Error fetching notifications:', err);
          return of([]);
        })
      );
  }
}
