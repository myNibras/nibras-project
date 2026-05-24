import { Injectable, signal } from '@angular/core';
import { Observable, throwError } from 'rxjs';
import { catchError, map, tap } from 'rxjs/operators';
import { ApiService } from 'app/core/api/api.service';
import { HttpResponse } from '@angular/common/http';
import { AuthService } from '../auth-service/auth-service.service';
import { ApiResponse } from 'app/shared/models/api-response';
import { CartResponse, CouponDetails, InstallmentSessionData, InstallmentSessionResponse } from 'app/shared/models/payment.model';

@Injectable({
  providedIn: 'root'
})
export class PaymentService {

  // Reactive cart-item count — drives the navbar badge.
  private _cartCount = signal<number>(0);
  cartCount = this._cartCount.asReadonly();

  constructor(
    private apiService: ApiService,
    private authService: AuthService) { }

  setCartCount(count: number) {
    this._cartCount.set(Math.max(0, count));
  }

  createSession(body: any): Observable<PaymentResponse> {
    const token = this.authService.getToken();
    return this.apiService.post<any>("api/v1/payments/create", body, { Authorization: `Bearer ${token}` }
    ).pipe(
      map((res: HttpResponse<any>) => res.body as PaymentResponse)
    );
  }

  createInstallmentSession(installmentIds: number[]): Observable<InstallmentSessionData> {
    const token = this.authService.getToken();
    const body = JSON.stringify({ payment_method: 'network', installment_ids: installmentIds });
    return this.apiService.post<InstallmentSessionResponse>(
      'api/v1/payments/installments/create-session',
      body,
      { Authorization: `Bearer ${token}` }
    ).pipe(
      map((res: HttpResponse<InstallmentSessionResponse>) => {
        const b = res.body;
        if (b?.status && b?.data) return b.data;
        throw new Error(b?.message || 'Failed to create installment payment session');
      })
    );
  }

  getCart(): Observable<CartResponse> {
    const token = this.authService.getToken();

    return this.apiService.get<CartResponse>(
      `api/v1/cart?p=${new Date().getTime()}`,
      {},
      { Authorization: `Bearer ${token}` }
    ).pipe(
      map((res: HttpResponse<CartResponse>) => res.body as CartResponse),
      tap((response: CartResponse) => {
        this._cartCount.set(response?.data?.items?.length ?? 0);
        return response;
      })
    );
  }

  addToCart(courseId: number, paymentType: string): Observable<boolean> {
    const token = this.authService.getToken();

    const body = JSON.stringify({ course_id: courseId, payment_type: paymentType });

    return this.apiService.post<ApiResponse<null>>(
      'api/v1/cart/add',
      body,
      { Authorization: `Bearer ${token}` }
    ).pipe(
      map((httpResponse: HttpResponse<ApiResponse<null>>) => {
        const res = httpResponse.body;
        if (res?.status) return true;
        throw new Error(res?.message || 'Failed to add to cart');
      }),
      tap(success => {
        if (success) this._cartCount.update(c => c + 1);
      }),
      catchError(err => {
        console.error('Add to cart error:', err);
        return throwError(() => err);
      })
    );
  }

  removeFromCart(courseId: number): Observable<boolean> {
    const token = this.authService.getToken();

    const body = JSON.stringify({ course_id: courseId });

    return this.apiService.delete<ApiResponse<null>>(
      'api/v1/cart/remove',
      body,
      { Authorization: `Bearer ${token}` }
    ).pipe(
      map((httpResponse: HttpResponse<ApiResponse<null>>) => {
        const res = httpResponse.body;
        if (res?.status) return true;
        throw new Error(res?.message || 'Failed to remove from cart');
      }),
      tap(success => {
        if (success) this._cartCount.update(c => Math.max(0, c - 1));
      }),
      catchError(err => {
        console.error('Remove from cart error:', err);
        return throwError(() => err);
      })
    );
  }

  addCoupon(couponCode: string): Observable<CouponDetails> {
    const token = this.authService.getToken();

    const body = JSON.stringify({ coupon_code: couponCode });

    return this.apiService.post<ApiResponse<CouponDetails>>(
      'api/v1/coupons/add',
      body,
      { Authorization: `Bearer ${token}` }
    ).pipe(
      map((httpResponse) => {
        const res = httpResponse.body;
        if (res?.status && res.data) {
          return res.data;
        }
        throw new Error(res?.message || 'Invalid coupon');
      })
    );
  }

  removeCoupon(): Observable<boolean> {
    const token = this.authService.getToken();

    return this.apiService.delete<ApiResponse<null>>(
      'api/v1/coupons/remove',
      {},
      { Authorization: `Bearer ${token}` }
    ).pipe(
      map((httpResponse) => {
        const res = httpResponse.body;
        if (res?.status) {
          return true;
        }
        throw new Error(res?.message || 'Failed to remove coupon');
      })
    );
  }


}
