import { Component, OnInit, OnDestroy, output } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import { ProfilePaymentsRefreshService } from 'app/shared/services/profile-payments-refresh/profile-payments-refresh.service';
import { UnpaidInvoiceReminder } from 'app/shared/models/payment.model';
import { Subject, takeUntil } from 'rxjs';

@Component({
  selector: 'app-unpaid-reminders',
  standalone: true,
  imports: [TranslateModule],
  templateUrl: './unpaid-reminders.component.html',
  styleUrls: ['./unpaid-reminders.component.scss']
})
export class UnpaidRemindersComponent implements OnInit, OnDestroy {
  unpaidReminders: UnpaidInvoiceReminder[] = [];
  payNow = output<UnpaidInvoiceReminder>();
  private destroy$ = new Subject<void>();

  constructor(
    private authService: AuthService,
    private paymentsRefresh: ProfilePaymentsRefreshService
  ) {}

  ngOnInit(): void {
    this.loadReminders();
    this.paymentsRefresh.onRefresh.pipe(takeUntil(this.destroy$)).subscribe(() => this.loadReminders());
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  private loadReminders(): void {
    this.authService.getUnpaidInvoicesNextTwoDays().subscribe({
      next: (data) => {
        this.unpaidReminders = data.installments ?? [];
      },
      error: () => {
        this.unpaidReminders = [];
      }
    });
  }

  onPayNow(reminder: UnpaidInvoiceReminder): void {
    this.payNow.emit(reminder);
  }
}
