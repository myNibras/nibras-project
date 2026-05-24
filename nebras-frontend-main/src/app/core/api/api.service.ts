import { HttpClient, HttpHeaders, HttpParams, HttpResponse } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { StorageService } from 'app/core/storage/storage.service';
import { environment } from 'environments/environment';

@Injectable({
  providedIn: 'root'
})

export class ApiService {
  private apiUrl = environment.apiUrl;

  public static JSON_REQUEST: Record<string, string> = {
    'accept': 'application/json',
    'Content-Type': 'application/json',
    'Cache-Control': 'no-cache',
    'Pragma': 'no-cache'
  };
  public static FORM_REQUEST: Record<string, string> = {
    'Content-Type': 'application/x-www-form-urlencoded',
  };

  public headers: Record<string, string> = {
    'accept': 'application/json',
    'Content-Type': 'application/json',
    'Cache-Control': 'no-cache',
    'Pragma': 'no-cache'
  };

  constructor(
    public _httpClient: HttpClient,
    public storageService: StorageService,
  ) { }

  /**
   * Makes a get request to the given url.
   * @param {string}         url     The url to make a get request to
   * @param {HttpParams}     params http params to send to api
   * @param {Record<string, string>} additionalHeaders
   */
  get<T>(
    url: string,
    params: Record<string, string | string[]> | HttpParams = {},
    additionalHeaders: Record<string, string | string[]> = {},
    withCredentials?: boolean,
  ): Observable<HttpResponse<T>> {
    const headers: Record<string, string | string[]> = {
      ...this.headers,
      'Accept-Language': this.storageService.siteLanguage$.value,
      ...additionalHeaders,
    };

    const httpParams =
      params instanceof HttpParams
        ? params
        : new HttpParams({ fromObject: params });

    return this._httpClient.get<T>(this._buildApiUrl(url), {
      headers: new HttpHeaders(headers),
      params: httpParams,
      observe: 'response',
      withCredentials,
    });
  }

  /**
   * Makes a post request to the given url.
   * @param {string}                  url               The url to make a post request to
   * @param {string}                  body              The body of the post request
   * @param {boolean}                 sign              Should the request be signed?
   * @param {Record<string, string>}  additionalHeaders A list of key/value pairs to add as headers
   * @param handler
   * @returns {Observable<Response>}                    Response from put request
   */
  post<T>(
    url: string,
    body: any,
    additionalHeaders?: Record<string, string>,
    withCredentials?: boolean,
  ): Observable<HttpResponse<T>> {
    const isFormData = body instanceof FormData;

    // When sending FormData, do NOT set Content-Type (browser sets boundary automatically).
    const headerOptions: Record<string, string> = {
      ...(isFormData ? {} : ApiService.JSON_REQUEST),
      'Accept-Language': this.storageService.siteLanguage$.value,
      ...additionalHeaders,
    };

    return this._httpClient.post<T>(this._buildApiUrl(url), body, {
      headers: new HttpHeaders(headerOptions),
      observe: 'response',
      withCredentials,
    });
  }

  /**
   * Makes a put request to the given url.
   * @param   {string}                      url               The url to make a put request to
   * @param   {string | FormData}           body              The body of the put request
   * @param   {Record<string, string>}      additionalHeaders Additional Headers
   * @returns {Observable<HttpResponse<T>>}                   Response from put request
   */
  put<T>(
    url: string,
    body: string | FormData,
    additionalHeaders?: Record<string, string>,
    withCredentials?: boolean,
  ): Observable<HttpResponse<T>> {
    const isFormData = body instanceof FormData;
    const headers: Record<string, string> = {
      ...(isFormData ? {} : ApiService.JSON_REQUEST),
      'Accept-Language': this.storageService.siteLanguage$.value,
      ...additionalHeaders,
    };

    // Remove Content-Type header for FormData to let browser set it with boundary
    if (isFormData) {
      delete headers['Content-Type'];
    }

    return this._httpClient.put<T>(this._buildApiUrl(url), body, {
      headers: new HttpHeaders(headers),
      observe: 'response',
      withCredentials,
    });
  }

  /**
   * Makes delete request to given url
   * @param {string}        url     The url to make a delete request to
   * @param {string}        options The RequestOptions object for the call
   * @returns {Observable<Response>}  Response from delete request
   */
  delete<T>(
  url: string,
  body?: any,
  additionalHeaders?: Record<string, string>,
  withCredentials?: boolean
): Observable<HttpResponse<T>> {

  const headerOptions: Record<string, string> = {
    ...ApiService.JSON_REQUEST,
    'Accept-Language': this.storageService.siteLanguage$.value,
    ...additionalHeaders,
  };

  return this._httpClient.request<T>('DELETE', this._buildApiUrl(url), {
    body,
    headers: new HttpHeaders(headerOptions),
    observe: 'response',
    withCredentials,
  });
}


  /**
   * Extract the object from the http response from the api service
   * @param response
   */
  extractTypeFromMessage = <T>(response: HttpResponse<T>): T | null => {
    return response.body;
  };

  /**
   * Builds the url for the api call by combining the calls route and the current environments prefix
   * @param {string} url The relative url for the api call
   */
  _buildApiUrl(url: string): string {
    // If this is a full url, do not attempt to add prefixes
    if (!/^(http|\/\/)/gi.test(url)) {
      // TODO: we can add anything here as prefix if we need
      url = `${this.apiUrl}${url}`;
    }

    return url;
  }
}
