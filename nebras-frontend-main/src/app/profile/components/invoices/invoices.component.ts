import { Component, OnInit, OnDestroy, input, output } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import { ProfilePaymentsRefreshService } from 'app/shared/services/profile-payments-refresh/profile-payments-refresh.service';
import { ProfilePaymentItem } from 'app/shared/models/payment.model';
import { Subject, takeUntil } from 'rxjs';

export interface InstallmentRow {
  id?: number;
  date: string;
  dueDate?: string | null;
  label: string;
  paid: boolean;
  hasReceipt?: boolean;
  /** Invoice PDF link for this installment (from API). */
  invoiceLink?: string | null;
  /** Order ID for this installment (from API). Use instead of payment-level order_id for installments. */
  orderId?: string | null;
  /** True when this is the next unpaid installment (only one Pay Now per invoice). */
  isNextUnpaid?: boolean;
}

export interface InvoiceItem {
  id: string;
  invoiceNumber: string;
  type: 'course' | 'curriculum';
  description: string;
  installments: InstallmentRow[];
  /** Invoice PDF link for download (from API). */
  invoiceLink?: string | null;
  /** True when payment had no installments from API (cash/full payment). */
  isCashPayment?: boolean;
}

@Component({
  selector: 'app-invoices',
  standalone: true,
  imports: [TranslateModule],
  templateUrl: './invoices.component.html',
  styleUrls: ['./invoices.component.scss']
})
export class InvoicesComponent implements OnInit, OnDestroy {
  expandedIds = new Set<string>();
  invoices: InvoiceItem[] = [];
  loading = true;
  installmentPaymentLoading = input<boolean>(false);
  payNowInstallment = output<number>();
  private destroy$ = new Subject<void>();

  constructor(
    private authService: AuthService,
    private paymentsRefresh: ProfilePaymentsRefreshService
  ) {}

  ngOnInit(): void {
    this.loadPayments();
    this.paymentsRefresh.onRefresh.pipe(takeUntil(this.destroy$)).subscribe(() => this.loadPayments());
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  private loadPayments(): void {
    this.authService.getProfilePayments().subscribe({
      next: (items: ProfilePaymentItem[]) => {
        this.invoices = this.mapPaymentsToInvoices(items);
        this.loading = false;
      },
      error: () => {
        this.invoices = [];
        this.loading = false;
      }
    });
  }

  isExpanded(id: string): boolean {
    return this.expandedIds.has(id);
  }

  hasMultipleInstallments(invoice: InvoiceItem): boolean {
    return invoice.installments.length > 1;
  }

  /** For parent row: show next unpaid installment number, or last if all paid. */
  getParentInstallmentDisplay(invoice: InvoiceItem): { number: string; total: number } {
    const total = invoice.installments.length;
    const nextUnpaid = invoice.installments.find(i => i.isNextUnpaid);
    const number = nextUnpaid
      ? nextUnpaid.label
      : invoice.installments[total - 1]?.label ?? '1';
    return { number, total };
  }

  toggleExpand(id: string): void {
    if (this.expandedIds.has(id)) {
      this.expandedIds.delete(id);
    } else {
      this.expandedIds.add(id);
    }
    // trigger change detection by creating a new Set reference
    this.expandedIds = new Set(this.expandedIds);
  }

  payNow(_invoice: InvoiceItem, installment: InstallmentRow): void {
    if (installment.id != null) {
      this.payNowInstallment.emit(installment.id);
    }
  }

  private mapPaymentsToInvoices(items: ProfilePaymentItem[]): InvoiceItem[] {
    return items.map((item, index) => {
      const hasInstallments = item.installments && item.installments.length > 0;

      let installments: InstallmentRow[] = hasInstallments
        ? item.installments.map(inst => ({
            id: inst.id,
            date: inst.due_date || inst.paid_at || item.paid_at,
            dueDate: inst.due_date,
            label: inst.installment_number.toString(),
            paid: inst.status === 'paid',
            hasReceipt: !!inst.invoice_link && inst.status === 'paid',
            invoiceLink: inst.invoice_link ?? null,
            orderId: inst.order_id ?? null,
            isNextUnpaid: false
          }))
        : [
            {
              date: item.paid_at,
              dueDate: item.paid_at,
              label: '1',
              paid: true,
              hasReceipt: !!item.invoice_link,
              orderId: item.order_id ?? null
            }
          ];

      // Mark only the first unpaid installment as "next" (Pay Now button)
      if (hasInstallments && installments.length > 0) {
        const nextUnpaidIndex = installments.findIndex(i => !i.paid);
        if (nextUnpaidIndex !== -1) {
          installments = installments.map((i, idx) =>
            idx === nextUnpaidIndex ? { ...i, isNextUnpaid: true } : i
          );
        }
      }

      return {
        id: `${item.order_id}-${index}`,
        invoiceNumber: item.order_id,
        type: 'course', // API does not distinguish type, treat as course
        description: item.course_name,
        installments,
        // Business rule: payments with installments should not expose invoice links
        invoiceLink: hasInstallments ? null : (item.invoice_link ?? null),
        isCashPayment: !hasInstallments
      };
    });
  }
}
