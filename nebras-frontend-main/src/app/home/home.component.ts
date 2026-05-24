import { Component, OnDestroy, OnInit } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { ContactUsComponent } from "./contact-us/contact-us.component";
import { EducationalStagesComponent } from './educational-stages/educational-stages.component';
import { HomeSliderComponent } from "./home-slider/home-slider.component";
import { ThankYouModalComponent } from "app/shared/components/thank-you-modal/thank-you-modal.component";
import { ActivatedRoute, Router } from '@angular/router';
import { Subject } from 'rxjs';
import { HomeSlider } from 'app/shared/models/home-slider';
import { TestimonialsComponent } from "app/shared/components/testimonials/testimonials.component";
import { NewsComponent } from "app/news/news.component";
import { ArticlesSectionComponent } from "app/articles/articles-section/articles-section.component";
import { PartnersComponent } from "./partners/partners.component";
import { FaqComponent } from "app/shared/components/faq/faq.component";
import { TeachersSectionComponent } from "app/teachers/teachers-section/teachers-section.component";
import { environment } from 'environments/environment';
import { StorageService } from 'app/core/storage/storage.service';

@Component({
  selector: 'app-home',
  imports: [TranslateModule, ArticlesSectionComponent, ContactUsComponent, EducationalStagesComponent, HomeSliderComponent, ThankYouModalComponent, TestimonialsComponent, PartnersComponent, NewsComponent, FaqComponent, TeachersSectionComponent],
  templateUrl: './home.component.html',
  styleUrl: './home.component.scss'
})
export class HomeComponent implements OnInit, OnDestroy {
  destroy$ = new Subject<void>;
  tyOpen = false;
  isRTL = false;
  homeSliders: HomeSlider[] | undefined;
  faqLimit: number | null = environment.faqLimit;

  private profileRoutes: Record<'ar' | 'en', string> = {
    ar: '/ar/الملف-الشخصي',
    en: '/en/profile',
  };

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private storageService: StorageService,
  ) { }


  ngOnInit(): void {
    this.route.queryParams.subscribe(params => {
      const payment = params['payment'];
      if (payment === 'success') {
        // After a successful checkout: forward the user straight to the profile
        // (My Enrolled Courses tab) where the success popup + payments refresh
        // are already wired up.
        const lang = this.storageService.siteLanguage$.value === 'en' ? 'en' : 'ar';
        this.router.navigate([this.profileRoutes[lang]], {
          queryParams: { payment: 'success', tab: 'registered-materials' },
        });
      }
    });
  }

  openTY() { this.tyOpen = true; }

  goHome() {
    this.tyOpen = false;
    this.router.navigate([], {
      queryParams: {
        'payment': null,
        'message': null
      },
      queryParamsHandling: 'merge'
    })
  }

  ngOnDestroy() {
    this.destroy$.next();
    this.destroy$.complete();
  }
}
