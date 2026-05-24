import { Inject, Injectable, PLATFORM_ID, Renderer2, RendererFactory2 } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';

@Injectable({
  providedIn: 'root',
})
export class TextDirectionService {
  renderer: Renderer2;
  appDirection: string = 'rtl';

  constructor(
    @Inject(PLATFORM_ID) public platformId: Object,
    rendererFactory: RendererFactory2,
  ) {
    this.renderer = rendererFactory.createRenderer(null, null);
  }

  /**
   * Sets the text direction for the application based on the language code
   * @param lang The language code to set the text direction for
   */
  setDirection(lang: string) {
    const direction = lang === 'ar' ? 'rtl' : 'ltr';
    this.appDirection = direction;
    
    // Only set the attribute if running in the browser
    if (isPlatformBrowser(this.platformId)) {
      this.renderer.setAttribute(document.documentElement, 'dir', direction);
      this.renderer.setAttribute(document.documentElement, 'lang', lang);
    }
  }
}
