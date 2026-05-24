import { Component, Input, OnInit, OnChanges, OnDestroy, SimpleChanges, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { TranslateModule } from '@ngx-translate/core';
import { Subject, takeUntil } from 'rxjs';

import { FaqService } from 'app/shared/services/faq/faq.service';
import { FaqSection, FaqItem } from 'app/shared/models/faq';
import { StorageService } from 'app/core/storage/storage.service';

@Component({
  selector: 'app-faq',
  standalone: true,
  imports: [CommonModule, RouterLink, TranslateModule],
  templateUrl: './faq.component.html',
  styleUrl: './faq.component.scss'
})
export class FaqComponent implements OnInit, OnChanges, OnDestroy {

  /**
   * Optional max number of FAQs to display.
   * When set, the component also renders a "View All FAQs" link
   * pointing to the full FAQ page.
   */
  @Input() limit?: number;

  /**
   * Whether to render the "View All FAQs" link when `limit` is set.
   * Defaults to true; can be turned off if hosting page wants its own CTA.
   */
  @Input() showViewAllLink = false;

  /**
   * Whether to render the section title (e.g. "Frequently Asked Questions").
   * Defaults to true; turn off when the host page already has its own page heading
   * (e.g. the dedicated FAQ page) to avoid showing the title twice.
   */
  @Input() showSectionTitle = true;
  @Output() sectionChange = new EventEmitter<FaqSection | null>();

  faqSection: FaqSection | null = null;

  faqs: (FaqItem & { open: boolean })[] = [];

  loading = true;

  private destroy$ = new Subject<void>();

  constructor(
    private faqService: FaqService,
    public storageService: StorageService
  ) { }

  ngOnInit(): void {
    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        this.loadFaqSection();
      });
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['limit'] && !changes['limit'].firstChange) {
      this.loadFaqSection();
    }
  }

  loadFaqSection(): void {
    this.loading = true;
    this.faqSection = null;
    this.faqs = [];

    this.faqService.getSection(this.limit)
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: section => {
          if (section) {
            this.faqSection = section;
            this.applyLimit(section.data);
          }
          this.sectionChange.emit(section);
          this.loading = false;
        },
        error: () => {
          this.loading = false;
          this.faqSection = null;
          this.faqs = [];
          this.sectionChange.emit(null);
        }
      });
  }

  /** Slice down to the configured limit (if any) and seed UI state. */
  private applyLimit(items: FaqItem[]): void {
    this.faqs = items.map((item, index) => ({
      ...item,
      open: index === 0
    }));
  }

  toggle(index: number): void {
    this.faqs[index].open = !this.faqs[index].open;
  }

  /** Locale-aware route to the full FAQ page. */
  get faqPageRoute(): string {
    return this.storageService.siteLanguage$.value === 'ar'
      ? '/ar/الأسئلة-الشائعة'
      : '/en/faqs';
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
}
