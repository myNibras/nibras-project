import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { HttpClient, HttpResponse } from '@angular/common/http';
import { ApiResponse } from 'app/shared/models/api-response';
import { environment } from 'environments/environment';


@Injectable({
  providedIn: 'root'
})
export class CareersService {

  private baseUrl = environment.apiUrl;

  constructor(private http: HttpClient) { }

  sendContact(formData: FormData): Observable<HttpResponse<ApiResponse<null>>> {
    return this.http.post<ApiResponse<null>>(
      `${this.baseUrl}api/v1/candidates`,
      formData,
      {
        observe: 'response'
      }
    );
  }
}