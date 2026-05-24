import { Component, OnDestroy, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';
import { Subject, takeUntil } from 'rxjs';

import { StorageService } from 'app/core/storage/storage.service';
import { PartnersService } from 'app/shared/services/partners/partners.service';
import { PartnersSection, Partner } from 'app/shared/models/partners';
import { BreadcrumbComponent } from 'app/shared/components/breadcrumb/breadcrumb.component';
import { Breadcrumb } from 'app/shared/components/breadcrumb/types/breadcrumb.types';

@Component({
  selector: 'app-partners-page',
  standalone: true,
  imports: [CommonModule, TranslateModule, BreadcrumbComponent],
  templateUrl: './partners-page.component.html',
  styleUrl: './partners-page.component.scss'
})
export class PartnersPageComponent implements OnInit, OnDestroy {

  loadingPartners = true;
  partnersSection: PartnersSection | null = null;
  partners: Partner[] = [];
  breadcrumbs: Breadcrumb[] = [];

  private destroy$ = new Subject<void>();

  constructor(
    public storageService: StorageService,
    private partnersService: PartnersService
  ) { }

  ngOnInit(): void {
    this.storageService.siteLanguage$
      .pipe(takeUntil(this.destroy$))
      .subscribe((lang) => {

        // Breadcrumb
        this.breadcrumbs = [
          {
            title: 'Home',
            link: `/${lang}`
          },
          {
            title: 'our_partners',
            link: lang === 'ar' ? '/ar/شركاؤنا' : '/en/partners'
          }
        ];

        this.loadingPartners = true;

        this.partnersService.get()
          .pipe(takeUntil(this.destroy$))
          .subscribe((response: PartnersSection | null) => {
            this.partnersSection = response;
            this.partners = response?.data ?? [];
            this.loadingPartners = false;
          });

      });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
}
