import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { HttpResponse } from '@angular/common/http';
import { ContactRequest } from 'app/shared/models/contact-us';
import { ApiService } from 'app/core/api/api.service';
import { ApiResponse } from 'app/shared/models/api-response';


@Injectable({
  providedIn: 'root'
})
export class ContactService {

  constructor(private apiService: ApiService) { }

  sendContact(body: ContactRequest): Observable<ApiResponse<null>> {
    return this.apiService
      .post<ApiResponse<null>>(
        'api/v1/contact',
        JSON.stringify(body)
      )
      .pipe(
        map((res: HttpResponse<ApiResponse<null>>) => res.body as ApiResponse<null>)
      );
  }
}
