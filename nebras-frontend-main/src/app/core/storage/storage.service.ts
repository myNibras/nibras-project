import { Injectable } from '@angular/core';

import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})

export class StorageService {
  siteLanguage$: BehaviorSubject<'en' | 'ar'> = new BehaviorSubject<'en' | 'ar'>('ar');
    
  constructor() { }
}
