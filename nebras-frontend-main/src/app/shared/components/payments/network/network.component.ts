import { isPlatformBrowser } from '@angular/common';
import { AfterViewInit, Component, Inject, Input, PLATFORM_ID, Renderer2 } from '@angular/core';
import { environment } from 'environments/environment';

declare var Checkout: any;

@Component({
    selector: 'app-network',
    imports: [],
    templateUrl: './network.component.html',
    styleUrl: './network.component.scss'
})
export class NetworkComponent implements AfterViewInit {
  @Input() sessionId!: string;
  private scriptLoaded = false;

  constructor(private renderer: Renderer2, @Inject(PLATFORM_ID) private platformId: Object) {}

  ngAfterViewInit(): void {
    this.loadMastercardScript()
      .then(() => {
        if (isPlatformBrowser(this.platformId)) {
          this.initializeCheckout();
          Checkout.showPaymentPage();
        }
      })
      .catch(error => {
        console.error('Failed to load Mastercard script:', error);
      });
  }

  private loadMastercardScript(): Promise<void> {
    return new Promise((resolve, reject) => {
      if (this.scriptLoaded || typeof Checkout !== 'undefined') {
        resolve();
        return;
      }

      const script = this.renderer.createElement('script');
      script.src = environment.networkUrl + '/static/checkout/checkout.min.js';
      script.setAttribute('data-afterRedirect', 'Checkout.restoreFormFields');
      script.onload = () => {
        this.scriptLoaded = true;
        resolve();
      };
      script.onerror = (err: any) => reject(err);
      if (isPlatformBrowser(this.platformId)) {
        this.renderer.appendChild(document.body, script);
      }
    });
  }

  private initializeCheckout(): void {
    // Define global callback functions
    (window as any).errorCallback = (error: any) => {
      console.error('Payment Error:', JSON.stringify(error));
    };

    (window as any).cancelCallback = () => {
      console.warn('Payment cancelled');
    };

    (window as any).restoreFormFields = () => {
      console.log('Restoring form fields...');
    };

    // Configure Mastercard Checkout
    Checkout.configure({
      session: {
        id: this.sessionId
      }
    });
  }
}
