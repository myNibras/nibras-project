import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslateModule, TranslateService } from '@ngx-translate/core';
import { HeroBannerComponent } from 'app/shared/components/hero-banner/hero-banner.component';

@Component({
  selector: 'app-terms-and-conditions',
  standalone: true,
  imports: [CommonModule, TranslateModule, HeroBannerComponent],
  templateUrl: './terms-and-conditions.component.html',
  styleUrl: './terms-and-conditions.component.scss',
})
export class TermsAndConditionsComponent {
  constructor(public translateService: TranslateService) {}

  terms: string[] = [
    'I undertake in my',
    'The Terms and Conditions',
    'My use and/or',
    'I undertake not',
    'I agree to indemnify',
    'I agree to install',
    'I grant Shottaar',
    'I undertake not to',
    'I acknowledge that any',
    'I agree that Shottaar',
    'I acknowledge that delay',
    'I acknowledge that changes',
    'Intellectual property rights',
    'I agree not to',
    'I undertake to comply',
    'The company provides',
    'Jordanian law governs',
  ];
}
