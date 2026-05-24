import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';

import { FaqComponent } from 'app/shared/components/faq/faq.component';
import { StorageService } from 'app/core/storage/storage.service';
import { FaqSection } from 'app/shared/models/faq';

@Component({
  selector: 'app-faq-page',
  standalone: true,
  imports: [CommonModule, TranslateModule, FaqComponent],
  templateUrl: './faq-page.component.html',
  styleUrl: './faq-page.component.scss'
})
export class FaqPageComponent {

  faqSection: FaqSection | null = null;
  constructor(public storageService: StorageService) {}

  onFaqSectionChange(section: FaqSection | null): void {
    this.faqSection = section;
  }

  /** Locale-aware route to the home page (used for breadcrumb). */
  get homeRoute(): string {
    return this.storageService.siteLanguage$.value === 'ar' ? '/ar' : '/en';
  }
}
