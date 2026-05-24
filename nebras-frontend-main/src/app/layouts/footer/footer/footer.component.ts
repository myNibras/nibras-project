import { Component } from '@angular/core';
import { TranslateModule, TranslateService } from '@ngx-translate/core';
import { StorageService } from 'app/core/storage/storage.service';
import { RouterLink } from '@angular/router';


@Component({
    selector: 'app-footer',
    imports: [TranslateModule, RouterLink],
    templateUrl: './footer.component.html',
    styleUrl: './footer.component.scss'
})
export class FooterComponent {
    constructor(
        public translateService: TranslateService,
        public storageService: StorageService
    ) { }
    homeRoutes: Record<'ar' | 'en', string> = {
        ar: 'ar',
        en: 'en'
    };
    termsRoutes: Record<'ar' | 'en', string> = {
        ar: 'ar/الشروط-والاحكام',
        en: 'en/terms-and-conditions'
    };
    aboutUsRoutes: Record<'ar' | 'en', string> = {
        ar: 'ar/من-نحن',
        en: 'en/about-us'
    };
    contactUsRoutes: Record<'ar' | 'en', string> = {
        ar: 'ar/تواصل-معنا',
        en: 'en/contact-us'
    };
    subjrctsRoutes: Record<'ar' | 'en', string> = {
        ar: 'ar/المواد/الكل',
        en: 'en/subjects/all'
    };
    CancellationAndRefundRoutes: Record<'ar' | 'en', string> = {
        ar: 'ar/سياسة-الالغاء-والاسترجاع',
        en: 'en/cancellation-and-refund-policy'
    };
    PrivacyPolicyRoutes: Record<'ar' | 'en', string> = {
        ar: 'ar/سياسة-الخصوصية',
        en: 'en/privacy-policy'
    };
    careersRoutes: Record<'ar' | 'en', string> = {
        ar: 'ar/وظائفنا',
        en: 'en/our-careers'
    };

     faqRoutes: Record<'ar' | 'en', string> = {
        ar: 'ar/الأسئلة-الشائعة',
        en: 'en/faqs'
    };

    currentYear = new Date().getFullYear();
    scrollToFragment(fragment: string): void {
        setTimeout(() => {
            const element = document.getElementById(fragment);
            if (element) {
                const elementPosition = element.getBoundingClientRect().top + window.scrollY;
                const offset = 90; // adjust to match your sticky navbar height
                window.scrollTo({
                    top: elementPosition - offset,
                    behavior: 'smooth'
                });
            }
        }, 100); // small delay in case of async rendering
    }

}
