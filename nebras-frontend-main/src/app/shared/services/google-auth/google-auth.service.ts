import { Injectable } from '@angular/core';
import { Observable, from } from 'rxjs';
import { switchMap } from 'rxjs/operators';
import { environment } from 'environments/environment';

type TokenClient = { requestAccessToken: () => void };
type TokenResponse = {
  access_token?: string;
  error?: string;
  error_description?: string;
};

declare global {
  interface Window {
    google?: {
      accounts: {
        oauth2: {
          initTokenClient: (config: {
            client_id: string;
            scope: string;
            callback: (response: TokenResponse) => void;
          }) => TokenClient;
        };
      };
    };
  }
}

@Injectable({
  providedIn: 'root'
})
export class GoogleAuthService {

  private scriptLoading: Promise<void> | null = null;
  private tokenClient: TokenClient | null = null;
  private pendingCallback: ((response: TokenResponse) => void) | null = null;

  loadScript(): Promise<void> {
    if (window.google?.accounts?.oauth2) {
      this.ensureClient();
      return Promise.resolve();
    }
    if (this.scriptLoading) {
      return this.scriptLoading;
    }
    this.scriptLoading = new Promise<void>((resolve, reject) => {
      const script = document.createElement('script');
      script.src = 'https://accounts.google.com/gsi/client';
      script.async = true;
      script.defer = true;
      script.onload = () => {
        try {
          this.ensureClient();
        } catch (err) {
          console.error('[GoogleAuth] failed to init token client', err);
        }
        resolve();
      };
      script.onerror = () => {
        // Don't cache the rejection — let the next attempt retry.
        this.scriptLoading = null;
        script.remove();
        reject(new Error('Could not reach accounts.google.com. Check your network or disable any tracker/ad blocker.'));
      };
      document.head.appendChild(script);
    });
    return this.scriptLoading;
  }

  private ensureClient(): void {
    if (this.tokenClient || !window.google?.accounts?.oauth2 || !environment.googleClientId) {
      return;
    }
    this.tokenClient = window.google.accounts.oauth2.initTokenClient({
      client_id: environment.googleClientId,
      scope: 'email profile',
      callback: (response) => {
        const cb = this.pendingCallback;
        this.pendingCallback = null;
        cb?.(response);
      }
    });
  }

  /**
   * Open a Google OAuth2 popup and return the access token.
   *
   * On mobile browsers (especially iOS Safari) the user-gesture is lost across
   * any microtask hop, so requestAccessToken() must be called synchronously
   * inside the click handler. We pre-build the token client during loadScript()
   * and only invoke requestAccessToken() here — no `from(promise)` chain when
   * the SDK is already warm.
   */
  signIn(): Observable<string> {
    return new Observable<string>(subscriber => {
      const clientId = environment.googleClientId;
      if (!clientId) {
        subscriber.error(new Error('Google Client ID is not configured'));
        return;
      }

      const begin = () => {
        try {
          this.ensureClient();
          if (!this.tokenClient) {
            subscriber.error(new Error('Google SDK not ready'));
            return;
          }
          this.pendingCallback = (response) => {
            if (response.error || !response.access_token) {
              console.warn('[GoogleAuth] sign-in did not return a token', response);
              subscriber.complete();
              return;
            }
            subscriber.next(response.access_token);
            subscriber.complete();
          };
          this.tokenClient.requestAccessToken();
        } catch (err) {
          console.error('[GoogleAuth] requestAccessToken threw', err);
          subscriber.error(err);
        }
      };

      // Fast path: SDK already loaded. Run synchronously to preserve the
      // user-gesture context that mobile browsers require for popups.
      if (window.google?.accounts?.oauth2) {
        begin();
        return;
      }

      // Slow path: SDK not yet loaded. Load it then try — likely to be popup-
      // blocked on iOS, but better than nothing.
      from(this.loadScript()).pipe(switchMap(() => new Observable<void>(s => { s.next(); s.complete(); }))).subscribe({
        next: () => begin(),
        error: (err) => {
          console.error('[GoogleAuth] loadScript failed', err);
          subscriber.error(err);
        }
      });
    });
  }

  isAvailable(): boolean {
    return !!environment.googleClientId;
  }
}
