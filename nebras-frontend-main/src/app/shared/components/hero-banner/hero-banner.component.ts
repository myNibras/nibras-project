import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';
import { BreadcrumbComponent } from 'app/shared/components/breadcrumb/breadcrumb.component';
import { Breadcrumb } from 'app/shared/components/breadcrumb/types/breadcrumb.types';

/**
 * Unified page hero used across internal pages (policies, careers, contact, etc).
 *
 * Inputs:
 *   - title: translation key OR literal text (depending on `translate`)
 *   - subtitle: optional translation key OR literal text
 *   - breadcrumb: optional crumbs displayed under the title
 *   - translate: when true (default), title and subtitle are run through the
 *     translate pipe; pass false to use literal strings.
 */
@Component({
  selector: 'app-hero-banner',
  standalone: true,
  imports: [CommonModule, TranslateModule, BreadcrumbComponent],
  templateUrl: './hero-banner.component.html',
  styleUrl: './hero-banner.component.scss',
})
export class HeroBannerComponent {
  @Input({ required: true }) title!: string;
  @Input() subtitle?: string;
  @Input() breadcrumb: Breadcrumb[] = [];
  @Input() translate = true;
}
