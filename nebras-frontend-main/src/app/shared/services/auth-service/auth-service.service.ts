import { Injectable, signal } from '@angular/core';
import { BehaviorSubject, Observable, map, tap } from 'rxjs';
import { HttpResponse } from '@angular/common/http';
import { CookieService } from 'ngx-cookie-service';
import { ApiService } from 'app/core/api/api.service';
import { VerifyOtpResponse, ResetPasswordResponse, LoginResponse, RegisterResponse, Student, SendOtpResponse, ProfileResponse, ChangePasswordResponse } from 'app/shared/models/auth';
import { PurchasedCoursesResponse } from 'app/shared/models/courses';
import { ProfilePaymentItem, ProfilePaymentsResponse, UnpaidInvoicesNextTwoDaysData, UnpaidInvoicesNextTwoDaysResponse } from 'app/shared/models/payment.model';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  private loginSuccessSubject = new BehaviorSubject<boolean>(false);
  loginSuccess$ = this.loginSuccessSubject.asObservable();
  private registerPopupSubject = new BehaviorSubject<boolean>(false);
  registerPopup$ = this.registerPopupSubject.asObservable();

  // Reactive auth state — signal so templates re-render when login/logout happens.
  // Exposed as a readonly signal; callers keep using `authService.isLoggedIn()`.
  private _isLoggedIn = signal<boolean>(false);
  isLoggedIn = this._isLoggedIn.asReadonly();

  constructor(
    private apiService: ApiService,
    private cookieService: CookieService
  ) {
    this._isLoggedIn.set(this.cookieService.check('token'));
  }

  login(body: any): Observable<LoginResponse> {
    return this.apiService.post<LoginResponse>("api/v1/login", body).pipe(
      map((res: HttpResponse<LoginResponse>) => res.body as LoginResponse),
      tap((response: LoginResponse) => {
        return response;
      })
    );
  }

  loginWithGoogle(accessToken: string): Observable<LoginResponse> {
    return this.apiService.post<LoginResponse>("api/v1/auth/google", JSON.stringify({ access_token: accessToken })).pipe(
      map((res: HttpResponse<LoginResponse>) => res.body as LoginResponse),
      tap((response: LoginResponse) => response)
    );
  }


  register(body: any): Observable<RegisterResponse> {
    return this.apiService.post<RegisterResponse>("api/v1/register", body).pipe(
      map((res: HttpResponse<RegisterResponse>) => res.body as RegisterResponse),
      tap((response: RegisterResponse) => {
        return response;
      })
    );
  }

  resetLoginSuccess() {
    this.loginSuccessSubject.next(false);
  }

  notifyLoginSuccess() {
    this._isLoggedIn.set(true);
    this.loginSuccessSubject.next(true);
  }

  getToken(): string | null {
    return this.cookieService.get('token') || null;
  }

  getStudent(): Student | null {
    const student = this.cookieService.get('student');
    return student ? JSON.parse(student) as Student : null;
  }

  logout(): void {
    this.cookieService.delete('token', '/', undefined, true, 'Strict');
    this.cookieService.delete('student', '/', undefined, true, 'Strict');
    this._isLoggedIn.set(false);
    this.loginSuccessSubject.next(false);
  }

  getStudentName() {
    const fullName = this.getStudent()?.name;
    return fullName ? fullName.split(' ')[0] : '';
  }

  /** Keeps `isLoggedIn` aligned with the token cookie (e.g. after profile hydration). */
  syncLoginSignalFromCookie(): void {
    this._isLoggedIn.set(this.cookieService.check('token'));
  }

  getRecordedMaterial(): Observable<PurchasedCoursesResponse> {
    const token = this.getToken();

    return this.apiService.get<PurchasedCoursesResponse>(
      `api/v1/profile/materials?p=${new Date().getTime()}`,
      {},
      { Authorization: `Bearer ${token}` }
    ).pipe(
      map((res: HttpResponse<PurchasedCoursesResponse>) => res.body as PurchasedCoursesResponse),
      tap((response: PurchasedCoursesResponse) => {
        if (response.data) {
          console.log(response);
        }
        return response;
      })
    );
  }

  getProfilePayments(): Observable<ProfilePaymentItem[]> {
    const token = this.getToken();

    return this.apiService.get<ProfilePaymentsResponse>(
      `api/v1/profile/payments?p=${new Date().getTime()}`,
      {},
      { Authorization: `Bearer ${token}` }
    ).pipe(
      map((res: HttpResponse<ProfilePaymentsResponse>) => (res.body?.data ?? []))
    );
  }

  getUnpaidInvoicesNextTwoDays(): Observable<UnpaidInvoicesNextTwoDaysData> {
    const token = this.getToken();
    return this.apiService.get<UnpaidInvoicesNextTwoDaysResponse>(
      'api/v1/profile/unpaid-invoices-next-two-days',
      {},
      { Authorization: `Bearer ${token}` }
    ).pipe(
      map((res: HttpResponse<UnpaidInvoicesNextTwoDaysResponse>) => res.body?.data ?? { has_unpaid_invoices_next_two_days: false, count: 0, installments: [] })
    );
  }

  getProfile(): Observable<ProfileResponse> {
    const token = this.getToken();

    return this.apiService.get<ProfileResponse>(
      `api/v1/profile?p=${new Date().getTime()}`,
      {},
      { Authorization: `Bearer ${token}` }
    ).pipe(
      map((res: HttpResponse<ProfileResponse>) => res.body as ProfileResponse),
      tap((response: ProfileResponse) => {
        if (response.data) {
          this.cookieService.set('student', JSON.stringify(response.data), { path: '/', secure: true, sameSite: 'Strict' });
        }
        return response;
      })
    );
  }


  updateProfile(body: any | FormData): Observable<Student> {
    const token = this.getToken();

    return this.apiService.put<ProfileResponse>(
      'api/v1/profile',
      body,
      { Authorization: `Bearer ${token}` }
    ).pipe(
      map((res: HttpResponse<ProfileResponse>) => {
        const student = res.body?.data as Student;
        if (student) {
          this.cookieService.set('student', JSON.stringify(student), { path: '/', secure: true, sameSite: 'Strict' });
        }
        return student;
      })
    );
  }

  requestPasswordReset(body: any): Observable<SendOtpResponse> {
    return this.apiService.post<SendOtpResponse>("api/v1/password/send-otp", body).pipe(
      map((res: HttpResponse<SendOtpResponse>) => res.body as SendOtpResponse),
      tap((response: SendOtpResponse) => {
        return response;
      })
    );
  }

  verifyOtp(body: any): Observable<VerifyOtpResponse> {
    return this.apiService.post<VerifyOtpResponse>("api/v1/password/verify-otp", body).pipe(
      map((res: HttpResponse<VerifyOtpResponse>) => res.body as VerifyOtpResponse),
      tap((response: VerifyOtpResponse) => {
        console.log('Response inside verifyOtp service:', response);
      })
    );
  }

  resetPassword(body: any): Observable<ResetPasswordResponse> {
    return this.apiService.post<ResetPasswordResponse>(
      "api/v1/password/reset",
      body
    ).pipe(
      map((res: HttpResponse<ResetPasswordResponse>) => res.body as ResetPasswordResponse),
      tap((response: ResetPasswordResponse) => {
        console.log('Reset password response:', response);
      })
    );
  }

  changePassword(body: any): Observable<ChangePasswordResponse> {
    const token = this.getToken();

    return this.apiService.post<ChangePasswordResponse>(
      "api/v1/profile/change-password",
      body,
      { Authorization: `Bearer ${token}` }   // ✅ add auth header
    ).pipe(
      map((res: HttpResponse<ChangePasswordResponse>) => res.body as ChangePasswordResponse),
      tap((response: ChangePasswordResponse) => {
        console.log('Change password response:', response);
      })
    );
  }

}
