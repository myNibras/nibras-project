import { DestroyRef, Injectable, signal, inject } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { BehaviorSubject, Observable, of } from 'rxjs';
import { catchError, map, tap } from 'rxjs/operators';
import { HttpResponse } from '@angular/common/http';
import { ApiService } from 'app/core/api/api.service';
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import {
  ChatNotificationItem,
  ChatUnreadCountResponse,
  ChatNotificationsListResponse,
  ChatMarkReadResponse,
  ChatThreadType,
} from 'app/shared/models/chat-notification.model';

const POLL_INTERVAL_MS = 60_000;

@Injectable({ providedIn: 'root' })
export class ChatNotificationsService {
  private readonly api = inject(ApiService);
  private readonly auth = inject(AuthService);
  private readonly destroyRef = inject(DestroyRef);

  private readonly _count = signal<number>(0);
  readonly count = this._count.asReadonly();

  private readonly _recent$ = new BehaviorSubject<ChatNotificationItem[]>([]);
  readonly recent$ = this._recent$.asObservable();

  private timerId: ReturnType<typeof setInterval> | null = null;
  private failureStreak = 0;

  constructor() {
    this.auth.loginSuccess$.pipe(takeUntilDestroyed(this.destroyRef)).subscribe((loggedIn) => {
      if (loggedIn || this.auth.isLoggedIn()) {
        this.start();
      } else {
        this.stop();
      }
    });

    if (typeof document !== 'undefined') {
      document.addEventListener('visibilitychange', this.onVisibility);
    }

    this.destroyRef.onDestroy(() => {
      if (typeof document !== 'undefined') {
        document.removeEventListener('visibilitychange', this.onVisibility);
      }
    });

    if (this.auth.isLoggedIn()) {
      this.start();
    }
  }

  private onVisibility = (): void => {
    if (document.visibilityState === 'visible' && this.auth.isLoggedIn()) {
      this.refresh();
    }
  };

  start(): void {
    if (this.timerId !== null) return;
    this.refresh();
    this.timerId = setInterval(() => this.refresh(), POLL_INTERVAL_MS);
  }

  stop(): void {
    if (this.timerId !== null) {
      clearInterval(this.timerId);
      this.timerId = null;
    }
    this._count.set(0);
    this._recent$.next([]);
  }

  refresh(): void {
    this.fetchCount().subscribe();
  }

  private fetchCount(): Observable<number> {
    const token = this.auth.getToken();
    if (!token) return of(0);

    return this.api
      .get<ChatUnreadCountResponse>(
        `api/v1/chat/notifications/unread-count?p=${Date.now()}`,
        {},
        { Authorization: `Bearer ${token}` }
      )
      .pipe(
        map((res: HttpResponse<ChatUnreadCountResponse>) => res.body?.data.count ?? 0),
        tap((count) => {
          this._count.set(count);
          if (this.failureStreak > 0) {
            this.failureStreak = 0;
            this.restoreNormalPolling();
          }
        }),
        catchError(() => {
          this.failureStreak += 1;
          if (this.failureStreak >= 3 && this.timerId !== null) {
            clearInterval(this.timerId);
            this.timerId = setInterval(() => this.refresh(), 5 * 60_000);
          }
          return of(this._count());
        })
      );
  }

  private restoreNormalPolling(): void {
    if (this.timerId !== null) {
      clearInterval(this.timerId);
      this.timerId = setInterval(() => this.refresh(), POLL_INTERVAL_MS);
    }
  }

  loadRecent(): Observable<ChatNotificationItem[]> {
    const token = this.auth.getToken();
    if (!token) return of([]);

    return this.api
      .get<ChatNotificationsListResponse>(
        `api/v1/chat/notifications?p=${Date.now()}`,
        {},
        { Authorization: `Bearer ${token}` }
      )
      .pipe(
        map((res: HttpResponse<ChatNotificationsListResponse>) => res.body?.data ?? []),
        tap((items) => this._recent$.next(items)),
        catchError(() => of([]))
      );
  }

  markRead(courseId: number, threadType: ChatThreadType, threadPartnerId?: number): Observable<number> {
    const token = this.auth.getToken();
    if (!token) return of(0);

    const payload: Record<string, unknown> = { course_id: courseId, thread_type: threadType };
    if (threadType === 'direct' && threadPartnerId != null) {
      payload['thread_partner_id'] = threadPartnerId;
    }

    return this.api
      .post<ChatMarkReadResponse>(
        'api/v1/chat/notifications/mark-read',
        JSON.stringify(payload),
        { Authorization: `Bearer ${token}` }
      )
      .pipe(
        map((res: HttpResponse<ChatMarkReadResponse>) => res.body?.data.marked ?? 0),
        tap(() => this.refresh()),
        catchError(() => of(0))
      );
  }
}
