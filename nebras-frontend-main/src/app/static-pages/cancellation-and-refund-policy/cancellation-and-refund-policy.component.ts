import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslateModule, TranslateService } from '@ngx-translate/core';
import { HeroBannerComponent } from 'app/shared/components/hero-banner/hero-banner.component';

@Component({
  selector: 'app-cancellation-and-refund-policy',
  standalone: true,
  imports: [CommonModule, TranslateModule, HeroBannerComponent],
  templateUrl: './cancellation-and-refund-policy.component.html',
  styleUrl: './cancellation-and-refund-policy.component.scss',
})
export class CancellationAndRefundPolicyComponent {
  constructor(public translateService: TranslateService) {}

  cancellationItems: string[] = [
    'Cancellation Services',
    'Cancellation Requirements',
    'Cancellation Period',
    'Cancellation Refund',
    'Cancellation Note',
  ];

  refundItems: string[] = [
    'Refund Services',
    'Refund Requirements',
    'Refund Period',
    'Refund Process',
    'Refund Note',
  ];
}
