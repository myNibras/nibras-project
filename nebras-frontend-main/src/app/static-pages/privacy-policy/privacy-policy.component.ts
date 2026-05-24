import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';
import { HeroBannerComponent } from 'app/shared/components/hero-banner/hero-banner.component';

@Component({
  selector: 'app-privacy-policy',
  standalone: true,
  imports: [CommonModule, TranslateModule, HeroBannerComponent],
  templateUrl: './privacy-policy.component.html',
  styleUrl: './privacy-policy.component.scss',
})
export class PrivacyPolicyComponent {
  sections = [
    { key: 'Privacy Collection', icon: 'database' },
    { key: 'Privacy Usage',      icon: 'check' },
    { key: 'Privacy Sharing',    icon: 'shield' },
    { key: 'Privacy Retention',  icon: 'clock' },
    { key: 'Privacy Rights',     icon: 'user' },
  ] as const;
}
