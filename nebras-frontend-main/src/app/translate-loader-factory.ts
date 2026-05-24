import { HttpClient } from '@angular/common/http';

import { Observable } from 'rxjs';

import { TranslateLoader } from '@ngx-translate/core';

export class CustomTranslateHttpLoader implements TranslateLoader {
  constructor(
    private http: HttpClient, private prefix: string = '/assets/i18n/', 
    private suffix: string = '.json',
  ) {}

  /**
   * 
   * @param lang the language to load
   */
  getTranslation(lang: string): Observable<any> {
    const langCode = lang.split('-')[0];
    const translations = this.http.get(`${this.prefix}${langCode}${this.suffix}`);

    return translations;
  }
}

export function translateLoaderFactory(http: HttpClient) {
  return new CustomTranslateHttpLoader(http, 'app/assets/i18n/', '.json?v=1.3');
}