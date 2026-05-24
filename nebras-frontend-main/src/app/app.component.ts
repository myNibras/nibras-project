import { Component, OnInit } from '@angular/core';
import { Location } from '@angular/common';
import { RouterOutlet } from '@angular/router';
import { StorageService } from './core/storage/storage.service';
import { TextDirectionService } from 'app/text-direction.service';
import { TranslateService } from '@ngx-translate/core';
import { HeaderComponent } from "./layouts/header/header/header.component";
import { FooterComponent } from 'app/layouts/footer/footer/footer.component';
import { routeTranslations } from 'app/route-translations';
import { WhatsappButtonComponent } from "./shared/components/whatsapp-button/whatsapp-button.component";
import { ToastComponent } from './shared/components/toast/toast.component';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, HeaderComponent, FooterComponent, WhatsappButtonComponent, ToastComponent],
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss'
})
export class AppComponent implements OnInit {
  title = 'nebras-frontend';

  translatedRoutes!: { [route: string]: string; };

  constructor(
    public directionService: TextDirectionService,
    public location: Location,
    public storageService: StorageService,
    public translate: TranslateService,
  ) {
    translate.addLangs(['en', 'ar']);
  }

  ngOnInit(): void {
    this.setSiteLanguageFromUrl();

    this.storageService.siteLanguage$.subscribe((lang: 'en' | 'ar') => {
      this.handleSiteLanguage(lang);
    });
  }

  /**
   * Used to handle the site translation, cotent and routes
   */
  setSiteLanguageFromUrl(): void {
    const path = this.location.path();
    const firstSegment = path.split('/')[1]; // first segment after "/"
    const siteLanguage = firstSegment === 'ar' ? 'ar' : 'en';
    this.storageService.siteLanguage$.next(siteLanguage);
  }

  /**
   * Used to handle the site ( content and routes ) Language
   * @param lang en or ar
   */
  handleSiteLanguage = (lang: 'en' | 'ar'): void => {
    this.translate.use(lang);
    this.directionService.setDirection(lang);
    this.translatedRoutes = routeTranslations[lang];
  }
}
