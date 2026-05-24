import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ProfilePaymentsRefreshService {
  private refresh$ = new Subject<void>();

  /** Emits when profile payments and unpaid reminders should be refetched (e.g. after gateway callback). */
  get onRefresh() {
    return this.refresh$.asObservable();
  }

  triggerRefresh(): void {
    this.refresh$.next();
  }
}
